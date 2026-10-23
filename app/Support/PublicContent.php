<?php

namespace App\Support;

class PublicContent
{
    /** @return list<array<string,array<string,string>>> */
    public static function faqs(): array
    {
        $rows = [
            ['Is this only for shops with a physical counter?', 'এটি কি শুধু সরাসরি দোকানের জন্য?', 'No. The product includes presets for online sellers, boutiques, wholesalers, tailors and multi-branch businesses. Explore the preset pages to see the planned module combinations.', 'না। অনলাইন বিক্রেতা, বুটিক, পাইকার, দর্জি ও একাধিক শাখার জন্য প্রিসেট আছে। প্রস্তাবিত মডিউলগুলো প্রিসেট পেজে দেখুন।'],
            ['Can I use only the modules I need?', 'শুধু প্রয়োজনীয় মডিউল ব্যবহার করা যাবে?', 'Yes. The module planner helps you choose a stack. In the demo workspace, owners can toggle optional modules. Dependencies are checked and existing data is kept.', 'হ্যাঁ। প্ল্যানারে মডিউল বাছুন। ডেমোতে মালিক ঐচ্ছিক মডিউল চালু বা বন্ধ করতে পারেন। নির্ভরতা যাচাই হয় ও পুরনো তথ্য থাকে।'],
            ['Does it support Bangla and taka?', 'বাংলা ও টাকা সমর্থন করে?', 'The public site supports Bangla and English. The demo uses BDT, Bangladesh mobile-number validation and Asia/Dhaka dates. Prices are stored as integer poisha to avoid floating-point rounding.', 'পাবলিক সাইট বাংলা ও ইংরেজিতে। ডেমোতে টাকা, বাংলাদেশি মোবাইল নম্বর যাচাই ও ঢাকার সময় আছে। দশমিক ভুল এড়াতে দাম পূর্ণ পয়সায় সংরক্ষণ হয়।'],
            ['Is the full business system ready?', 'সম্পূর্ণ ব্যবসার ব্যবস্থা কি প্রস্তুত?', 'The public site and focused demo workflows work today. Advanced modules are still on the roadmap. Every module detail page and the build-status page distinguishes implemented work from remaining scope.', 'পাবলিক সাইট ও নির্দিষ্ট ডেমো কাজ এখন চলে। উন্নত মডিউল এখনও পরিকল্পনায়। প্রতিটি মডিউল ও অবস্থা পেজে বাস্তবায়িত ও বাকি কাজ আলাদা করা আছে।'],
            ['Will the demo charge me or send messages?', 'ডেমো কি টাকা কাটবে বা মেসেজ পাঠাবে?', 'No. Demo payments are bookkeeping records, and no real courier, payment or messaging service is called. Enter fictional data only because the sandbox is shared.', 'না। ডেমোর পেমেন্ট শুধু হিসাবের রেকর্ড। বাস্তব কুরিয়ার, পেমেন্ট বা মেসেজ সার্ভিসে কল হয় না। ডেমো সবার জন্য উন্মুক্ত, তাই কাল্পনিক তথ্য দিন।'],
            ['Can I move data from a spreadsheet?', 'শিট থেকে তথ্য আনা যাবে?', 'The working demo exports sales as CSV. Bulk product imports and other spreadsheet integrations are part of the remaining product scope and are not advertised as working today.', 'ডেমোতে বিক্রয়ের CSV এক্সপোর্ট আছে। একসাথে পণ্য আমদানি ও অন্য শিট সংযোগ এখনও বাকি।'],
            ['Are these monthly prices live offers?', 'মাসিক দাম কি সরাসরি অফার?', 'No. Prices are illustrative planning examples. The contact form records interest without activating billing or a subscription.', 'না। দামগুলো পরিকল্পনার নমুনা। যোগাযোগ ফর্ম আগ্রহ সংরক্ষণ করে, কোনো বিল বা সাবস্ক্রিপশন চালু করে না।'],
            ['How can I install the project?', 'প্রকল্প কীভাবে ইনস্টল করব?', 'Follow the repository README. It includes the dependency setup, migrations, demo seeding, frontend build and quality commands. Docker configuration targets PHP 8.3, MySQL 8 and Redis.', 'রিপোজিটরির README অনুসরণ করুন। নির্ভরতা, মাইগ্রেশন, ডেমো তথ্য, ফ্রন্টএন্ড বিল্ড ও পরীক্ষার কমান্ড আছে। ডকারে PHP 8.3, MySQL 8 ও Redis লক্ষ্য করা হয়েছে।'],
        ];

        return array_map(fn ($r) => ['q' => ['en' => $r[0], 'bn' => $r[1]], 'a' => ['en' => $r[2], 'bn' => $r[3]]], $rows);
    }

