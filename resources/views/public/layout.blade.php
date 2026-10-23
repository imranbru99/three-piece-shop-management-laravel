<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title ?? 'Three-Piece Shop' }} — Three-Piece Shop</title>
@if(request()->routeIs('store.receipt','store.track'))<meta name="robots" content="noindex,nofollow">@endif
<meta name="description" content="{{ $description ?? $t('A thoughtful business workspace for Bangladeshi three-piece sellers. Explore the public demo, product catalog, orders and modular business tools.', 'বাংলাদেশের থ্রি-পিস ব্যবসার জন্য একটি সহজ ব্যবস্থা। ক্যাটালগ, অর্ডার ও মডিউলার টুল দেখুন।') }}">
<link rel="canonical" href="{{ url()->current() }}"><meta name="theme-color" content="#254c3e">
<meta property="og:title" content="{{ $title ?? 'Three-Piece Shop' }}"><meta property="og:description" content="{{ $description ?? 'Made for the way you do business.' }}"><meta property="og:type" content="website"><meta property="og:url" content="{{ url()->current() }}"><meta property="og:image" content="{{ asset('img/github/homepage.png') }}"><meta name="twitter:card" content="summary_large_image">
<link rel="icon" type="image/svg+xml" href="{{ asset('img/brand-mark.svg') }}">
@vite(['resources/css/app.css', 'resources/js/app.js'])
<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'SoftwareApplication','name'=>'Three-Piece Shop','applicationCategory'=>'BusinessApplication','operatingSystem'=>'Web','inLanguage'=>['en','bn'],'url'=>url('/')], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@stack('head')
</head>
<body>
<a href="#main" class="skip-link">{{ $t('Skip to content','মূল অংশে যান') }}</a>
<div class="announcement"><span class="dot"></span>{{ $t('Thoughtfully made for Bangladesh. Built around your business.', 'বাংলাদেশের জন্য তৈরি। আপনার ব্যবসাকে ঘিরে।') }} <a href="{{ route('how') }}">{{ $t('Meet your new workspace','পরিচিত হোন') }} <span aria-hidden="true">↗</span></a></div>
<header class="site-header"><div class="container header-inner"><a class="brand" href="{{ route('home') }}"><img src="{{ asset('img/brand-mark.svg') }}" alt="" width="39" height="39"><span>three-piece<span class="brand-shop">SHOP & BUSINESS</span></span></a>
<button class="mobile-toggle" aria-label="{{ $t('Open navigation','মেনু খুলুন') }}" aria-expanded="false" aria-controls="navigation" data-menu><x-shop-icon name="menu"/></button>
<nav id="navigation" class="main-nav" aria-label="{{ $t('Main navigation','মূল মেনু') }}">
<a @class(['active'=>request()->is('features*')]) href="{{ route('features') }}">{{ $t('Features','ফিচার') }}</a><a @class(['active'=>request()->is('solutions*')]) href="{{ route('solutions') }}">{{ $t('For your business','আপনার ব্যবসার জন্য') }}</a><a @class(['active'=>request()->is('modules')]) href="{{ route('modules') }}">{{ $t('Build your stack','মডিউল বাছাই') }}</a><a @class(['active'=>request()->is('pricing')]) href="{{ route('pricing') }}">{{ $t('Plans','প্ল্যান') }}</a><a href="{{ route('docs') }}">{{ $t('Resources','রিসোর্স') }}</a>
<div class="nav-actions"><a class="language" href="{{ request()->fullUrlWithQuery(['lang'=>app()->getLocale()==='en'?'bn':'en']) }}" lang="{{ app()->getLocale()==='en'?'bn':'en' }}">{{ app()->getLocale()==='en'?'বাংলা':'English' }}</a><a class="btn btn-small" href="{{ route('demo') }}">{{ $t('Explore demo','ডেমো দেখুন') }} <x-shop-icon name="arrow"/></a></div>
</nav></div></header>
<main id="main">@if(session('success'))<div role="status" class="container notice">{{ session('success') }}</div>@endif
@if($errors->any())<div role="alert" class="container notice error"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')</main>
<section class="closing-cta"><div class="container cta-inner"><div><span class="eyebrow light">{{ $t('YOUR BUSINESS. YOUR WAY.','আপনার ব্যবসা। আপনার মতো।') }}</span><h2>{{ $t('Make room for what’s next.', 'পরের ধাপের জন্য প্রস্তুত হোন।') }}</h2><p>{{ $t('Start with a real demo. Explore at your own pace.','বাস্তব ডেমো দিয়ে শুরু করুন। নিজের মতো করে দেখুন।') }}</p></div><a class="btn btn-cream" href="{{ route('demo') }}">{{ $t('Step inside the demo','ডেমোতে প্রবেশ করুন') }} <x-shop-icon name="arrow"/></a></div></section>
<footer><div class="container footer-grid"><div><a class="brand" href="{{ route('home') }}"><img src="{{ asset('img/brand-mark.svg') }}" alt="" width="36" height="36"><span>three-piece<span class="brand-shop">SHOP & BUSINESS</span></span></a><p>{{ $t('For the shops, the makers, and the people building something of their own.','দোকান, প্রস্তুতকারক ও নিজের ব্যবসা গড়া মানুষদের জন্য।') }}</p><span class="made-in">{{ $t('Made with care, for Bangladesh.','যত্নে তৈরি, বাংলাদেশের জন্য।') }}</span></div>
<div><h3>{{ $t('Product','পণ্য') }}</h3>@foreach(['features'=>'All features','modules'=>'Module builder','pricing'=>'Plans','demo'=>'Live demo','changelog'=>'Changelog'] as $route=>$label)<a href="{{ route($route) }}">{{ $t($label, ['features'=>'সব ফিচার','modules'=>'মডিউল বাছাই','pricing'=>'প্ল্যান','demo'=>'ডেমো','changelog'=>'পরিবর্তন'][$route]) }}</a>@endforeach</div>
<div><h3>{{ $t('Explore','দেখুন') }}</h3><a href="{{ route('solutions') }}">{{ $t('Business types','ব্যবসার ধরন') }}</a><a href="{{ route('how') }}">{{ $t('How it works','যেভাবে কাজ করে') }}</a><a href="{{ route('compare') }}">{{ $t('Compare workflows','তুলনা করুন') }}</a><a href="{{ route('blog') }}">{{ $t('Shop journal','ব্যবসার কথা') }}</a><a href="{{ route('faq') }}">{{ $t('Questions & answers','প্রশ্ন ও উত্তর') }}</a></div>
<div><h3>{{ $t('Let’s connect','যোগাযোগ') }}</h3><a href="{{ route('contact') }}">{{ $t('Contact us','যোগাযোগ করুন') }}</a><a href="{{ route('docs') }}">{{ $t('Documentation','ডকুমেন্টেশন') }}</a><a href="{{ route('privacy') }}">{{ $t('Privacy','গোপনীয়তা') }}</a><a href="{{ route('terms') }}">{{ $t('Terms','শর্তাবলি') }}</a><a href="{{ route('status') }}">{{ $t('Build status','বাস্তবায়ন অবস্থা') }}</a></div></div>
<div class="container footer-bottom"><span>© {{ date('Y') }} Three-Piece Shop</span><span>{{ $t('BDT · Asia/Dhaka · বাংলা / English','টাকা · ঢাকা · বাংলা / English') }}</span></div></footer>
</body></html>