# MASTER PROMPT — "Three-Piece Shop"
### A complete, modular business system for Bangladeshi women's three-piece (থ্রি-পিস) retailers, wholesalers, online sellers and tailors

> Hand this whole file to your AI code agent (Claude Code or similar) as the task. It is written to be executed end to end, phase by phase, with no questions asked.

---

## A. YOUR MISSION AND OPERATING RULES

You are the lead engineer. Build a **complete, production-grade, demo-ready business application** in **PHP / Laravel with Filament** for owners of **girls' / women's three-piece shops in Bangladesh**. Any kind of three-piece owner must be able to run their whole business on it: online-only Facebook sellers, boutiques with a counter, importers and wholesalers, and tailor/manufacturer businesses. Each owner **turns modules on or off** depending on what their business needs.

The finished repository must contain:

1. The working application (admin panels, POS, public storefront, landing pages, APIs, queues).
2. **Several public marketing pages** that show every feature of the product, generated from the same module registry that produces the feature list in `README.md`.
3. **A complete demo business with demo users** (all roles) and realistic Bangla/English data, so a visitor can click "Try demo" and use every module immediately.
4. `README.md` documenting every feature, every module, install, demo logins, architecture and package usage.
5. Tests, CI, and deployment docs.

### Rules of engagement (non-negotiable)

- **Do not ask me questions.** When something is ambiguous, choose the most reasonable option, and record it in `docs/DECISIONS.md` (decision, reason, date). Only stop for something that cannot be undone and could reasonably go either way.
- **No stubs, no "coming soon", no dead menu items.** Every navigation entry, button and public page must work against real data. If a feature is genuinely too large, ship a smaller working version and list the gap in `docs/KNOWN-GAPS.md`.
- **Never invent package APIs.** The seven `imrandevbd/*` packages are installed from `dev-main` (mostly untagged). After installing, **read each package's README and source under `vendor/imrandevbd/`** and follow what is really there. If this prompt and the package disagree, the package wins; log it in `docs/DECISIONS.md`.
- **Work in phases (Section K).** At the end of each phase: migrations + seeders (extend the demo seeder), Pest tests green, Pint and Larastan clean, README/feature docs updated, one conventional commit (`feat(pos): ...`). Do not start the next phase with a red build.
- **Never delete tenant data when a module is switched off.** Switching a module off hides and pauses it; data stays.
- **No real external calls in demo or tests.** SMS, courier, payment and AI integrations each have a `fake`/sandbox driver. Demo mode uses fakes unless real keys are provided.
- **Everything user-facing is translatable (`bn` and `en`)**, and money, phone and address handling follow Bangladesh conventions (Section E).
- Prefer few dependencies. Allowed extras when justified: Filament v3 plugins (e.g. Shield or spatie/laravel-permission for RBAC), spatie/laravel-activitylog, spatie/laravel-backup, maatwebsite/excel, intervention/image, picqer/php-barcode-generator, pestphp/pest, larastan/larastan, laravel/pint, laravel/horizon (optional). Each addition gets a line in `docs/DECISIONS.md`.

---

## B. PRODUCT DEFINITION

### B1. What a three-piece is (domain you must model)
A **three-piece** is a set of **kameez / kurti (top)**, **salwar / pant / palazzo / trouser (bottom)** and **orna / dupatta (scarf)**. It is sold as:

| Form | Meaning |
|---|---|
| **Unstitched** (আনস্টিচড) | Fabric pieces cut to length, buyer stitches it. Track fabric in pieces and in meters/yards. |
| **Semi-stitched** | Partly ready (e.g. top stitched, bottom as fabric). |
| **Ready-made / stitched** (রেডিমেড) | Sized stock: S/M/L/XL/XXL or numeric 36–44, with size chart. |
| **Custom-stitch / made-to-order** | Customer measurements taken, tailor job created, delivered later. |
| **2-piece / 1-piece / mix-and-match** | Sold as partial sets or by component. |

### B2. Who uses it (business types → starter presets)
Presets only set the **initial module toggles**. The owner can change any toggle later.

| Preset | Typical owner | Modules switched on at start |
|---|---|---|
| `online_seller` | Facebook/Instagram/WhatsApp page seller | catalog, orders, order_confirmation, landing_pages, storefront, courier, payments, customers, marketing, ai, reports |
| `boutique_retail` | Shop with a counter (+ small online presence) | catalog, inventory, pos, orders, customers, crm, accounts, hr, reports, storefront |
| `wholesale_importer` | Wholesaler / importer of local, Indian or Pakistani sets | catalog, inventory (multi-warehouse, batches), purchasing, wholesale, accounts, orders, customers, reports |
| `manufacturer_tailor` | Boutique that stitches, tailor house, small factory | catalog, production, inventory, purchasing, orders, hr (piece-rate), accounts, reports |
| `omnichannel_chain` | Multi-branch owner | everything |

### B3. Product goals
- A new owner is selling within 15 minutes: sign up → pick a preset → AI-assisted product import → publish storefront or open POS.
- Staff who are not technical (cashier, call agent, packer) use a simplified UI with only what their role needs.
- Fast on cheap Android phones over mobile data (storefront and landing pages are server-rendered and light).

---

## C. TECH STACK (fixed)

- **PHP 8.3**, **Laravel 12** (pin to 12 — `imrandevbd/laravel-attendance-hub` supports Laravel 10–12 and `imrandevbd/drutogoti` supports 11–12; do **not** use Laravel 13).
- **Filament v3** (latest 3.x) for the owner/staff and platform panels (the attendance-hub plugin is Filament v3).
- **MySQL 8** (default), Redis for cache/queues/sessions/locks, Laravel Queues + Scheduler. Horizon optional.
- Storefront and public pages: **Blade + Tailwind CSS + Alpine.js** (server-rendered; add Livewire only where it clearly pays off, e.g. cart/checkout). Self-host a Bangla web font (Noto Sans Bengali or Hind Siliguri, subset, `font-display: swap`).
- Tests: **Pest** + Livewire testing helpers. Static analysis: **Larastan level 6+**. Style: **Pint**.
- Timezone `Asia/Dhaka`, default currency **BDT (৳)**, default languages `bn` and `en`.

Composer setup (do this first):

