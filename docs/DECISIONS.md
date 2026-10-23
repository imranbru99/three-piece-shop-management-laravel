# Decisions

| Date | Decision | Reason |
| --- | --- | --- |
| 2026-10-23 | Pin Laravel to ^12.0, Filament to ^3.3, Composer platform PHP to 8.3.0 | Preserve the requested stack even though the host runs 8.5.4. |
| 2026-10-23 | Create Laravel in /tmp then copy into the existing workspace | Workspace contained protected mount directories; create-project requires an empty target. |
| 2026-10-23 | Treat vendor source as authoritative; leave Phase 0 incomplete | Admin Panel and Drutogoti source does not implement required capabilities. Do not invent APIs, substitute packages or quietly skip acceptance gates. |
| 2026-10-23 | Keep all package UIs/APIs denied during foundation work | Package defaults can expose endpoints locally before authentication and tenancy exist. Replace the blocker only after isolation/policy tests pass in Phase 1. |
| 2026-10-23 | Use SQLite only for host spike and quick tests | MySQL remains the target; Docker and MySQL CI config supplied. Host container runtime is unavailable. |
| 2026-10-23 | Add Pest 3, Laravel Pest plugin and Larastan 3 | Explicitly required testing and static analysis; Laravel Pint comes with the skeleton. No extra business dependencies added. |
| 2026-10-23 | Keep .env private and .env.example free of secrets | Framework-generated local APP_KEY must not be committed. |
| 2026-10-23 | Leave vendor source unchanged | Pin source references in composer.lock; missing functionality must not be masked by local vendor edits. |

## Public-site update — 2026-10-23

- The user's follow-up explicitly prioritizes public pages and business gap closure, so proceed beyond the earlier phase gate for this working demo while retaining the unresolved package findings.
- Keep Laravel/Blade in the existing repository; use Sites design guidance locally, without creating or publishing a separate hosted project.
- Use original SVG textile/product art and actual local screenshots instead of external imagery. Reuse bundled Noto Bengali with its license.
- Use a minimal JSON module registry and a Blade workspace for a working demo; production Filament panels and full module provider architecture remain gaps rather than falsely claiming those requirements are complete.
- Make every public CTA functional: demo sign-in, storefront order, module planner, documentation or persisted contact interest. Example pricing does not activate billing.
- Store all money in integer poisha, calculate prices server-side, use transactional locks and idempotency, and test rollback/stock restoration across module changes.
- Public contact messages remain private database records; no outbound messaging is introduced.
