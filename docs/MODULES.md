# Module system

`app/Modules/catalog.json` contains 23 module definitions, each with bilingual names/descriptions, feature scope, dependencies, locked state and implementation status. `ModuleRegistry` also defines five starter presets. The public feature index, detail pages, sitemap, module planner and README feature matrix derive from this source.

Run `php artisan docs:features` after registry edits. CI should run `php artisan docs:features --check` to detect stale documentation.

## Working module controls

Each `Shop` stores its enabled keys in JSON. Core, catalog, orders and customers are always on. The owner/manager workspace checks dependencies when disabling a module and automatically includes dependencies when enabling one. A change does not delete business rows. Disabled working routes return 404 and disappear from workspace navigation. The public planner is a separate browser-local tool and does not mutate a shop.

## Exact business subset

Implemented subsets cover core, catalog, orders, customers, inventory, POS, storefront, order confirmation, payments, accounts and reports. The live `/status` page derives exact scope from `PublicContent::implemented()` and `remaining()`. Remaining modules have complete public descriptions but no advertised business screens.

## Remaining architecture

The full requested per-module service providers, settings schemas, route/block/schedule contributions, module catalog and pivot tables, scheduler gating and plan entitlements are not implemented. The simpler JSON-backed registry is a working demo foundation, not a claim that the full modular architecture is finished.