```bash
composer create-project laravel/laravel three-piece-shop "^12.0"
cd three-piece-shop
composer config minimum-stability dev
composer config prefer-stable true
composer require filament/filament:"^3.3"
composer require \
  imrandevbd/drutogoti:dev-main \
  imrandevbd/laravel-admin-panel:dev-main \
  imrandevbd/laravel-ai-hub:"^1.6" \
  imrandevbd/laravel-ai-catalog:dev-main \
  imrandevbd/laravel-attendance-hub:dev-main \
  imrandevbd/laravel-unicode-pdf:dev-main \
  imrandevbd/laravel-universal-slug:dev-main
```

---

## D. THE SEVEN PACKAGES — INTEGRATION CONTRACT

You **must** use these packages for the listed jobs. Do not re-implement what they provide.

| Package | Job in this product | Where it is used |
|---|---|---|
| `imrandevbd/drutogoti` | Speed: finds N+1 queries, slow queries, cache misses, cookie bloat, unoptimized images; sampled, queue-offloaded profiling | Production performance monitoring, CI perf checks, super-admin "Performance" link |
| `imrandevbd/laravel-admin-panel` | Dashboard: widget grid, Kanban/Calendar views, health diagnostics, tamper-evident audit log, natural-language data queries, headless API docs | The **Insights** panel (`/insights`) for owners; see D8 for how it coexists with Filament |
| `imrandevbd/laravel-ai-catalog` | Scrape/generate product data from **photo, keyword, URL, voice, datasheet, CSV/JSON**; Bangla/English listings, SEO, JSON-LD, taxonomy matching, return-reduction/fit advice, social video scripts, `llms.txt`, Google/Facebook feeds, catalog audit | Product creation, bulk import, storefront SEO, marketing |
| `imrandevbd/laravel-ai-hub` | One interface to any AI provider (OpenAI, Gemini, Claude, Grok, DeepSeek, Mistral, Groq, Ollama, …), failover, structured JSON, streaming, chat sessions, cost tracking, `AIHub::fake()` | All AI features; also the engine under ai-catalog |
| `imrandevbd/laravel-attendance-hub` | Any attendance machine (ZKTeco, Hikvision, eSSL, Anviz, FingerTec, Suprema, …), ADMS push, webhook bridge, virtual GPS/QR check-in, shift calculator, `AttendanceHub::fake()`, `attendance:simulate` | HR module |
| `imrandevbd/laravel-unicode-pdf` | PDFs in Bangla (and any script) with correct conjuncts, numerals, RTL, font fallback | Invoices, receipts, labels, challans, payslips, reports, catalogs |
| `imrandevbd/laravel-universal-slug` | Unicode-safe slugs for any language, per-model unique slugs, 301 slug history, reserved words, validation rules | Products, categories, brands, pages, landing pages, blog |

### D1. drutogoti (speed)
- `composer require` done above. Publish: `php artisan vendor:publish --tag="drutogoti-config"` and `--tag="drutogoti-migrations"`, then migrate. Requires PHP ^8.3.
- Production `.env`: `DRUTOGOTI_ENABLED=true`, `DRUTOGOTI_STORAGE_DRIVER=queue`, `DRUTOGOTI_QUEUE_CONNECTION=redis`, `DRUTOGOTI_SAMPLE_RATE=0.1`. Local/CI: sample rate `1.0`, driver `sync`.
- Restrict its dashboard (`/drutogoti`) to **platform super-admins** via the config middleware.
- Add `exclude_paths` for `up`, `livewire*`, `filament*` asset routes, `ai-hub*`, `iclock*`.
- Also enable `Model::preventLazyLoading(! app()->isProduction())` and write a Pest test that loads the heaviest pages (POS, order list, storefront home/category/product) and asserts no N+1 and a query-count ceiling.
- Add a scheduled job that emails/Telegrams the super-admin a weekly digest of new high-severity Drutogoti suggestions.

### D2. laravel-ai-hub
- `php artisan vendor:publish --tag=ai-hub-config` + migrate. Facade: `ImranDevBd\AiHub\Facades\AIHub`.
- Protect Studio (`/ai-hub`) with `AIHub::auth(...)` → platform super-admins and shop owners only. Keys are configured per platform in the Studio/`.env`; shops can optionally bring their own key (stored encrypted).
- **Tag every request** with `->forJob("shop:{$shop->id}:{$feature}")` so cost can be reported per shop and feature; enforce a **per-shop monthly AI budget** (owner-visible) using the package's budget/cost APIs plus your own per-shop counter.
- Use `AIHub::structured($schema)` / `asJsonSchema()` for anything machine-read (chat-to-order extraction, invoice OCR, size advice).
- Tests use `AIHub::fake()`; demo mode uses canned responses unless a key is configured.

### D3. laravel-ai-catalog
- Publish config (`--tag=ai-catalog-config`) and migrate. Facade: `ImranDevBd\AiCatalog\Facades\AiCatalog`. Studio UI at `/ai-catalog` (restrict like ai-hub).
- **Schema mapping** (`config/ai-catalog.php`): map to the real `Product` model. Product text fields are stored in the shop's primary language; other languages in a `translations` JSON column — map `translations` there.
- **Taxonomy**: bind to the `Category` model so categories are **never invented**; unmatched ones are quarantined and shown to the owner.
- **Tenancy**: set `ai-catalog.tenancy` resolver to the current shop so drafts, costs and feeds are per shop.
- **Settings for this market**: default locale `bn`, extra locale `en`; `region('bd')` compliance pack (flags counterfeit/"guarantee" style claims); turn **return-reduction ON** (fit tendency, tactile fabric copy, size FAQs are exactly what three-piece buyers need); **social scripts ON** (TikTok/Reels/Shorts for the marketing module); **DPP and spatial OFF**; embeddings optional.
- Inputs to expose in the UI: photo (multiple), keyword, supplier/competitor URL (this is the "scrape from online data" feature), voice note, datasheet/PDF, bulk CSV/JSON. Everything lands as a **draft → review → approve** workflow; approval writes to the live product table.
- Filament: register the plugin (`AiCatalogPlugin`) and add `GenerateWithAiAction` to the Product resource.
- Per-shop `llms.txt` route (`/{shop}/llms.txt`) using `AiCatalog::llmsTxt(...)`; Google and Facebook feeds use `AiCatalog::googleFeed()` / the export command, scoped per shop.
- Scheduled `ai-catalog:audit` per shop (health score on the dashboard) and `ai-catalog:refresh-stale`.

