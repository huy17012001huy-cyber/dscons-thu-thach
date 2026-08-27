# ADR-001: Modular monolith for DSCons

## Status

Accepted

## Context

DSCons is a Laravel and Livewire product with multi-community data, learning,
commerce, recruitment and Revit tooling. Splitting it into services now would
increase deployment and transaction complexity without a measured need.

## Decision

Keep one Laravel application and introduce module boundaries under `modules/`.
Modules depend on `App\\Core` only. Cross-module collaboration uses contracts
or Laravel domain events; it must not reach into another module's internals.

The module loader discovers enabled module manifests and lets each provider
register routes, views, translations, configuration and new migrations. Legacy
migrations stay in `database/migrations` so their historic order is preserved.

`CommunityContext` is the only application service used to resolve the active
community. Queries that intentionally bypass tenant scope must be explicit and
authorized.

## Consequences

- Existing URLs, route names and Livewire pages remain compatible while logic
  moves incrementally.
- Laravel events/listeners replace global static hooks. Side effects run after
  transaction commit.
- KP3's EAV, static hook manager and API-first Sanctum SPA are intentionally
  not adopted because they do not solve a current DSCons requirement.
