# Demo guide

Set `DEMO_MODE=true` in a non-production environment. Run migrations, `php artisan demo:seed`, and the frontend build. Open `/demo` and choose a business and role. The seeder refuses production; existing shop slugs are skipped to avoid resetting changes.

Four sample businesses contain 156 illustrated product variants and 640 orders. Noor Boutique has 60 products and 400 orders. Names, addresses, phone-like strings and all payment records are fictional; no real external providers are called. The shared sandbox is not suitable for real customer data.

Roles are owner, manager, cashier, agent, stock and accounts. Emails use `{role}@{shop-slug}.demo.test`; seeded password is `password`. Current login uses the role launcher. This is deliberately unavailable in production.

## Try a counter sale

1. Enter Noor Boutique as cashier or owner.
2. Open Point of sale and set a product quantity to 1.
3. Read the total. Enter cash/bKash in poisha (100 poisha = ৳1), with an exact combined total.
4. Complete the sale and print the resulting receipt.
5. Owner/accountant can review sales and journal balances; stock keeper can inspect on-hand stock.

## Try an online order

1. Visit `/s/noor`, select a product and enter fictional recipient details.
2. Place the sample COD order and keep the order number.
3. Enter the owner workspace; filter Orders by pending, then confirm → pack → ship → deliver.
4. Enable the optional payments module if not included by the preset. As owner/accountant, record a collection against the delivered balance.
5. Track the order with its number and recipient phone. Public receipts are accessible only in the placing browser session.

No nightly reset, guided checklist, platform super-admin, HR/tailor/buyer/customer roles or all-module smoke tour exists yet. No demo action submits a real courier booking or moves money.