### D4. laravel-attendance-hub
- Publish migrations/config, migrate. Facade `ImranDevBd\AttendanceHub\Facades\AttendanceHub`; Filament plugin `ImranDevBd\AttendanceHub\Filament\AttendanceHubPlugin` (register in the **shop panel only when the `hr` module is on**).
- Map `tenancy.tenant_resolver` to the current shop id; map device PINs to `employees` via `device_user_maps`.
- Support: LAN pull (ZKTeco needs `ext-sockets`), **ADMS push endpoint `/iclock/cdata`** (needed for shops behind NAT — document the nginx location block), webhook bridge for ESP32/RPi, **virtual GPS/QR check-in** (mobile web for staff), `AttendanceCalculator` for late/overtime/half-day, debounce + anti-passback pipeline, HMAC webhooks.
- Listen to its `AttendanceRecorded` event to build `attendance_days`; feed payroll.
- Demo: `php artisan attendance:simulate --employees=12 --days=30`; tests use `AttendanceHub::fake()`.

### D5. laravel-unicode-pdf
- Publish config, views, fonts; run `php artisan unicode-pdf:font:install --font=bengali` and `--font=universal` as part of setup and deploy (SIL OFL fonts).
- Facade `ImranDev\UnicodePdf\Facades\UnicodePdf`. Define named **profiles** in `config/unicode-pdf.php` for: `invoice` (A4), `receipt` (80 mm and 58 mm thermal via custom paper size), `barcode-label` (e.g. 50×30 mm), `challan`, `payslip`, `job-card`, `measurement-slip`, `ledger-statement`, `daily-closing`, `price-list-catalog`, `purchase-order`.
- Per-shop setting "Bangla numerals" → use `NumeralConverter`/`@unicodeNumerals`. Taka sign ৳.
- Heavy PDFs (statements, catalogs, bulk labels) go through `->queue()`; frequently re-printed ones use `->cache()`.
- Tests use `UnicodePdf::fake()`; add a visual smoke test that renders a Bangla invoice with conjuncts (e.g. শিক্ষার্থী, কৃষ্ণ) and asserts the PDF is generated without exceptions.

### D6. laravel-universal-slug
- Publish config and migrations (slug history). Use `HasUniversalSlug` (+ `HasSlugHistory`) on `Product`, `Category`, `Brand`, `Page`, `BlogPost`, `LandingPage`, `Collection`. Prefer the PHP 8 `#[Slug(...)]` attribute where simple.
- **Per-shop uniqueness**: `uniqueScope(fn ($q, $m) => $q->where('shop_id', $m->shop_id))`.
- Default mode `preserve_unicode` (Bangla URLs). Per-shop setting **"URL style"**: *Bangla* (`preserve_unicode`) or *English* (`transliterate`/`ascii_only`) — Facebook/WhatsApp sharing is easier with ASCII links, so default new shops to *English* and let them switch.
- Extend the reserved list with this app's routes: `cart`, `checkout`, `track`, `pos`, `app`, `platform`, `insights`, `s`, `demo`, `features`, `pricing`, `blog`, `landing`, `api`, `login`, `register`.
- Public routes bind with `{product:slug}`; with `HasSlugHistory` old links **301** to the new one. Filament slug inputs use `Rule::universalSlug()` and `Rule::uniqueUniversalSlug(...)->ignore(...)`.
- Sitemaps must percent-encode Bangla slugs; set canonical URLs.
- CI runs `php artisan slug:audit` for each sluggable model on the demo data.

### D7. Package spike (Phase 0, mandatory)
Before any feature work: install all seven, publish everything, read each README and `src/`, and write `docs/PACKAGE-NOTES.md` listing the **actual** namespaces, facades, config keys, publish tags, migrations and Filament classes you found. Run each package's smoke command (`attendance:simulate`, `unicode-pdf:diagnose`, `slug:generate`, `ai-hub:configure --show`, `admin-panel:doctor`). Fix environment gaps (PHP extensions such as `intl`, `gd`, `sockets`, `mbstring`).

### D8. laravel-admin-panel vs Filament (resolve explicitly)
`laravel-admin-panel` is a standalone, Filament-independent panel framework with its own RBAC, auth and widgets. This product's main UI is Filament, and ai-catalog/attendance-hub plug into Filament. Therefore:

- **Filament v3 is the primary UI**: `platform` panel (`/platform`) for the SaaS operator and `app` panel (`/app/{shop}`) for shop owners/staff (use Filament's built-in tenancy with `Shop` as the tenant).
- **`laravel-admin-panel` powers the Insights dashboard** at `/insights` as a second panel (`Panel::make('insights')->path('insights')`): the 12-column drag-and-drop widget grid (today's sales, cash position, pending orders, low stock, courier performance, AI cost), Kanban boards (orders by status, production jobs), Calendar views (deliveries, tailoring due dates, Eid/season campaigns), natural-language "ask your business" (NLQ), the system-health center, and the **tamper-evident audit log** view.
- **One identity, one permission source.** Both panels authenticate against the same `users` table and the same guard. Our roles/permissions (Section E4) are the single source of truth; adapt the admin-panel's authorization to Laravel Gates that read ours. Add a signed one-click "Open Insights" link from the Filament panel so users never log in twice.
- Apply the same `shop_id` global scope to everything the Insights panel reads.
- If after Phase 0 the two truly cannot share auth cleanly, fall back to: Filament dashboards for widgets, and use admin-panel only for health, audit and NLQ; document the decision. Do **not** silently drop the package.

---

## E. ARCHITECTURE

### E1. Multi-shop (SaaS) with one database
- Table `shops` (tenant). Every tenant-owned table has `shop_id` and uses a `BelongsToShop` trait with a global scope and auto-fill. Tenant isolation is the most important property in the system: write **isolation tests** (a user in shop A can never read, update, list, search, export or download anything from shop B — including via Filament relation managers, global search, public APIs, PDFs, queued jobs and cache keys).
- Platform panel: shops, plans, subscriptions (manual/offline billing with a payment-gateway-ready adapter), module catalog, impersonation (audited), announcements, global settings, health, AI/cost overview.
- Public shop routing: `/s/{shop-slug}` by default and optional **custom domain** per shop (`shop_domains`, resolved by middleware; document nginx/FastPanel setup).

### E2. Module system (the heart of "run or stop any module")
Create `app/Modules/<ModuleName>/` per module with a `ModuleServiceProvider` that registers a `ModuleDefinition`:

```php
// shape, not literal code
ModuleDefinition::make('pos')
    ->name(bn: 'পিওএস (বিক্রয় কাউন্টার)', en: 'Point of Sale')
    ->icon('heroicon-o-calculator')
    ->locked(false)                 // true for core/catalog/orders/customers
    ->dependsOn(['orders'])         // hard dependencies
    ->enhancedBy(['inventory'])     // soft: extra behaviour when also enabled
    ->permissions([...])
    ->filamentResources([...])->pages([...])->widgets([...])
    ->routes(web: ..., api: ..., public: ...)
    ->publicBlocks([...])           // storefront/landing blocks it contributes
    ->settings(SettingsSchema::class)
    ->scheduled(fn (Schedule $s) => ...)
    ->featureList(bn: [...], en: [...]);  // feeds README + public feature pages
```

- Tables: `modules` (catalog, seeded from registry) and `shop_modules` (`shop_id`, `module_key`, `enabled`, `enabled_at`, `settings` JSON).
- `ModuleManager::enabled('pos')`, `@module('pos')` Blade directive, `module:pos` route middleware, and a `HasModule` check for Filament resources/pages/widgets (`shouldRegisterNavigation`, `canAccess`).
- **When a module is OFF:** its navigation, routes, public blocks, widgets, scheduled jobs and queue listeners are inactive; direct URLs return a friendly "module not enabled" page with an Enable button (owner only); API returns 403 with a module code. **Data is retained.**
- **Dependencies:** enabling a module offers to enable its hard dependencies; disabling one that others depend on shows which modules would also be paused and requires confirmation.
- **Decoupling:** modules talk through domain events (`OrderPlaced`, `OrderDelivered`, `StockMoved`, `PaymentReceived`, `ReturnApproved`, …) and contracts. Example: if `accounts` is on, it listens to `OrderDelivered`/`PaymentReceived` and posts journal entries; if off, nothing breaks.
- A **"Modules" settings screen** for owners: card grid with description, what it adds, dependencies, ON/OFF switch, and "Apply a preset".
- Plans (platform) map to allowed modules; a shop can only enable modules its plan allows.

### E3. Money, numbers, dates
- Money stored as **integer minor units (poisha)** with a `Money` value object/cast; never floats. Display `৳১,২৫০.০০` or `৳1,250.00` by locale/setting.
- Per-shop sequences with prefixes for orders, invoices, POs, challans, returns (atomic, DB-locked): e.g. `NB-250123-0001`.
- Timezone `Asia/Dhaka`; Bangla and English month names; optional Bangla numerals.

### E4. Roles & permissions
Seeded roles per shop: **Owner, Manager, Cashier, Sales/Call Agent, Inventory Keeper, Packer, Accountant, HR, Tailor/Production Lead, Wholesale Customer (portal), Customer (storefront)**; platform roles: **Super Admin, Support**. Permissions are module-scoped (e.g. `pos.sell`, `pos.refund`, `orders.confirm`, `inventory.adjust`, `accounts.view_profit`). Hide cost price and profit from roles without permission. Everything sensitive is audit-logged.

### E5. Bangladesh localization
- Languages: complete `lang/bn` and `lang/en` for every panel, storefront, emails, SMS templates and PDFs. A language switcher everywhere. All strings through `__()`.
- **Phone**: accept `01XXXXXXXXX`, `+8801XXXXXXXXX`, `8801XXXXXXXXX`; normalize to one canonical form; validate operator range `01[3-9]\d{8}`; Bangla-digit input accepted.
- **Address**: division → district → upazila/thana → area/union + free-text; seed the 8 divisions, 64 districts and a usable upazila set, with an importer for the full dataset from a vetted open source (no network assumptions in tests). Delivery charge rules by inside-Dhaka / outside-Dhaka / zone / weight / order value, free-delivery threshold, courier-zone mapping.
- Payments: COD, bKash, Nagad, Rocket, Upay (manual send-money + TrxID verification), bank transfer, card; gateway adapters (SSLCommerz, AamarPay, ShurjoPay) behind one interface with a sandbox/fake driver.
- Optional VAT: configurable rate and an optional **Mushak-style VAT invoice template** (owner must confirm requirements with their accountant; mark this clearly in the UI).
- Festival calendar helper (Eid-ul-Fitr, Eid-ul-Adha, Pohela Boishakh, Puja, Winter, Wedding season) for campaigns and dashboard "season" widgets.

---

## F. THREE-PIECE DOMAIN MODEL

**Product (a "design")** — style code, name (bn/en), slug, category tree (e.g. Unstitched → Cotton → Printed), brand, **product form** (`unstitched | semi_stitched | ready_made | custom_stitch`), **set composition** (kameez, bottom, orna: each with its own fabric, color, length, optional separate stock), pieces-per-set (1/2/3), origin (Local / India / Pakistan / Other), fabric (cotton, lawn, georgette, linen, silk, muslin, jamdani, katan, chiffon, voile, khadi, viscose, rayon, tissue, cotton-silk, …), work type (block print, screen print, digital print, hand paint, batik, hand/machine embroidery, sequin, zari/jari, chumki, applique, …), occasion (daily, office, party, wedding, Eid, Puja, Boishakh, winter), season, care instructions, size chart (per product or shared), fit tendency, images/video, tags, SEO fields, `translations` JSON, status (draft/active/archived), visibility per channel (POS / storefront / wholesale / landing).

**Variant** — color + size (or "free size"/unstitched length), SKU, **barcode**, MRP, offer price, wholesale tiers, cost price / landed cost, weight, per-variant images, stock per location. **Stock mode** per product: *by set* or *by component* (kameez/bottom/orna separately, for mix-and-match shops).

**Bundles & offers** — "3-piece set" bundles, buy-N discount, combo pricing, flash sales with start/end.

**Measurement profile** (custom-stitch): customer, named profile, chest/bust, waist, hip, shoulder, sleeve length, armhole, neck (front/back), kameez length, salwar/palazzo length, bottom opening, notes, sketch/photo, version history.

**Orders**: unified for online, landing page, phone, Facebook/WhatsApp/Instagram, POS, wholesale.
Statuses: `pending` → `confirmed` → `processing` → `packed` → `ready_to_ship` → `shipped` → `out_for_delivery` → `delivered`; plus `on_hold`, `cancelled`, `returned`, `exchanged`, `refunded`, `partially_delivered`. Payment statuses: `unpaid | partial | paid | refunded`. Sources: `storefront | landing_page | pos | facebook | whatsapp | instagram | phone | manual | wholesale | marketplace`.

Also model: `customers`, `addresses`, `warehouses/locations`, `stock_movements` (ledger), `batches/lots`, `suppliers`, `purchase_orders`, `goods_receipts`, `couriers`, `shipments`, `payments`, `accounts` + `journal_entries`, `employees`, `shifts`, `attendance_days`, `payroll_runs`, `production_jobs`, `coupons`, `campaigns`, `landing_pages`, `pages`, `blog_posts`, `reviews`, `activity_log`.

---

## G. MODULE CATALOG (build all of these)

Keys in `code`. **Locked** modules are always on; the rest are toggleable.

### Locked core
1. **`core` — Shop, users, settings.** Registration/onboarding wizard with preset picker; shop profile and branding (logo, colors, fonts); roles/permissions; staff management; branches; global search; notification center; media library (WebP conversion, thumbnails); import/export (CSV/Excel) with templates; activity/audit log; language & numeral settings; backups.
2. **`catalog` — Products.** Everything in Section F. Admin list as table **and** grid (photos), quick filters (form, fabric, work, occasion, stock state), bulk edit (price, category, status, visibility), duplicate product, variant generator (colors × sizes), barcode/QR label printing (queued PDF), size-chart manager, brand/category/collection management, **AI creation via ai-catalog from photo/keyword/URL/voice/datasheet/CSV** with draft→review→approve, hallucination-free category matching, Bangla/English listing, JSON-LD, return-reduction fit advice, catalog health score.
3. **`orders` — All orders.** Single order list with powerful filters (status, source, courier, payment, date, district, agent), saved views, **Kanban by status** (Insights panel), order detail with timeline, edit before confirmation, split shipment, notes, tags, print invoice/challan/packing slip (Bangla PDF), bulk actions (confirm, print, assign courier, mark packed). **Quick Order entry** (phone/Facebook/WhatsApp): customer lookup by phone, product search by SKU/barcode/name, address auto-fill, delivery-charge auto-calc — target under 30 seconds per order. Stock reservation on confirm; invoice numbering.
4. **`customers` — Customers.** Profiles, multiple addresses, phone-based dedupe, order history, notes, tags, measurement profiles, customer groups, import.

### Toggleable
5. **`inventory`** — multi-location stock (shop floor, store room, branches), stock-movement ledger, adjustments with reasons, **stock transfers** between locations, **stocktake/cycle count** with barcode scanning, batches/lots for imported consignments, low-stock and dead-stock alerts, reorder levels, stock aging, reservations for online orders, negative-stock policy per shop, unstitched fabric in meters/yards where relevant.
6. **`purchasing`** — suppliers (with balance), purchase orders, goods receipt, **landed-cost** allocation (freight, customs, handling) for imported goods, supplier invoices/payments/ledger, purchase returns, price history, PO PDF, supplier-wise reports. Requires `inventory`.
7. **`pos`** — touch-friendly counter screen: barcode/SKU/name search, set-or-component sale, variants, discounts (line/cart/coupon/staff-approval), hold & resume carts, split payments (cash/bKash/Nagad/card/credit), customer attach (quick create by phone), loyalty redeem, **cashier shifts** (open/close, cash in/out, drawer reconciliation, Z-report), **returns & exchanges at the counter**, receipt print (80 mm / 58 mm / A4, Bangla), reprint, keyboard shortcuts, resilient to flaky connectivity (queue unsent actions client-side and sync; show state clearly). Works without `inventory` (no stock check) and uses stock when it is on.
8. **`storefront`** — the public online shop at `/s/{shop}` (or custom domain): home (banners, featured, new arrivals, offers, seasonal), category/collection pages with filters (fabric, work, color, size, price, occasion, form), product page (gallery, variants, size chart, **fit advice**, delivery estimate, reviews, related, WhatsApp/Messenger order button), search with suggestions, cart, **guest checkout** (phone-first, COD default, advance-payment option), order confirmation page, **order tracking by phone + order number**, customer account (orders, addresses, wishlist), CMS pages/FAQ/policies/size guide/blog, menus, SEO (meta, OpenGraph, JSON-LD Product, sitemap with encoded Bangla slugs, per-shop `llms.txt`), 301 redirects on slug changes, Core Web Vitals–friendly.
9. **`landing_pages`** — **campaign landing-page builder** (a must for Facebook sellers): choose product(s)/bundle/offer, block editor (hero, gallery/video, price & offer, countdown, features, size chart, reviews/testimonials, FAQ, delivery info, sticky order button, WhatsApp/call button), **one-page order form** (name, phone, address, quantity/variant, delivery zone → live total), 6+ ready templates, duplicate/clone, Bangla slug, per-page Pixel/CAPI events, **incomplete-order capture** (lead saved when phone is typed), AI copywriting in Bangla via ai-catalog/ai-hub, source/UTM tracking, conversion stats. Works without `storefront`.
10. **`order_confirmation`** — the **Pending Orders desk**: every new unconfirmed order lands here; assignment to call agents, call log and notes, one-tap Confirm / Hold / Cancel with reason, SLA timers and auto-reminders, auto-cancel-after-N-days setting, **fake-order & duplicate protection** (same phone/address/IP bursts, blacklist, high-value COD risk flag, optional OTP confirmation), customer history/return-rate badge, quick edit (change size/color/address), WhatsApp/SMS confirmation templates, agent performance report.
11. **`courier`** — adapter interface + drivers for **Pathao, Steadfast, RedX, eCourier, Paperfly** (verify each provider's current API docs; implement behind one `CourierDriver` contract with `fake` sandbox driver) plus **manual courier**; book single/bulk, print labels, track via polling/webhooks, status mapping to order statuses, **COD collection reconciliation**, courier charge reconciliation, return-to-origin handling, optional customer success-rate lookup where the provider exposes it, per-courier performance report, auto-suggest the cheapest/best courier by zone.
12. **`payments`** — payment methods per shop; bKash/Nagad/Rocket/Upay **manual verification queue** (customer submits TrxID + sender number; staff verifies; later auto-match hook), gateway adapters (SSLCommerz, AamarPay, ShurjoPay) behind one interface with sandbox/fake; partial/advance payments (e.g. delivery-charge advance for COD), refunds, payment ledger, webhook handling with signature verification, idempotency.
13. **`returns`** — return/exchange/refund requests (storefront self-service, POS, call desk), RMA, reasons (size, color, fabric, defect, wrong item), photo evidence, QC and **restock/write-off**, refund to cash/MFS/store credit, exchange with price difference, return-rate by product/size/fabric to feed ai-catalog fit advice.
14. **`wholesale`** — B2B: wholesale price tiers and MOQ, **buyer accounts with approval**, wholesale catalog/portal login, quotation → order, credit limits and due ledger, set-wise and carton-wise ordering, reseller/dropship price lists and commission, downloadable price-list PDF (Bangla).
15. **`production`** — made-to-order and in-house stitching: measurement capture (with profiles), BOM per design (e.g. kameez 2.5 m, salwar 2.25 m, orna 2.5 m, lining, buttons), fabric issue from stock, **cutting orders**, job cards (PDF), assignment to tailors/vendors, stages (cut → stitch → finish → QC → ready), **piece-rate wages** feeding payroll, alteration jobs, due-date calendar, rework/defect tracking, delivery to the customer order. Requires `inventory`.
16. **`crm`** — loyalty points and tiers, store credit/wallet, **customer due ledger (বাকি)**, segments (VIP, dormant, high-return, by district/occasion), birthday/anniversary/Eid reminders, feedback requests, notes and follow-ups.
17. **`marketing`** — coupons and discount rules, flash sales, bundles, SMS/WhatsApp-link/email campaigns to segments, **abandoned-cart & incomplete-order recovery**, Facebook Pixel + Conversions API, TikTok Pixel, GA4/GTM, product feeds (Google/Facebook via ai-catalog), **AI social video scripts** (TikTok/Reels/Shorts from ai-catalog), referral codes, campaign ROI.
18. **`accounts`** — chart of accounts, cash/bank/MFS accounts, **day book**, expenses with categories and attachments, income, receivables/payables, double-entry journals auto-posted from sales, purchases, returns, payroll and courier COD, **profit & loss, balance sheet, cash flow, trial balance**, VAT summary, period lock, daily closing report (PDF), multi-branch view.
19. **`hr`** — employees, departments, shifts/rosters, **attendance via attendance-hub** (devices, ADMS, webhook bridge, GPS/QR virtual check-in for field staff), leave, holidays, salary structures, advances/loans, commissions, **piece-rate wages** from `production`, payroll runs, payslip PDF (Bangla), attendance and payroll reports.
20. **`reports`** — sales (by day/channel/product/category/fabric/size/color/district/agent), gross margin, stock valuation and aging, best/slow sellers, return rates, courier success/COD pending, cash and MFS reconciliation, customer lifetime value, staff performance; export CSV/Excel/PDF; scheduled email/Telegram summary to the owner.
21. **`insights`** — the `/insights` dashboard via `laravel-admin-panel` (see D8): widget grid, Kanban, Calendar, NLQ, health, audit.
22. **`ai`** — AI hub for owners: **chat-to-order** (paste a Messenger/WhatsApp conversation → structured order draft with customer, phone, address, items), AI product creation (links to ai-catalog), Bangla/English caption and ad-copy generator, **size recommendation** from measurements/height/weight + return history, reply suggestions for customer messages, simple demand/reorder suggestions from sales history, price-suggestion with margin guardrails, plain-language "explain this report". All calls are tagged per shop with a visible monthly budget.
23. **`integrations`** — REST API with tokens and scopes, webhooks (HMAC-signed, retry log), CSV/Excel importers, Zapier-style outgoing events, OpenAPI docs, API usage log. Backups and data export (PII-safe).

**Stretch (only after everything above is green):** `rental` (bridal/party wear rental with booking calendar, deposit, return inspection), multi-currency, marketplace sync.

---

## H. PUBLIC PAGES (marketing site that shows every feature)

Build a fast, server-rendered public site for the product itself (separate from any shop's storefront). All feature content is **generated from the module registry** (`featureList` of each `ModuleDefinition`), so the public pages, README and in-app module cards never drift apart.

| URL | Content |
|---|---|
| `/` | Hero ("সব ধরনের থ্রি-পিস ব্যবসার জন্য সম্পূর্ণ সমাধান"), who it is for, module grid, 3-step "how it works", demo CTA, social proof placeholders clearly labelled as sample, FAQ teaser |
| `/features` | Full feature index grouped by theme (Sell, Stock, Fulfil, Money, People, Grow, AI) |
| `/features/{module}` | One page per module: what it does, screenshots, workflows, who needs it, dependencies |
| `/solutions/{preset}` | One page per business type (online seller, boutique, wholesaler/importer, manufacturer/tailor, chain) with the recommended module set |
| `/modules` | Interactive "build your stack": toggle modules → shows dependencies and which plan fits |
| `/pricing` | Plans mapped to module bundles and limits |
| `/demo` | Pick a demo business and a role → one-click sign-in (see Section I); lists all demo credentials |
| `/how-it-works`, `/compare` | Workflow diagrams; comparison with spreadsheets/Facebook-inbox-only selling |
| `/docs` and `/docs/{page}` | Renders the README/docs sections |
| `/blog` (optional), `/faq`, `/contact`, `/changelog`, `/privacy`, `/terms` | Static/CMS pages |
| `/sitemap.xml`, `/robots.txt`, `/llms.txt` | SEO and AI-agent discovery |

Requirements: `bn`/`en` switcher, canonical + OpenGraph + JSON-LD (`SoftwareApplication`, `FAQPage`), no layout shift, images lazy-loaded, mobile-first, Lighthouse ≥ 90 on mobile for `/` and `/features`.

**Screenshots:** add a Playwright (or Pest Browser) script `php artisan docs:screenshots` that logs into the demo and captures each module's main screen into `public/img/features/`, used by the public pages and README.

**README parity test:** a Pest test must fail if any module key is missing from `README.md`, from `/features/{module}`, or from the sitemap.

---

## I. DEMO BUSINESS & DEMO USERS

Create `DemoSeeder` (extended in every phase). It refuses to run when `APP_ENV=production` unless `--force-demo` is passed. `DEMO_MODE=true` enables the banner, role switcher, fake drivers and nightly reset.

### Demo shops (each with a different preset, realistic data)
1. **নূর বুটিক / Noor Boutique** — `boutique_retail` + online: 60 products (unstitched, ready-made, custom-stitch), 2 locations, 400+ orders over 90 days across POS and web, returns, a cashier shift history, 12 employees with 30 days of simulated attendance, payroll, expenses, supplier POs.
2. **রংধনু অনলাইন / Rongdhonu Online** — `online_seller`: 40 products, 6 landing pages with orders, a busy pending-order desk with fake/duplicate flags, fake-courier bookings (all five drivers) and COD reconciliation, abandoned carts, campaigns.
3. **ঢাকা ফ্যাব্রিকস হোলসেল / Dhaka Fabrics Wholesale** — `wholesale_importer`: imported consignments with landed cost, batches, wholesale tiers, buyer accounts, credit ledgers, B2B orders, supplier ledger.
4. **সুন্দরী টেইলার্স / Sundori Tailors** — `manufacturer_tailor`: measurement profiles, BOMs, cutting orders, job cards, tailor piece-rate wages, alteration jobs.
Plus a platform with 6+ sample shops/plans for the super-admin view.

**Data realism:** Bangla customer names and addresses across many districts, valid-format BD phone numbers (fictional, not real people), realistic prices in BDT, three-piece product names (e.g. "জামদানি কটন থ্রি-পিস", "লন প্রিন্ট আনস্টিচড") with fabric/work/occasion attributes. **Images:** generate placeholder swatches/patterns programmatically (GD/Intervention) — no scraped or stock photos.

### Demo users (shown on `/demo` and in README; demo-only, passwords not changeable in demo mode)
| Role | Email | Password |
|---|---|---|
| Platform Super Admin | `superadmin@demo.threepiece.test` | `password` |
| Owner (each shop) | `owner@<shop>.demo.test` | `password` |
| Manager | `manager@<shop>.demo.test` | `password` |
| Cashier | `cashier@<shop>.demo.test` | `password` |
| Call/Sales Agent | `agent@<shop>.demo.test` | `password` |
| Inventory Keeper | `stock@<shop>.demo.test` | `password` |
| Packer | `packer@<shop>.demo.test` | `password` |
| Accountant | `accounts@<shop>.demo.test` | `password` |
| HR | `hr@<shop>.demo.test` | `password` |
| Tailor/Production Lead | `tailor@<shop>.demo.test` | `password` |
| Wholesale Buyer | `buyer@<shop>.demo.test` | `password` |
| Retail Customer | `customer@<shop>.demo.test` | `password` |

Demo behaviour: **role switcher** in the top bar (instantly sign in as another demo role), a dismissible guided checklist ("Make a POS sale", "Confirm a pending order", "Book a courier", "Create a landing page", "Generate a product with AI", "Run payroll"), nightly `demo:reset` that restores seed data, throttling on AI/SMS-like actions, and all outbound drivers set to `fake`.

---

## J. CROSS-CUTTING REQUIREMENTS

### J1. Security
- Authorization via policies on every model, module-aware; no IDOR (tenant isolation tests, Section E1).
- Rate limiting + honeypot (+ optional Turnstile) on public order/landing/checkout endpoints; idempotency keys on order creation; CSRF everywhere; strict validation; safe file uploads (type/size/content checks, store outside web root, signed URLs for private files).
- Secrets only from `.env`; encrypt per-shop third-party keys at rest; webhook signature checks (couriers, payments, attendance bridge `X-Bridge-Token`).
- Audit log for money, stock, permission and settings changes (admin-panel's tamper-evident chain for the critical set).
- Biometric/attendance data stays encrypted (attendance-hub does this at rest — keep `APP_KEY` handling documented).
- Optional 2FA for owners; session/device management.

### J2. Performance (use drutogoti + normal discipline)
- Indexes on every foreign key and common filter; eager loading everywhere; pagination; no unbounded queries.
- Cache: Redis; response/fragment caching for storefront and public pages with tag-based invalidation on product/price/stock change.
- Queues for PDFs, AI, SMS, courier calls, feeds, image conversion; `--queue` for attendance sync.
- Budgets: storefront home/category/product **p95 server time < 300 ms** on demo data, **≤ 25 queries** per page, mobile Lighthouse ≥ 90; POS add-to-cart interaction < 150 ms.
- Image pipeline: WebP/AVIF thumbnails, `srcset`, lazy loading, explicit dimensions.

### J3. Testing and quality gates
- **Pest** unit + feature + Livewire tests per module; tenant-isolation suite; module on/off suite (every module toggled off ⇒ nav/routes/public blocks gone, data intact; toggled on ⇒ restored); money/rounding tests; BD phone and address tests; Bangla PDF tests; package integration tests with `AIHub::fake()`, `AttendanceHub::fake()`, `UnicodePdf::fake()`.
- **Smoke tour test:** log in as each demo role and open every navigation item of every enabled module (expects 200, no exceptions, no N+1 beyond limit).
- Larastan level 6+, Pint clean, `composer audit` clean (or documented), PHPStan baseline only for third-party noise.

### J4. CI (GitHub Actions)
Pipeline: install → Pint → Larastan → Pest (with MySQL + Redis services) → `composer audit` → **Semgrep** (PHP/Laravel ruleset) → front-end build → artifact. Nightly job runs the full smoke tour and the performance budget test. Dependabot/Renovate config included.

### J5. Deployment docs (`docs/DEPLOY-*.md`)
- **FastPanel/nginx on a VPS** (PHP 8.3-FPM, extensions: `intl gd mbstring sockets curl openssl bcmath redis`), Redis, MySQL, Supervisor for `queue:work` (and Horizon if used), cron for `schedule:run`, nginx `location /iclock/` block for ADMS push (`client_max_body_size 10M`), custom-domain setup for shops, SSL, `.env.production` template, `storage:link`, font install (`unicode-pdf:font:install`), `config:cache`/`route:cache`/`view:cache`, zero-downtime deploy script, backups (DB + media) with restore test, log rotation.
- Docker Compose for local development (app, mysql, redis, mailpit, queue, scheduler).
- Health endpoint `/up`, uptime/alert hooks, Drutogoti weekly digest.

### J6. Documentation
- `README.md` (root): product overview, screenshots, **feature matrix per module** (generated by `php artisan docs:features` from the registry and committed), business presets, demo links and credentials, quick start, architecture diagram, package-by-package usage, configuration reference, testing, security notes, deployment pointers, roadmap, license.
- `docs/`: `ARCHITECTURE.md`, `MODULES.md`, `PACKAGE-NOTES.md`, `DECISIONS.md`, `KNOWN-GAPS.md`, `DEPLOY-FASTPANEL.md`, `API.md`, `DEMO.md`, `CONTRIBUTING.md`, `CHANGELOG.md`, `ACCEPTANCE.md`.

---

## K. BUILD PHASES (do them in this order)

| Phase | Scope | Exit criteria |
|---|---|---|
| **0 — Foundation & package spike** | Laravel 12 + Filament 3, Docker dev env, CI skeleton, install and publish all 7 packages, `docs/PACKAGE-NOTES.md`, resolve D8 | All package smoke commands pass; CI green; decisions logged |
| **1 — Core platform** | Multi-shop tenancy + isolation tests, auth, roles/permissions, **Module system** + modules screen + presets, shop settings/branding, i18n (bn/en), BD phone/address/money foundations, media library, audit log, platform panel (shops, plans), demo shell (banner, role switcher) | Toggle any placeholder module on/off with tests; shop A/B isolation proven |
| **2 — Catalog** | Three-piece domain model, variants, size charts, barcode labels, slugs (per-shop, history, 301), AI creation via ai-catalog (photo/keyword/URL/voice/datasheet/bulk), review workflow, category matching, import/export | Create a product from a photo in demo (fake AI) and publish |
| **3 — Inventory & purchasing** | Locations, ledger, adjustments, transfers, stocktake, batches, suppliers, PO, GRN, landed cost | Stock ledger reconciles to on-hand in tests |
| **4 — Orders, desk, customers, payments, courier** | Unified orders, quick order, status workflow, **pending-order desk with fraud flags**, customers/addresses, payment methods + MFS verification queue, courier adapters + fakes + COD reconciliation, Bangla invoice/challan PDFs | Place → confirm → book courier → deliver → reconcile COD works end to end |
| **5 — POS & returns** | POS screen, shifts, split payments, receipts (80/58 mm), returns/exchanges/refunds, restock/QC | A full cashier day with Z-report in an automated test |
| **6 — Storefront & landing pages** | Public shop, checkout, tracking, customer account, CMS, SEO/sitemap/`llms.txt`, landing-page builder + templates + one-page order + incomplete-order capture | Mobile Lighthouse ≥ 90; landing order appears in pending desk |
| **7 — CRM, marketing, notifications** | Loyalty, wallet, due ledger, segments, coupons, flash sales, campaigns, abandoned-cart recovery, pixels/CAPI, feeds, SMS/WhatsApp/email adapters (+fakes), AI social scripts | Campaign sends through fake driver and is logged |
| **8 — Accounts, HR, production, wholesale** | Journals auto-posting, P&L/BS/cash flow, day book; attendance-hub integration, payroll, payslips; production jobs, BOM, piece-rate; wholesale portal, tiers, credit ledgers | P&L ties out to sales/expenses in tests; payroll run includes attendance + piece-rate |
| **9 — Insights, reports, AI assistant** | `/insights` via admin-panel (widgets, Kanban, Calendar, NLQ, health, audit), report suite with exports and scheduled summaries, chat-to-order, size recommendation, AI budgets per shop | Owner sees a live executive dashboard; chat-to-order creates a draft order |
| **10 — Public site, demo data, docs** | Marketing site (Section H) generated from registry, `/demo` role launcher, full DemoSeeder + `demo:reset`, screenshots, README feature matrix, parity test | All H/I requirements met; README parity test green |
| **11 — Hardening** | Drutogoti tuning and query budgets, security review (Semgrep, `composer audit`), backups/restore drill, deploy docs verified on a clean VM or container, accessibility pass, final QA against Section L | Section L fully passes |

---

## L. ACCEPTANCE SCENARIOS (all must pass; script them in `docs/ACCEPTANCE.md` and a Pest "golden path" suite)

1. **Online seller day:** Facebook order arrives via landing page → appears in Pending Desk with risk badge → agent calls, edits size, confirms → courier booked (fake Pathao) → delivered → COD reconciled → accounts show revenue and courier cost.
2. **Counter day:** cashier opens shift → sells a ready-made set with split bKash + cash → customer exchanges size next day → closes shift → Z-report and drawer difference correct → stock correct.
3. **Imported stock:** wholesaler receives an imported consignment with freight/customs → landed cost spread across variants → B2B order with tier price and credit limit → supplier and buyer ledgers correct.
4. **Custom stitch:** customer measurements saved → made-to-order sold → job card printed → cut, stitched, QC'd by tailor → piece-rate wage appears in payroll → delivered.
5. **AI catalog:** photo + keyword + supplier URL each produce a reviewed draft in Bangla and English with matched category, JSON-LD and fit advice; approval creates a live product with a Bangla/English slug and storefront page; renaming it keeps the old URL via 301.
6. **Modules:** a shop switches off `pos`, `hr` and `production` → they vanish from nav, routes, public blocks and schedules; data remains; switching back restores everything; dependency warnings work.
7. **Attendance:** `attendance:simulate` data (and one ADMS push request in a feature test) flows into attendance days → late/overtime → payroll → Bangla payslip PDF.
8. **Tenant isolation:** automated attack suite (IDs, search, exports, PDFs, queues, cache) finds no cross-shop leakage.
9. **Public site:** `/`, `/features`, every `/features/{module}`, `/solutions/*`, `/pricing`, `/demo` load fast, list every module, and match README; sitemap/`llms.txt` valid.
10. **Fresh install:** on an empty machine, README quick-start yields a running app with demo data in under 15 minutes.

### Definition of Done
- Every module in Section G is implemented, toggleable, tested, documented and visible on the public site and in the README.
- All seven packages are genuinely used for their jobs (D1–D6) and the Insights dashboard works (D8).
- Demo shops and demo users work as in Section I with no real external calls.
- CI is green (Pint, Larastan, Pest, `composer audit`, Semgrep); performance and isolation suites pass.
- `docs/DECISIONS.md` and `docs/KNOWN-GAPS.md` are honest and current.

---

## M. FIRST ACTIONS

1. Create the project and run the Composer setup from Section C.
2. Do **Phase 0** exactly as D7 describes and commit `docs/PACKAGE-NOTES.md` before building features.
3. Create `docs/DECISIONS.md`, `docs/ACCEPTANCE.md` and the task list for Phase 1.
4. Proceed phase by phase to Phase 11, committing at the end of each, and finish with a short final report: what was built, what is in `KNOWN-GAPS.md`, and how to run the demo.
