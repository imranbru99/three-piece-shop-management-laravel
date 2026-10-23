# Architecture

Public pages use Blade, a shared layout, reusable SVG icon component, Tailwind/Vite CSS and small JavaScript enhancements. `ModuleRegistry` loads a single JSON catalog for public feature pages, in-app module controls and generated README content. Public content distinguishes roadmap scope from implemented workflows.

The working workspace is a Blade demo; Filament is installed but production panel providers/resources have not yet been implemented. It uses the Laravel `web` guard and seeded users with one `shop_id` and one restricted role.

## Tenant boundary

Every business-owned model uses `BelongsToShop`. No authenticated tenant means a `shop_id=0` scope rather than unscoped results. Workspace controllers resolve the requested shop and call `BusinessAccess::check` before reading or mutating scoped data. Public queries deliberately remove the authenticated tenant scope and immediately constrain the requested shop. Order relations retain the global scope.

## Sales transaction

`SalesService::create` locks the shop, checks an idempotency key, normalizes a BD phone, obtains matching active products for that shop, computes totals from server prices and persists order lines. Storefront orders are pending and COD; POS orders complete as delivered with an exact cash/bKash split. Invalid payments roll back the entire sale.

Confirmation locks products, deducts committed quantities and writes stock movements. Cancellation restores only an actual prior commitment, even if inventory settings changed meanwhile. Delivery snapshots revenue and cost in balanced subledger lines. Payments credit receivables and debit the selected cash/MFS account with a unique event key.

## Boundaries still to implement

One user can belong to only one shop. No queue tenant serialization, scoped cache, backup/restore, production login/onboarding, full policies, tamper-evident audit or production integrations are implemented. The sales subledger is not a full general ledger. See KNOWN-GAPS.md.
