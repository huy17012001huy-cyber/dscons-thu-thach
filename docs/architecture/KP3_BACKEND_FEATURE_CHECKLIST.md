# DSCons Backend Feature Checklist

Use this compact checklist with
[`KP3_BACKEND_GUARDRAILS.md`](KP3_BACKEND_GUARDRAILS.md) before implementing or
reviewing any backend-impacting feature. Add the answers to the task note, PR
description or commit message when the change is non-trivial.

## 1. Classify first

- [ ] Module owner selected: Core / Community / Learning / Commerce /
      Recruitment / RevitTools.
- [ ] Actor and permission class identified: Super Admin, community role,
      engineer/recruiter account type, verification and/or commercial state.
- [ ] Community scope named: active `brand_id`, deliberately global, or not
      community-scoped.
- [ ] Existing URL, route name, slug, legacy API or client contract checked.
- [ ] Sensitive data and external side effects identified.

## 2. Design the boundary

- [ ] Controller/Livewire action only coordinates UI/request work; a Core or
      module application service owns the use case.
- [ ] No direct module-to-module import. Cross-domain communication uses a Core
      contract or Laravel domain event/listener.
- [ ] `CommunityContext` supplies the active community. Browser input cannot
      select another tenant.
- [ ] Every related model is checked against the same `brand_id` before a write.
- [ ] Any `withoutGlobalScopes()` has explicit authorization, a `brand_id`
      filter where applicable and a cross-community test.

## 3. Make writes safe

- [ ] State transitions use an enum/explicit transition rule where relevant.
- [ ] The transaction boundary includes all source-of-truth writes.
- [ ] Unique index, lock or idempotency key protects retry/concurrency.
- [ ] Notification, job, cache work and external calls happen after commit.
- [ ] Audit entry covers role, ownership, payment, credit, privacy,
      impersonation or license decisions where relevant.

## 4. Protect interfaces and data

- [ ] New external API is under `/api/v1`, uses a request validator, Resource
      and the standard `ApiResponse` envelope.
- [ ] A legacy endpoint is preserved or a versioned migration path exists.
- [ ] Webhook validates signature/key before parsing and stores an idempotent
      provider event receipt.
- [ ] Rate limiter and abuse cases considered for login, admin, webhook,
      device authorization, uploads and impersonation.
- [ ] Tokens, OAuth data, raw device fingerprints, billing data and full
      sensitive payloads are excluded from logs and public resources.

## 5. Preserve data and verify

- [ ] Schema change has a migration, correct FK/index/unique constraint and
      fresh/current database path considered.
- [ ] Backfill is deterministic and preserves existing data.
- [ ] Tests cover allowed and denied roles, same-community and cross-community
      behaviour, and invalid/repeated state transitions.
- [ ] API/webhook tests cover validation, auth/signature, response shape and
      idempotency when relevant.
- [ ] `composer lint`, `composer stan`, relevant PHPUnit suite and
      `git diff --check` pass; migrations/UI/security checks are added when the
      change type requires them.

## Stop rule

Do not code around an unanswered question about ownership, `brand_id`, database
invariant, transaction/after-commit side effect or compatibility. Resolve the
design first; that is cheaper than a data/permission refactor later.
