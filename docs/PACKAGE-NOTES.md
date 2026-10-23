# Package spike notes

Inspected installed README files, providers, routes, configs, commands and relevant source on 2026-10-23. This inventory describes the locked source, not all README claims.

## imrandevbd/drutogoti

- Version: `dev-main` / `6c5b4c94bf42e350d5b06c88736dcbf2b8e199f1`.
- PSR-4: `Drutogoti\` → `src/`.
- Publish tags: `drutogoti-config`, `drutogoti-migrations`.
- Facades: none.
- Migrations: `create_drutogoti_cache_events_table.php.stub`, `create_drutogoti_cookie_events_table.php.stub`, `create_drutogoti_image_scans_table.php.stub`, `create_drutogoti_query_logs_table.php.stub`, `create_drutogoti_requests_table.php.stub`, `create_drutogoti_suggestions_table.php.stub`.
- Filament plugins: none.
- Config `drutogoti.php`: .

## imrandevbd/laravel-admin-panel

- Version: `dev-main` / `67305195926b7e151f8cb061a1765d23645098a1`.
- PSR-4: `ImranDevBD\LaravelAdminPanel\` → `src/`.
- Publish tags: `admin-panel-config`, `admin-panel-assets`, `admin-panel-views`, `admin-panel-lang`.
- Facades: `src/Facades/AdminPanel.php`.
- Migrations: none.
- Filament plugins: none.
- Config `admin-panel.php`: .

## imrandevbd/laravel-ai-catalog

- Version: `dev-main` / `07b5783f3ec08ed61cad9c1ea3439ae9b4e15c25`.
- PSR-4: `ImranDevBd\AiCatalog\` → `src/`.
- Publish tags: `ai-catalog-config`, `ai-catalog-migrations`, `ai-catalog-views`.
- Facades: `src/Facades/AiCatalog.php`.
- Migrations: `2026_01_01_000001_create_ai_catalog_generations_table.php`, `2026_01_01_000002_create_ai_catalog_revisions_table.php`, `2026_01_01_000003_create_ai_catalog_bulk_jobs_table.php`, `2026_01_01_000004_create_ai_catalog_brand_voices_table.php`, `2026_01_01_000005_create_ai_catalog_image_hashes_table.php`.
- Filament plugins: `src/Filament/AiCatalogPlugin.php`.
- Config `ai-catalog.php`: .

## imrandevbd/laravel-ai-hub

- Version: `v1.6.0` / `5fc1bf2e13b663a3646af57b2416c1f214951c04`.
- PSR-4: `ImranDevBd\AiHub\` → `src/`.
- Publish tags: `ai-hub-config`, `ai-hub-views`, `ai-hub-migrations`.
- Facades: `src/Facades/AIHub.php`.
- Migrations: `2024_01_01_000001_create_ai_hub_request_logs_table.php`, `2024_01_01_000002_create_ai_hub_settings_table.php`.
- Filament plugins: `src/Filament/AiHubPlugin.php`.
- Config `ai-hub.php`: .

## imrandevbd/laravel-attendance-hub

- Version: `dev-main` / `7bd5a7b4a9b27687e9c060a17544fe359d6ce74c`.
- PSR-4: `ImranDevBd\AttendanceHub\` → `src/`.
- Publish tags: `attendance-hub-config`, `attendance-hub-migrations`.
- Facades: `src/Facades/AttendanceHub.php`.
- Migrations: `2026_01_01_000001_create_attendance_devices_table.php`, `2026_01_01_000002_create_attendance_logs_table.php`, `2026_01_01_000003_create_device_user_maps_table.php`, `2026_01_01_000004_create_device_biometric_templates_table.php`.
- Filament plugins: `src/Filament/AttendanceHubPlugin.php`.
- Config `attendance-hub.php`: .

## imrandevbd/laravel-unicode-pdf

- Version: `dev-main` / `bc0288d3422728a88d81280124fd193050929982`.
- PSR-4: `ImranDev\UnicodePdf\` → `src/`.
- Publish tags: `unicode-pdf-config`, `unicode-pdf-views`, `unicode-pdf-fonts`.
- Facades: `src/Facades/UnicodePdf.php`.
- Migrations: none.
- Filament plugins: none.
- Config `unicode-pdf.php`: .

## imrandevbd/laravel-universal-slug

- Version: `dev-main` / `9fdcf1659a514e94e7ac58b17ec783e73003fb7b`.
- PSR-4: `ImranDev\UniversalSlug\` → `src/`.
- Publish tags: `universal-slug-config`, `universal-slug-migrations`.
- Facades: `src/Facades/UniversalSlug.php`.
- Migrations: `create_slug_histories_table.php.stub`.
- Filament plugins: none.
- Config `universal-slug.php`: .

## Verified API and capability differences

- `AIHub::fake(string|array|Closure|AiResponse)` exists. Actual machine-readable flow: `AIHub::structured($schema)->prompt(...)->forJob(...)->send()`. Studio authorization supports `AIHub::auth(Closure)`, but explicit blocking middleware also protects the foundation routes.
- `AiCatalogPlugin` and `Filament\Actions\GenerateWithAiAction` exist. `schema.map.translations`, `taxonomy.model`, `tenancy`, `workflow.require_review`, `quality.region`, `returns_reduction`, and `social_commerce` appear in the installed config. Tenant safety still needs a separate audit before exposing any package routes.
- Attendance uses `tenant_id` internally, not `shop_id`. Its simulator creates `EMP-101` string employee IDs and assumes Saturday/Sunday weekends; future demo integration must map these explicitly and configure Bangladesh schedules.
- Unicode PDF has a bundled `native` engine and bundled Noto fonts. Its font installer can return success after a failed download, so verify actual font files and PDF bytes. Native Bengali PDF generation is tested; visual conjunct accuracy is not yet verified.
- Slugs use `ImranDev\UniversalSlug`, `Traits\HasUniversalSlug`, `Traits\HasSlugHistory`, and `SlugOptions`. Config publish tags use `universal-slug-*`.
- Admin Panel has `Panel\Panel`, `PanelRegistry`, shared `web` guard configuration, navigation, theme, dashboard and asset/theme routes. The source has only `admin-panel:install` and `admin-panel:publish`. No doctor, audit chain, health, NLQ, Calendar or Kanban implementation exists. The `widgets()` method stores class names but the installed dashboard does not render a widget grid. D8's fallback cannot provide health/audit/NLQ from this source.
- Drutogoti's provider registers only QueryWatcher. Cache/cookie/image settings and dashboard settings exist, but no HTTP route registration appears in its provider. Do not advertise these as implemented package capabilities.

## Smoke results (host PHP 8.5.4, SQLite)

| Check | Result |
| --- | --- |
| Composer install and all seven package discovery | Pass |
| Published configs, migrations, views/assets/fonts | Pass |
| Package migrations | Pass |
| `attendance:simulate --employees=12 --days=30` | Pass; 480 punches in initial run (randomized) |
| `unicode-pdf:diagnose` | Pass; initially warned about GD and missing font-cache directory |
| `unicode-pdf:font:install --font=bengali` and `--font=universal` | Pass; bundled files already present |
| `slug:generate 'জামদানি কটন থ্রি-পিস'` | Pass: জামদানি-কটন-থ্রি-পিস |
| `ai-hub:configure --show` | Pass; no provider keys configured |
| `admin-panel:doctor` | Blocked: absent upstream command |

Phase 0 is **not complete**. See KNOWN-GAPS.md. Installed vendor files are unchanged.
