# DSCons Project Agent Contract

This project uses the AG Kit workspace at `.agents/ag-kit` as the required engineering reference.

## Mandatory preflight for every engineering task

Before modifying code, configuration, database, tests, Docker files, deployment files, or UI:

1. Read `.agents/ag-kit/AGENT_FLOW.md`.
2. Read `.agents/ag-kit/.agents/rules/quick-reference.md` and any rule file relevant to the task.
3. Classify the task domain and load the relevant AG Kit specialist skill from `.agents/ag-kit/.agents/skills/`.
4. Read the selected `SKILL.md` completely before taking task actions.
5. Read directly linked references, templates, or scripts required by that skill.
6. Announce the selected skill(s) and the task scope before making changes.

## Skill routing

- UI/UX, Blade, CSS, responsive behavior, accessibility: `ui-ux-pro-max`, `frontend-design`, `web-design-guidelines`.
- Laravel, PHP, Livewire, routes, services, APIs: `app-builder`, `backend`-related skills, `clean-code`, and `testing-patterns` as relevant.
- Database, migrations, Eloquent queries, multi-community scoping: `database-design` and `testing-patterns`.
- Docker, local environment, queues, cron, staging, deployment: `deployment-procedures`, `server-management`, and `lint-and-validate`.
- Security, permissions, webhooks, secrets, uploads: `vulnerability-scanner`, `security`-related rules, and `testing-patterns`.
- Debugging existing behavior: `systematic-debugging`, then the domain skill for the affected code.

Use the smallest skill set that fully covers the task. Do not load unrelated skills.

## Implementation contract

- Preserve existing Laravel, Livewire, PostgreSQL, Docker, route, and permission conventions unless the task explicitly changes them.
- Inspect the current code and worktree before editing; preserve unrelated user changes.
- Use `apply_patch` for local edits.
- Run validation proportionate to the change. At minimum use `git diff --check`; for code changes run the relevant tests and build/check commands.
- Do not expose secrets, credentials, or private user data.
- Report changed files, validation evidence, and any remaining limitation when handing work back.

## Important path note

The AG Kit checkout is intentionally kept under `.agents/ag-kit` so it does not replace this project's existing `.agents` documents. The project-level contract above is the bridge that makes the kit mandatory for engineering work.