    /** @return array<string,array<string,mixed>> */
    public static function articles(): array
    {
        return [
            'organize-your-three-piece-catalog' => ['title' => ['en' => 'A calmer way to organize your collection', 'bn' => 'কালেকশন সাজানোর সহজ উপায়'], 'intro' => ['en' => 'Give every fabric, size and color a useful place.', 'bn' => 'প্রতিটি কাপড়, মাপ ও রঙের তথ্য সাজিয়ে রাখুন।'], 'sections' => [
                ['heading' => ['en' => 'Start with a design, then its variants', 'bn' => 'ডিজাইন দিয়ে শুরু, তারপর ভ্যারিয়েন্ট'], 'body' => ['en' => 'Keep the design name consistent. Give every combination of color and size a distinct SKU. An unstitched set needs length information; a ready-made set needs its actual size.', 'bn' => 'ডিজাইনের নাম একই রাখুন। রং ও মাপের প্রতিটি মিশ্রণের আলাদা SKU দিন। আনস্টিচড সেটে দৈর্ঘ্য, রেডিমেড সেটে সঠিক মাপ রাখুন।']],
                ['heading' => ['en' => 'Write for the person wearing it', 'bn' => 'যিনি পরবেন, তাঁর জন্য লিখুন'], 'body' => ['en' => 'Describe fabric feel, opacity, lining and care honestly. A useful description reduces the questions that arrive in your inbox and helps customers choose with confidence.', 'bn' => 'কাপড়ের অনুভূতি, স্বচ্ছতা, আস্তরণ ও যত্নের নিয়ম সঠিকভাবে লিখুন। কার্যকর বিবরণ ইনবক্সের প্রশ্ন কমায় ও ক্রেতাকে বাছতে সাহায্য করে।']],
                ['heading' => ['en' => 'Give stock a single home', 'bn' => 'মজুদের একক হিসাব রাখুন'], 'body' => ['en' => 'Record opening quantities, then add a reason to each adjustment. A sale and a manual stock correction should leave different, traceable entries in your movement ledger.', 'bn' => 'শুরুর পরিমাণ লিখুন, তারপর প্রতিটি পরিবর্তনে কারণ দিন। বিক্রয় ও ম্যানুয়াল সংশোধনের আলাদা রেকর্ড থাকা দরকার।']],
            ]],
            'from-inbox-to-order' => ['title' => ['en' => 'From inbox conversation to a clear order', 'bn' => 'ইনবক্সের কথাবার্তা থেকে পরিষ্কার অর্ডার'], 'intro' => ['en' => 'A repeatable routine for a busy social seller.', 'bn' => 'ব্যস্ত অনলাইন বিক্রেতার জন্য নিয়মিত কাজের ধাপ।'], 'sections' => [
                ['heading' => ['en' => 'Capture the essentials once', 'bn' => 'প্রয়োজনীয় তথ্য একবার নিন'], 'body' => ['en' => 'Confirm the product, size, quantity, recipient phone, full address and district. Read the total including delivery back to the customer before confirming the order.', 'bn' => 'পণ্য, মাপ, পরিমাণ, ফোন, পূর্ণ ঠিকানা ও জেলা নিশ্চিত করুন। ডেলিভারি সহ মোট দাম ক্রেতাকে জানান।']],
                ['heading' => ['en' => 'Keep a clear state', 'bn' => 'অবস্থা পরিষ্কার রাখুন'], 'body' => ['en' => 'A pending order is not a delivered sale. Use explicit stages for confirmation, packing, shipping and delivery. Reserve stock when an order is confirmed so two people do not sell the same last piece.', 'bn' => 'অপেক্ষমাণ অর্ডার মানেই বিক্রয় নয়। নিশ্চিত, প্যাকিং, পাঠানো ও ডেলিভারির আলাদা ধাপ রাখুন। নিশ্চিত করার সময় মজুদ সংরক্ষণ করুন।']],
                ['heading' => ['en' => 'Reconcile what arrived', 'bn' => 'প্রাপ্ত টাকার হিসাব মিলান'], 'body' => ['en' => 'Separate the delivery outcome from the payment outcome. Record the amount and method received, then compare outstanding balances with the underlying orders.', 'bn' => 'ডেলিভারি ও পেমেন্টের অবস্থা আলাদা রাখুন। প্রাপ্ত টাকা ও মাধ্যম লিখে বাকি অর্ডারের সাথে মিলান।']],
            ]],
            'know-your-daily-numbers' => ['title' => ['en' => 'The daily numbers worth keeping close', 'bn' => 'প্রতিদিন যে হিসাবগুলো জানা দরকার'], 'intro' => ['en' => 'Sales, cash and stock tell different parts of the story.', 'bn' => 'বিক্রয়, নগদ ও মজুদ ব্যবসার আলাদা তথ্য দেয়।'], 'sections' => [
                ['heading' => ['en' => 'Revenue and receipts are different', 'bn' => 'বিক্রয় ও টাকা পাওয়া আলাদা'], 'body' => ['en' => 'Delivered goods can create revenue before all money is received. Keep receivables separate from cash and mobile-money receipts so the same sale is not counted twice.', 'bn' => 'সব টাকা পাওয়ার আগেও ডেলিভারি থেকে বিক্রয় হতে পারে। পাওনা ও প্রাপ্ত নগদ আলাদা রাখুন, যাতে একই বিক্রয় দুবার না গণনা হয়।']],
                ['heading' => ['en' => 'Margin needs a cost basis', 'bn' => 'লাভ জানতে ক্রয়মূল্য দরকার'], 'body' => ['en' => 'Record a cost alongside each selling price. The demo snapshots that cost on each order line, so later changes to the catalog do not rewrite historical gross margin.', 'bn' => 'বিক্রয়মূল্যের সাথে ক্রয়মূল্য রাখুন। ডেমো প্রতিটি অর্ডারে সেই সময়ের ক্রয়মূল্য রাখে, তাই পরে দাম বদলালেও পুরনো মোট লাভ বদলায় না।']],
                ['heading' => ['en' => 'Review before you restock', 'bn' => 'পুনরায় কেনার আগে হিসাব দেখুন'], 'body' => ['en' => 'Look at on-hand quantity, recent demand and pending orders together. A low quantity is a useful signal, but it is not a complete purchasing decision.', 'bn' => 'মজুদ, সাম্প্রতিক চাহিদা ও অপেক্ষমাণ অর্ডার একসাথে দেখুন। কম মজুদ একটি সংকেত, সম্পূর্ণ ক্রয়ের সিদ্ধান্ত নয়।']],
            ]],
        ];
    }

