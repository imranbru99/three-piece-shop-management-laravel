# Acceptance status

All product acceptance scenarios are pending. None are claimed to pass. The following are requirements, not execution results.

 (all must pass; script them in `docs/ACCEPTANCE.md` and a Pest "golden path" suite)

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


Execute and automate these in Pest after their implementation phases. Phase 0 package checks are recorded in PACKAGE-NOTES.md.

## Implemented-subset regression suite — 2026-10-23

`PublicSiteTest` covers bilingual marketing routes, every registry module/preset, discovery and validated contact persistence. `BusinessFlowTest` covers checkout → confirmation → delivery → collection; split-payment POS and rollback; stock restoration; tenant isolation for reads, writes, receipts and exports; role/module denial; private receipt/tracking; phone normalization and navigation smoke tests. These do not claim the original full golden paths pass (courier, exchange, imports, tailoring, payroll and AI publication remain absent).
