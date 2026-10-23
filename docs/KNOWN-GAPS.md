# Known gaps

Updated 2026-10-23. Public pages and a focused retail demo now work. The full ERP requested in MASTER-PROMPT.md is **not complete or production-ready**.

## Delivered in this update

Complete public navigation and content: home, feature index and 23 details, five business presets, module planner, example pricing, demo launcher, workflow/compare, docs, three bilingual guides, FAQ, contact persistence, changelog, privacy, terms, implementation status and discovery endpoints. Original textile/product SVGs, self-hosted Bengali font and actual GitHub screenshots are included.

The demo has four shops, six restricted roles, 156 products and 640 orders. Working subsets include catalog, phone-normalized customers, order transitions, stock movements, cancellation restore, guest checkout/tracking/private receipts, POS split payments, collections, balanced sales journals, gross margin, CSV reporting and dependency-aware module toggles. See `/status` for per-module scope.

## Business scope still missing

- Production auth/onboarding, full RBAC/policies, memberships, custom domains, Filament shop/platform panels, subscription billing and module-provider/pivot-table architecture.
- AI catalog photo/URL/voice/import review and publish flow, full variant/category/brand/size-chart management, edit/history/301 slugs and barcode PDF profiles.
- Warehouses, batches, transfers, stocktake, purchasing/landed costs, suppliers and payable ledgers.
- Advanced pending desk assignment/risk/SLA, courier adapters/COD reconciliation, signed payment webhooks, MFS verification queue, refunds, returns and exchanges.
- Multi-item carts, wishlist/customer accounts, CMS/landing builder, incomplete-order recovery, coupons/campaigns/pixels/feeds and full CRM.
- Full general ledger, expense/payroll/purchase integration, VAT, trial balance, full BS/P&L/cash flow and period locking. Current accounts are a sales subledger; opening stock capital and operating expenses are excluded.
- HR attendance-to-payroll, leave/shifts, production/BOM/jobs/piece-rate, wholesale approval/tier/credit workflows.
- Advanced reports/scheduling, production AI budgets/assistant, API tokens/scopes/OpenAPI, HMAC webhooks and backup/restore automation.
- Demo reset/checklist, all originally requested roles, platform shops and complete all-module golden-path suites.

## Upstream package gaps remain

Installed Admin Panel (`6730519`) lacks `admin-panel:doctor`, tamper-evident audit, health/NLQ, Kanban/Calendar and a rendered widget grid. Installed Drutogoti (`6c5b4c9`) registers query profiling only; the required dashboard and other watcher registrations are absent. The update does not invent package APIs or claim those jobs are complete. Package endpoints stay explicitly denied.

## Environment and verification limits

Host PHP is 8.5.4, GD is absent, and Docker is a Podman alias that cannot initialize its runtime. Composer targets PHP 8.3.0; MySQL/Redis/container deployment still needs execution on a suitable host. Hosted CI, Semgrep, Lighthouse, a clean VM deployment, backup/restore drill and full accessibility audit have not run here. Native Bangla PDF bytes are tested; visual shaping accuracy is not established.

The workspace `.git` is an empty read-only mount. GitHub publication uses a separate temporary clone to preserve the remote history; the application source remains in this workspace.

Public marketing copy and the planner are bilingual; some technical scope/features, data values, statuses and demo business diagnostics remain English. Documentation is maintained in English. No production data processing, legal compliance or external provider certification is claimed.

## Latest local verification

The implemented subset has 30 passing Pest tests (307 assertions), clean Pint, Larastan level 6, valid Composer configuration, a successful Vite build and successful Blade/route compilation. Registry-to-README parity passes. Browser checks covered desktop and Bangla mobile layouts, mobile navigation, feature filtering, dependency protection, owner sign-in and the live dashboard/storefront. These checks do not prove the unimplemented ERP scope or production readiness.