    /** @return array<string,string> */
    public static function implemented(): array
    {
        return [
            'core' => 'Four demo shops, six restricted roles, tenant-scoped records, session sign-in, locale preference, module controls.',
            'catalog' => 'Product list/search/create; form, fabric, color, size, SKU, price/cost, original product illustrations.',
            'orders' => 'Storefront and POS orders; validated transitions, event records, printable/PDF receipts, server-side totals and idempotency.',
            'customers' => 'Normalized Bangladesh phone deduplication, addresses and searchable customer directory.',
            'inventory' => 'On-hand quantities, locked stock deductions, cancellation restore, adjustments with a movement ledger.',
            'pos' => 'Touch-friendly quantity selection, server-validated cash + bKash split, atomic sale, printable receipt.',
            'storefront' => 'Shop catalog/search, product details, one-product guest checkout, COD delivery charges, private receipt, phone + number tracking.',
            'order_confirmation' => 'Pending/hold filtering and authorized confirm/hold/cancel actions in the unified order list.',
            'payments' => 'Delivered-order cash/MFS records, references, partial collections, overpayment prevention and retry deduplication.',
            'accounts' => 'Balanced sale/payment postings, receivables and cash/MFS balances, cost snapshots and gross-margin summary.',
            'reports' => 'Live sales metrics, seven-day delivered-sales chart, scoped paginated journals and streaming sales CSV export.',
        ];
    }

    /** @return array<string,string> */
    public static function remaining(): array
    {
        return [
            'core' => 'Production registration, multi-shop memberships, Filament panels, full RBAC and tamper-evident auditing.',
            'catalog' => 'AI draft approval UI, brands/categories, variant matrix, product edits, slug history, import and barcode PDFs.',
            'orders' => 'Quick order editor, bulk actions, shipment splitting, full return/refund statuses and invoice numbering policies.',
            'customers' => 'Multiple addresses, measurement history, self-service accounts and imports.',
            'inventory' => 'Warehouses, transfers, stocktake, batches, meter/yard conversion and reservation expiry.',
            'pos' => 'Cashier shifts, hold/resume, offline sync, thermal profiles, discounts and returns/exchanges.',
            'storefront' => 'Multi-item carts, wishlist, reviews, customer account, custom domains and complete per-shop SEO feeds.',
            'order_confirmation' => 'Agent assignment, call history, risk engine, OTP and SLA reminders.',
            'payments' => 'Manual verification queue, provider adapters, signed webhooks, advances and refunds.',
            'accounts' => 'Expenses, supplier/payroll posting, opening inventory capital, complete BS/P&L/cash flow, VAT and period lock.',
            'reports' => 'Advanced dimensions, PDF/Excel, scheduled summaries, aging and courier/return analytics.',
        ];
    }
}
