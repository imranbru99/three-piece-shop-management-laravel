# API and integration scope

This release uses session-backed web routes. It does not yet expose a production REST API, personal access tokens, API scopes, OpenAPI specification, HMAC webhooks or third-party credentials.

## Working web mutations

- `POST /contact`: validated contact/plan-interest record, CSRF, honeypot and 5/minute limit.
- `POST /demo/{shop}`: non-production demo role launcher, 20/minute limit and session rotation.
- `POST /s/{shop}/product/{slug}`: guest sample order with server totals, idempotency, CSRF and honeypot, 10/minute.
- `POST /s/{shop}/track`: phone + order-number lookup, 10/minute.
- `POST /app/{shop}/catalog/create`: owner/manager product creation.
- `POST /app/{shop}/inventory/{id}`: scoped stock adjustment with a reason.
- `POST /app/{shop}/orders/{id}/status`: authorized order transition.
- `POST /app/{shop}/payments/{id}`: scoped delivered-order collection, idempotency and overpayment validation.
- `POST /app/{shop}/pos/sale`: atomic split-payment demo sale, 30/minute.
- `POST /app/{shop}/modules/{key}`: nondestructive dependency-checked module toggle.
- `GET /app/{shop}/reports/export/csv`: role/module/tenant-scoped streaming CSV.

Package studio and attendance API routes remain behind explicit deny middleware until their tenant-aware production authorization is completed. Do not remove that protection to enable a public demo.
