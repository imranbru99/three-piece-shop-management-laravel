<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Shop;
use App\Modules\ModuleRegistry;
use App\Support\Language;
use App\Support\PublicContent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PublicController extends Controller
{
    public function home(): View
    {
        return view('public.home', ['title' => Language::text('Made for your kind of business', 'আপনার ব্যবসার জন্য')]);
    }

    public function features(): View
    {
        return view('public.features', ['title' => Language::text('All features', 'সব ফিচার')]);
    }

    public function feature(string $key, ModuleRegistry $registry): View
    {
        $module = $registry->get($key);

        return view('public.feature', ['module' => $module, 'title' => $module['name'][app()->getLocale()], 'description' => $module['description'][app()->getLocale()]]);
    }

    public function solutions(): View
    {
        return view('public.solutions', ['title' => Language::text('For your business', 'আপনার ব্যবসার জন্য')]);
    }

    public function solution(string $key, ModuleRegistry $registry): View
    {
        $preset = $registry->presets()[$key] ?? abort(404);

        return view('public.solutions', ['title' => $preset['name'][app()->getLocale()], 'preset' => $preset, 'presetKey' => $key]);
    }

    public function modules(): View
    {
        return view('public.modules', ['title' => Language::text('Build your stack', 'মডিউল বাছুন')]);
    }

    public function pricing(): View
    {
        return view('public.pricing', ['title' => Language::text('Plans for your next chapter', 'ব্যবসার প্ল্যান')]);
    }

    public function demo(): View
    {
        $shops = config('demo.enabled') && ! app()->isProduction() ? Shop::query()->where('is_demo', true)->get() : collect();

        return view('public.demo', ['title' => Language::text('Explore the demo', 'ডেমো দেখুন'), 'shops' => $shops]);
    }

    public function content(string $page): View
    {
        $t = Language::text(...);
        $headings = [
            'how' => [$t('A CLEARER WAY TO WORK', 'কাজের সহজ উপায়'), $t('From the first hello.', 'প্রথম পরিচয় থেকে।'), $t('To the next happy customer.', 'পরের সন্তুষ্ট ক্রেতা পর্যন্ত।'), $t('An everyday workflow, with the scattered pieces brought together.', 'ছড়িয়ে থাকা কাজগুলো নিয়ে একটি নিয়মিত ব্যবস্থা।')],
            'compare' => [$t('A BETTER DAILY RHYTHM', 'প্রতিদিনের সহজ কাজ'), $t('Fewer places to look.', 'কম জায়গায় খুঁজুন।'), $t('More room to focus.', 'কাজে মন দিন।'), $t('See how the working demo compares with a manual spreadsheet and inbox workflow.', 'ম্যানুয়াল শিট ও ইনবক্সের সাথে ডেমোর কাজ তুলনা করুন।')],
            'faq' => [$t('QUESTIONS, ANSWERED', 'প্রশ্ন ও উত্তর'), $t('A little clarity.', 'একটু পরিষ্কার ধারণা।'), $t('Before you begin.', 'শুরু করার আগে।'), $t('Practical answers about the product, the demo, and where the build stands.', 'পণ্য, ডেমো ও বাস্তবায়নের অবস্থা নিয়ে উত্তর।')],
            'contact' => [$t('LET’S TALK SHOP', 'ব্যবসার কথা বলি'), $t('Your next chapter', 'আপনার পরের অধ্যায়'), $t('starts with a conversation.', 'একটি আলোচনা দিয়ে শুরু।'), $t('Share what you are building, and what would make your working day easier.', 'আপনার ব্যবসা ও কাজ সহজ করার প্রয়োজন জানান।')],
            'blog' => [$t('THE SHOP JOURNAL', 'ব্যবসার কথা'), $t('Notes for the', 'যারা নিজের ব্যবসা'), $t('business you’re building.', 'গড়ছেন তাঁদের জন্য।'), $t('Short, practical guides for a more organized shop.', 'সাজানো দোকানের জন্য ছোট ও কার্যকর লেখা।')],
            'docs' => [$t('THE WORKING HANDBOOK', 'কাজের নির্দেশিকা'), $t('A little guidance.', 'কিছু নির্দেশনা।'), $t('Every step of the way.', 'প্রতিটি ধাপে।'), $t('Installation, architecture, package findings and an honest implementation log.', 'ইনস্টল, গঠন, প্যাকেজের তথ্য ও বাস্তবায়নের সঠিক বিবরণ।')],
            'status' => [$t('AN HONEST BUILD LOG', 'বাস্তবায়নের সঠিক তথ্য'), $t('What works today.', 'এখন যা কাজ করে।'), $t('What comes next.', 'এরপর যা বাকি।'), $t('A transparent view of the demo subset and the larger product scope.', 'ডেমোর কাজ ও সম্পূর্ণ পণ্যের পরিধি দেখুন।')],
            'privacy' => [$t('YOUR INFORMATION', 'আপনার তথ্য'), $t('A clear approach', 'পরিষ্কার নীতি'), $t('to privacy.', 'গোপনীয়তার জন্য।'), $t('Project privacy notice · updated 23 October 2026.', 'প্রকল্পের গোপনীয়তা নীতি · ২৩ অক্টোবর ২০২৬।')],
            'terms' => [$t('A SHARED UNDERSTANDING', 'একটি বোঝাপড়া'), $t('A few ground rules.', 'কিছু সাধারণ নিয়ম।'), $t('For a better experience.', 'ভালো অভিজ্ঞতার জন্য।'), $t('Demo and project terms · updated 23 October 2026.', 'ডেমো ও প্রকল্পের শর্ত · ২৩ অক্টোবর ২০২৬।')],
            'changelog' => [$t('THE BUILD JOURNAL', 'পরিবর্তনের খাতা'), $t('Small steps.', 'ছোট ধাপ।'), $t('A clearer product.', 'আরও পরিষ্কার পণ্য।'), $t('A record of shipped work, not a promise of future delivery dates.', 'তৈরি হওয়া কাজের তালিকা, ভবিষ্যতের সময়ের প্রতিশ্রুতি নয়।')],
        ];
        [$eyebrow,$heading,$subheading,$intro] = $headings[$page] ?? abort(404);
        $data = compact('page', 'eyebrow', 'heading', 'subheading', 'intro');
        $data['title'] = $heading;
        $data['documents'] = self::documents();
        $data['articles'] = PublicContent::articles();
        $data['implemented'] = PublicContent::implemented();
        $data['remaining'] = PublicContent::remaining();
        $data['steps'] = [[$t('Organize your collection', 'কালেকশন সাজান'), $t('Give each design a SKU, fabric, form, color, size and price. Set opening stock with a traceable movement.', 'প্রতিটি ডিজাইনে SKU, কাপড়, ধরন, রং, মাপ ও দাম দিন। খতিয়ানসহ শুরুর মজুদ রাখুন।')], [$t('Make the sale', 'বিক্রয় করুন'), $t('Take a storefront order or make a counter sale. Totals are calculated on the server and retries do not duplicate the sale.', 'অনলাইন অর্ডার নিন বা কাউন্টারে বিক্রয় করুন। সার্ভারে দাম গণনা হয় ও পুনরায় পাঠালে বিক্রয় দ্বিগুণ হয় না।')], [$t('Follow through', 'শেষ পর্যন্ত দেখুন'), $t('Confirm, pack, ship and deliver. Record payment, print a receipt and review the sales report.', 'নিশ্চিত, প্যাক, পাঠানো ও ডেলিভারি করুন। পেমেন্ট লিখুন, রসিদ নিন ও প্রতিবেদন দেখুন।')]];
        $data['rows'] = [[$t('Product information', 'পণ্যের তথ্য'), $t('Names and prices scattered across files', 'নাম ও দাম বিভিন্ন ফাইলে'), $t('One searchable catalog with SKUs and stock', 'SKU ও মজুদসহ এক ক্যাটালগ')], [$t('New orders', 'নতুন অর্ডার'), $t('Copy details from messages by hand', 'মেসেজ থেকে হাতে তথ্য লেখা'), $t('Storefront checkout saves a pending order', 'চেকআউট থেকে অপেক্ষমাণ অর্ডার')], [$t('Stock changes', 'মজুদের পরিবর্তন'), $t('Manual edits without a movement history', 'ইতিহাস ছাড়া হাতে পরিবর্তন'), $t('Recorded deductions and adjustments', 'খতিয়ানে কর্তন ও পরিবর্তন')], [$t('Counter payments', 'কাউন্টার পেমেন্ট'), $t('Cash and mobile receipts counted separately', 'নগদ ও মোবাইলের আলাদা হিসাব'), $t('Validated split with balanced journal entries', 'যাচাইকৃত ভাগ ও ভারসাম্যপূর্ণ হিসাব')], [$t('Sales review', 'বিক্রয় দেখা'), $t('Rebuild totals in a spreadsheet', 'শিটে আবার হিসাব'), $t('Live metrics and scoped CSV export', 'বর্তমান হিসাব ও CSV')]];
        $data['sections'] = match ($page) {
            'privacy' => [[$t('What is stored', 'যে তথ্য সংরক্ষণ হয়'), $t('Contact messages store your name, email, optional shop name, plan interest and message. Demo orders store fictional recipient, address, product and payment-record information. Sessions store language and sign-in state.', 'যোগাযোগে নাম, ইমেইল, দোকানের নাম, প্ল্যান ও মেসেজ সংরক্ষণ হয়। ডেমো অর্ডারে কাল্পনিক প্রাপক, ঠিকানা, পণ্য ও পেমেন্ট রেকর্ড থাকে। সেশনে ভাষা ও লগইন অবস্থা থাকে।')], [$t('A shared demo', 'সবার জন্য ডেমো'), $t('Demo data is visible to visitors who enter the same sample shop and may be reset. Do not enter real customer, payment, medical or identity data. Contact messages are not shown publicly.', 'একই নমুনা দোকানের দর্শকরা ডেমোর তথ্য দেখতে পারেন ও তথ্য রিসেট হতে পারে। বাস্তব ক্রেতা, পেমেন্ট বা পরিচয়ের তথ্য দেবেন না। যোগাযোগের মেসেজ প্রকাশ্যে দেখানো হয় না।')], [$t('Cookies and local storage', 'কুকি ও স্থানীয় সংরক্ষণ'), $t('Essential session and CSRF cookies support forms and demo sign-in. Your module-planner selection is saved locally in your browser. No advertising pixels or third-party analytics are installed by this build.', 'সেশন ও CSRF কুকি ফর্ম ও লগইনে ব্যবহার হয়। মডিউল বাছাই ব্রাউজারে থাকে। এই সংস্করণে বিজ্ঞাপন পিক্সেল বা বাইরের অ্যানালিটিক্স নেই।')], [$t('Retention and requests', 'সংরক্ষণ ও অনুরোধ'), $t('This project has no automated contact-message deletion policy yet. Use the contact form to request correction or deletion from the project operator. Before a production launch the operator must publish their identity, retention schedule and support contact.', 'যোগাযোগের মেসেজ স্বয়ংক্রিয় মুছে ফেলার নীতি এখনও নেই। সংশোধন বা মুছতে যোগাযোগ ফর্ম ব্যবহার করুন। প্রোডাকশনের আগে পরিচালকের পরিচয়, সংরক্ষণ সময় ও সহায়তার ঠিকানা প্রকাশ করতে হবে।')]],
            'terms' => [[$t('Development demo', 'উন্নয়নের ডেমো'), $t('The software is provided as a development preview. Only documented working workflows are implemented. It is not represented as a complete production business system.', 'সফটওয়্যারটি উন্নয়নের নমুনা। ডকুমেন্টে উল্লেখিত কাজগুলো বাস্তবায়িত। এটি সম্পূর্ণ প্রোডাকশন ব্যবসার ব্যবস্থা হিসেবে উপস্থাপিত নয়।')], [$t('Example plans', 'নমুনা প্ল্যান'), $t('Pricing is illustrative. Registering interest does not purchase a plan or create a paid subscription. No real payment is processed in this demo.', 'দাম নমুনা। আগ্রহ জানালে প্ল্যান কেনা বা সাবস্ক্রিপশন চালু হয় না। ডেমোতে বাস্তব পেমেন্ট হয় না।')], [$t('Responsible use', 'দায়িত্বশীল ব্যবহার'), $t('Use only fictional data in sample businesses. Do not upload secrets, attempt to access another tenant, abuse forms or rely on shared demo records as your business source of truth.', 'নমুনা দোকানে শুধু কাল্পনিক তথ্য দিন। গোপন তথ্য, অন্য দোকানে প্রবেশের চেষ্টা বা ফর্মের অপব্যবহার করবেন না। ডেমোর তথ্যকে নিজের ব্যবসার আসল হিসাব হিসেবে ব্যবহার করবেন না।')], [$t('Before production', 'প্রোডাকশনের আগে'), $t('Operators must complete the documented security, isolation, provider, backup, legal and deployment checks before using this code with real customer or financial data. The repository license governs source-code use.', 'বাস্তব ক্রেতা বা আর্থিক তথ্য ব্যবহারের আগে নিরাপত্তা, তথ্যের পৃথকীকরণ, সার্ভিস, ব্যাকআপ ও ডেপ্লয় পরীক্ষা সম্পন্ন করতে হবে। সোর্স কোডের ব্যবহার রিপোজিটরির লাইসেন্স অনুযায়ী।')]],
            'changelog' => [['23 October 2026 · Public experience', $t('Added the public marketing site, module registry, business presets, stack planner, documentation browser, contact persistence and original textile illustrations.', 'পাবলিক সাইট, মডিউল তালিকা, প্রিসেট, প্ল্যানার, ডকুমেন্ট, যোগাযোগ সংরক্ষণ ও নিজস্ব চিত্র যোগ হয়েছে।')], ['23 October 2026 · Working demo', $t('Added tenant-scoped sample shops, restricted demo roles, product catalog, inventory adjustments, storefront checkout, order states, counter sales, payments, receipts and core sales accounting. Remaining scope is listed on the build-status page.', 'আলাদা নমুনা দোকান, সীমিত ভূমিকা, পণ্য, মজুদ, অনলাইন অর্ডার, কাউন্টার বিক্রয়, পেমেন্ট, রসিদ ও প্রাথমিক হিসাব যোগ হয়েছে। বাকি কাজ অবস্থা পেজে আছে।')]],
            default => [],
        };

        return view('public.content', $data);
    }

    public function article(string $slug): View
    {
        $article = PublicContent::articles()[$slug] ?? abort(404);

        return view('public.content', ['page' => 'article', 'title' => $article['title'][app()->getLocale()], 'eyebrow' => Language::text('THE SHOP JOURNAL', 'ব্যবসার কথা'), 'heading' => $article['title'][app()->getLocale()], 'subheading' => '', 'intro' => $article['intro'][app()->getLocale()], 'article' => $article]);
    }

    /** @return array<string,string> */
    public static function documents(): array
    {
        return ['readme' => 'README', 'architecture' => 'Architecture', 'modules' => 'Module catalog', 'package-notes' => 'Package findings', 'decisions' => 'Decisions', 'known-gaps' => 'Known gaps', 'acceptance' => 'Acceptance scenarios', 'demo' => 'Demo guide', 'api' => 'API & integration scope', 'deploy-fastpanel' => 'Deployment guide', 'contributing' => 'Contributing', 'changelog' => 'Changelog'];
    }

    public function document(string $slug): View
    {
        $label = self::documents()[$slug] ?? abort(404);
        $path = $slug === 'readme' ? base_path('README.md') : base_path('docs/'.strtoupper($slug).'.md');
        abort_unless(is_file($path), 404);
        $source = (string) file_get_contents($path);
        // Resolve known repository-relative media and docs without accepting arbitrary file paths.
        $source = preg_replace('/!\[([^]]*)\]\(docs\/images\/([^()]+)\)/', '![$1]('.asset('img/github').'/'.'$2'.')', $source) ?? $source;
        $source = preg_replace_callback('/\[([^]]+)\]\((?:docs\/)?([A-Z-]+)\.md\)/', function ($m) {
            $key = strtolower($m[2]);

            return isset(self::documents()[$key]) ? '['.$m[1].']('.route('document', $key).')' : $m[1];
        }, $source) ?? $source;
        $markdown = Str::markdown($source, ['html_input' => 'strip', 'allow_unsafe_links' => false]);

        return view('public.content', ['page' => 'document', 'title' => $label, 'eyebrow' => Language::text('DOCUMENTATION', 'ডকুমেন্টেশন'), 'heading' => $label, 'subheading' => '', 'intro' => Language::text('Rendered from the repository source. Technical documentation is maintained in English.', 'রিপোজিটরির সোর্স থেকে দেখানো। কারিগরি ডকুমেন্ট ইংরেজিতে রাখা হয়েছে।'), 'markdown' => $markdown]);
    }

    public function contact(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'email' => 'required|email|max:190', 'shop_name' => 'nullable|string|max:100', 'plan' => ['required', Rule::in(['general', 'essential', 'growth', 'complete'])], 'message' => 'required|string|min:10|max:3000', 'website' => 'nullable|max:0']);
        unset($data['website']);
        ContactMessage::query()->create($data);

        return back()->with('success', Language::text('Your message has been saved for the project owner to review.', 'প্রকল্পের মালিকের পর্যালোচনার জন্য আপনার মেসেজ সংরক্ষিত হয়েছে।'));
    }

    public function sitemap(ModuleRegistry $registry): Response
    {
        $urls = ['/', '/features', '/solutions', '/modules', '/pricing', '/demo', '/how-it-works', '/compare', '/docs', '/faq', '/contact', '/changelog', '/privacy', '/terms', '/status', '/blog'];
        foreach (array_keys($registry->all()) as $key) {
            $urls[] = '/features/'.$key;
        }
        foreach (array_keys($registry->presets()) as $key) {
            $urls[] = '/solutions/'.$key;
        }
        foreach (array_keys(self::documents()) as $key) {
            $urls[] = '/docs/'.$key;
        }
        foreach (array_keys(PublicContent::articles()) as $key) {
            $urls[] = '/blog/'.$key;
        }
        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $url) {
            $xml .= '<url><loc>'.htmlspecialchars(url($url), ENT_XML1).'</loc></url>';
        }

        return response($xml.'</urlset>', 200, ['Content-Type' => 'application/xml']);
    }

    public function llms(ModuleRegistry $registry): Response
    {
        $text = "# Three-Piece Shop\n\nLaravel business software for Bangladeshi three-piece shops. Development demo; see /status for implemented scope.\n\n## Modules\n";
        foreach ($registry->all() as $module) {
            $text .= '- ['.$module['name']['en'].']('.route('feature',$module['key']).'): '.$module['description']['en'].' Status: '.$module['status']."\n";
        }

        return response($text,200,['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
