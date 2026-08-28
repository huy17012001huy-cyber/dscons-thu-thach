# DSCons Backend Guardrails (KP3-inspired)

## Purpose and status

This is the mandatory backend engineering standard for DSCons. Its purpose is
to prevent a convenient feature change from becoming a later architectural,
security or multi-community refactor.

It adapts the useful discipline from
[`duongtrongnghia/kp3-backend-core`](https://github.com/duongtrongnghia/kp3-backend-core),
audited at commit `eee7355261d7ed784ca2cd6782d789c7e185e877` on 2026-08-28.
It is not a copy of KP3. DSCons keeps Laravel, Livewire, PostgreSQL, Docker,
Google Login, its current URLs and its multi-community model.

Read this document before changing PHP/Laravel, Livewire actions, routes,
models, migrations, API/webhooks, authorization, payments, recruitment or
Revit licensing. Use the feature checklist in
[`KP3_BACKEND_FEATURE_CHECKLIST.md`](KP3_BACKEND_FEATURE_CHECKLIST.md) before
implementation and review.

## Non-negotiable architecture

DSCons is a **modular monolith**. It remains one deployable Laravel app and one
database; it is not a microservice system.

| Area | Canonical home | Responsibility |
| --- | --- | --- |
| Core | `app/Core` | Community context, auth, audit, shared contracts and cross-cutting services |
| Community | `modules/Community` | Feed, content, reports, rules and community support |
| Learning | `modules/Learning` | Courses, challenges, submissions, review, XP and unlocks |
| Commerce | `modules/Commerce` | Membership, marketplace, orders, purchases and SePay |
| Recruitment | `modules/Recruitment` | CV, recruiters, credits, contact requests and recruitment conversations |
| RevitTools | `modules/RevitTools` | Revit device authorization, tool entitlements, manifests and licenses |
| Presentation | `app/Http`, `app/Livewire`, Blade | Request/UI state, authorization entry point and rendering only |

### Module boundary

- A module may depend on `App\\Core` and Laravel contracts only.
- A module must not import another module's models, services, controllers or
  Livewire components. Use a Core contract or a Laravel domain event/listener.
- Do not add business-module dependencies to a module manifest. The enabled
  module order in `config/modules.php` is explicit.
- Put a new bounded domain in an existing module unless it genuinely introduces
  a new independent domain. Do not create a module merely for one screen.
- New module code uses `declare(strict_types=1);`, backed enums for finite
  state, typed DTO/result objects when a service result is more than a model,
  and a module provider/manifest consistent with current modules.
- Historical migrations stay in `database/migrations`. Only new module-owned
  migrations belong in that module, so history and deployed databases remain
  safe.

## Community is a security boundary

`brand_id` is not a display filter. It is an authorization boundary.

- Resolve the current community through `CommunityContext` (or the existing
  `brand()` helper backed by it). Do not re-derive it from request input,
  session fragments or a model passed by the browser.
- Every community-scoped read and write must be restricted to the active
  community's `brand_id`, including nested relations, counts, exports,
  background jobs, notifications and admin pages.
- For a relationship between two records, verify that *both* records belong to
  the same `brand_id` before creating or changing the relationship.
- Super Admin may perform an explicitly global query; Community Owner/Admin
  may only query their active community. A global screen must name its global
  intent in the service method and authorise it before querying.
- `withoutGlobalScopes()` is an escape hatch, never a shortcut. The calling
  service must have a clear name, explicitly filter `brand_id` where relevant,
  authorise the actor and have a cross-community test. Never use it in a
  public controller/Livewire component to "make data appear".
- Features must work correctly when the same user belongs to several
  communities. A default/first membership must never silently select data from
  another community.

## Authorization is four separate concerns

Never replace one class of permission with another.

| Concern | Source of truth | Examples |
| --- | --- | --- |
| Global administration | `users.is_admin` / `super-admin` | `/admin/*`, global audit and cross-community view |
| Community capability | Role in the active community | owner, admin, moderator, member |
| Account type | Engineer or Recruiter account state | CV workspace versus recruiter dashboard |
| Commercial/access state | Membership, verification, purchase or license state | paid learning access, verified recruiter, Revit tool entitlement |

- A Super Admin does not automatically become an engineer or recruiter for
  learner/recruiter workflows. Use the existing admin-preview path where a
  preview is required.
- Community Admin must never gain `/admin/*`, another community's management
  data or Super Admin management rights.
- Recruiter verification, membership state and license status are states, not
  roles. Check them in addition to account type where required.
- Authorise in middleware/policies and repeat critical ownership/community
  checks in the application service. Route binding alone is not authorization.
- Sensitive changes (roles, ownership, payments, credit, CV privacy,
  impersonation, device/license decisions) must produce an audit record.

## Presentation, application service and persistence

The flow is:

```text
Route / Livewire action
  -> validation + authorization entry point
  -> application service
  -> transaction + domain state
  -> after-commit event / notification / job
  -> response or rendered state
```

- Controllers stay thin: receive a FormRequest when appropriate, call one
  service/use case and return a response. They do not contain business rules,
  transactions, raw persistence or cross-domain orchestration.
- Livewire components own UI state, interactive validation and presentation.
  They must delegate persistent writes to a Core/module application service.
- Services own a use case and may query models. Keep a service method named as
  a business outcome (`approveDeviceAuthorization`, `publishCv`,
  `submitChallenge`) rather than a vague transport action (`handle`, `store`).
- Model casts, relations, local scopes and invariants belong on models; models
  do not format HTTP responses or inspect the current request.
- Keep files focused. Split a service by use case when it is difficult to test
  independently or begins to coordinate unrelated workflows.
- The architecture test preventing direct Eloquent writes in controllers and
  Livewire is deliberate. Do not weaken it to make a feature pass.

## Writes, transactions and side effects

- Wrap any business write that changes more than one record, spends credit,
  changes access, receives payment, changes status, or needs audit consistency
  in `DB::transaction()` (or a project service using the same boundary).
- Lock or use database uniqueness for concurrent state transitions. Application
  `exists()` checks alone are not enough for payments, credits, enrollments,
  activations, invitations or submissions.
- Persist the source-of-truth state inside the transaction. Send mail,
  notifications, queue jobs, cache invalidation and external callbacks only
  with `DB::afterCommit()` or an event/listener configured after commit.
- Make retries safe. A duplicate request, queued retry or provider retry must
  not charge, enroll, issue credit, activate a license or send an irreversible
  transition twice.
- Use finite-state enums and explicit allowed transitions for payment, order,
  submission, recruiter and license state. Reject impossible transitions rather
  than silently overwriting them.

## API and integration contract

- New external APIs use `/api/v1` and `ApiResponse`: `success`, `status`,
  `message`, plus `data` or `errors`. Use API Resources for response shape and
  FormRequests (or a purpose-built request validator) for external input.
- Legacy endpoints, route names, URLs, slugs and response contracts are
  compatibility commitments. Keep them unchanged; introduce a new version in
  parallel and migrate the client deliberately.
- Central exception mapping lives in `bootstrap/app.php`. Services throw
  meaningful exceptions; individual controllers must not invent inconsistent
  JSON error shapes.
- Never let client input choose a class name, database column, order clause,
  tenant, price, entitlement, admin flag or file path. Map it through an
  allow-list or server-side lookup.
- API Resources must expose the minimum necessary fields. CV candidate search,
  billing data, raw device fingerprints, OAuth data and internal audit metadata
  are private by default.

### Webhooks and machine clients

- Validate the provider signature/key and rate limit **before** parsing or
  applying a webhook payload.
- Store a webhook event/receipt with a unique provider + external event id (or
  an equally stable provider idempotency key) before the business effect.
- Process the business effect in a transaction and treat duplicate events as a
  successful no-op response.
- Log only safe identifiers and decision metadata. Never log an access token,
  Google credential, raw fingerprint, invoice details or full sensitive
  payload.
- Revit clients keep their established `/api/revit/*` contract until the
  launcher is deliberately upgraded. Any new Revit contract is versioned and
  checked against user, DSCons `brand_id`, installation and entitlement.

## Data, migrations and privacy

- Add a migration for every schema change. Never compensate for missing schema
  with `Schema::hasColumn()` branches in normal queries.
- Define foreign keys, indexes and unique constraints for the actual invariant:
  common examples are `(brand_id, user_id)`, one active device per user/platform,
  or provider event idempotency.
- Design the migration for both a fresh install and current data. Backfill
  legacy data deterministically, in chunks if necessary, and preserve prior
  records unless the change explicitly authorises deletion.
- Do not use EAV/meta for core domain properties, tenant keys, permissions,
  statuses, money, entitlement or fields that require indexing/querying. Use
  typed columns and constraints.
- Use decimal/integer minor units and server-side prices for commerce; never
  trust a displayed client price.
- Treat billing, identity, contact details, OAuth identifiers, tokens and
  device fingerprints as sensitive data: encrypt/hash when appropriate,
  minimise exposure, restrict queries and exclude them from logs/resources.

## Testing and quality gates

Every backend feature needs the tests that correspond to its risk, not merely a
happy-path screen test.

| Change type | Required evidence |
| --- | --- |
| Community-scoped data | Same-community success and cross-community read/write denial |
| Permission change | Every relevant role allowed/denied; Super Admin and community roles covered separately |
| Write/state transition | Service/feature tests for valid, invalid and repeated/concurrent transition |
| Migration/backfill | Fresh migration plus current-data/backfill assertions where applicable |
| API/webhook | Validation, response contract, auth/signature, idempotency and sensitive-field absence |
| Payment/credit/license | Duplicate/concurrent request protection, audit, after-commit side effect and rollback behaviour |
| Livewire/controller | Presentation delegates writes; service handles business rule |
| UI that invokes backend | Targeted browser/mobile smoke only when the flow or accessibility changes |

Before a safe checkpoint or pull request, run the smallest complete set below:

```powershell
docker compose exec -T app composer lint
docker compose exec -T app composer stan
docker compose exec -T app composer test
git diff --check
```

For migrations, run the PostgreSQL migration path. For changed Blade/Vite/UI,
also run the relevant build, view cache and browser smoke. For security,
webhook, upload, payment, recruiter or Revit changes, run the project security
scan and the focused abuse-case tests. CI remains the final independent gate;
never introduce a PHPStan baseline, lower level 8, broad suppression or a test
skip to make it green.

## Explicitly not adopted from KP3

The following KP3 ideas were reviewed and intentionally are **not** defaults in
DSCons:

- Static `HookManager`: use Laravel events/listeners and after-commit work.
- EAV/meta engine for core business data: use typed schema and migrations.
- Sanctum SPA/API-first conversion: DSCons remains a Livewire/web product;
  version only external APIs that need contracts.
- General cache engine/tag system: add a measured, narrowly scoped cache only
  after a performance need is demonstrated.
- CSP with `unsafe-eval`: DSCons keeps CSP Report-Only until its actual
  Livewire/Vite requirements are resolved, then hardens without copying that
  exception.
- Mandatory Pest conversion: keep the existing PHPUnit suite; consistency and
  coverage matter more than framework churn.

## Review stop conditions

Stop and redesign before implementation if any answer is unknown for a feature
that writes data, changes permission, crosses a community boundary, handles
money/credit/license or exposes an external API:

1. Which module owns this state and which actor is authorised?
2. Which `brand_id` scope applies, and how is cross-community access denied?
3. What is the transaction boundary and what can safely happen after commit?
4. What database constraint makes retries/concurrency safe?
5. Which existing URL/API/data contract must remain compatible?
6. What tests prove both the allowed path and the abuse path?

Record the answers in the pull request/commit description or the feature's
implementation note. If the feature crosses two business modules, define a
Core contract or domain event before writing the implementation.
