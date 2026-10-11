<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f8f5">
    <link rel="icon" type="image/jpeg" href="{{ asset('images/philrice-emblem-small.jpg') }}">
    <title>Fertilizer Subsidy | Support for every growing season</title>
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .landing { min-height:100vh; overflow:hidden; background:#f6f9f6; color:#192b21; }
        .landing-nav { position:relative; z-index:2; display:flex; align-items:center; justify-content:space-between; width:min(1180px,calc(100% - 48px)); height:86px; margin:auto; }
        .landing-logo { display:inline-flex; align-items:center; color:#174a34; font:800 1.03rem 'Manrope',sans-serif; text-decoration:none; }
        .landing-logo:hover { color:#23734b; }
        .landing-logo-text { display:grid; line-height:1.04; }
        .landing-logo-text small { margin-top:5px; color:#829187; font:600 .62rem 'DM Sans',sans-serif; letter-spacing:.13em; text-transform:uppercase; }
        .landing-nav-actions { display:flex; align-items:center; gap:12px; }
        .landing-link { padding:10px 14px; color:#506456; font-size:.88rem; font-weight:700; text-decoration:none; }
        .landing-link:hover { color:#23734b; }
        .landing-button { display:inline-flex; align-items:center; justify-content:center; gap:9px; min-height:44px; padding:0 18px; border:1px solid #23734b; border-radius:11px; background:#23734b; color:#fff; font-size:.87rem; font-weight:700; text-decoration:none; box-shadow:0 7px 16px #23734b22; transition:.18s ease; }
        .landing-button:hover { transform:translateY(-2px); background:#1b5d3c; color:#fff; box-shadow:0 10px 22px #23734b30; }
        .landing-button.secondary { border-color:#dce7dd; background:#fff; color:#27583c; box-shadow:none; }
        .landing-button.secondary:hover { border-color:#b9d9c2; background:#f8fcf8; }
        .landing-hero { position:relative; display:grid; grid-template-columns:1.02fr .98fr; align-items:center; gap:48px; width:min(1180px,calc(100% - 48px)); min-height:570px; margin:18px auto 0; padding:45px 0 68px; }
        .landing-hero::before { position:absolute; z-index:0; top:0; right:-17vw; width:72vw; height:100%; border-radius:48% 0 0 48%; background:radial-gradient(ellipse at 65% 42%,#e6f2e5,#eaf4eb 55%,#f0f6f0 75%); content:''; }
        .hero-copy { position:relative; z-index:1; max-width:570px; }
        .hero-kicker { display:inline-flex; align-items:center; gap:9px; margin-bottom:21px; padding:8px 12px; border:1px solid #d7e8d8; border-radius:999px; background:#fff9; color:#37714a; font-size:.72rem; font-weight:800; letter-spacing:.055em; }
        .hero-kicker i { font-size:.9rem; }
        .hero-title { max-width:620px; margin:0; color:#173e2c; font-size:clamp(2.7rem,5.4vw,4.65rem); font-weight:800; line-height:1.04; letter-spacing:-.065em; }
        .hero-title span { color:#398052; }
        .hero-description { max-width:490px; margin:21px 0 0; color:#66776b; font-size:1.03rem; line-height:1.75; }
        .hero-actions { display:flex; flex-wrap:wrap; gap:11px; margin-top:29px; }
        .hero-note { display:flex; align-items:center; gap:9px; margin-top:25px; color:#748579; font-size:.76rem; font-weight:600; }
        .hero-note i { color:#4e9160; font-size:.95rem; }
        .hero-visual { position:relative; z-index:1; min-width:0; }
        .farm-art { position:relative; width:min(100%,510px); aspect-ratio:1.08; margin:0 auto; }
        .farm-glow { position:absolute; inset:7% 6% 4%; border:1px solid #ffffffa8; border-radius:46% 54% 49% 51%; background:linear-gradient(145deg,#d6e9d6,#eef5e8 68%,#dae9d7); box-shadow:inset 0 1px 0 #fff,0 28px 60px #1c57301a; }
        .farm-sun { position:absolute; top:13%; right:20%; width:64px; height:64px; border-radius:50%; background:#f2d58d; box-shadow:0 0 0 13px #f2d58d28,0 0 55px #f2d58d70; }
        .farm-hill { position:absolute; right:0; bottom:0; left:0; overflow:hidden; height:61%; border-radius:48% 48% 45% 45%; }
        .farm-hill.back { bottom:9%; background:#9bbc83; clip-path:ellipse(70% 50% at 51% 100%); }
        .farm-hill.mid { bottom:-1%; height:53%; background:#65975f; clip-path:ellipse(76% 55% at 51% 100%); }
        .farm-hill.front { bottom:-10%; height:43%; background:#36744d; clip-path:ellipse(80% 60% at 52% 100%); }
        .field-lines { position:absolute; right:-8%; bottom:-8%; left:-8%; height:51%; border:2px solid #d4e4a8a8; border-bottom:0; border-radius:50% 50% 0 0; transform:rotate(-8deg); }
        .field-lines::before,.field-lines::after { position:absolute; right:8%; bottom:-4%; left:8%; height:86%; border:2px solid #d4e4a870; border-bottom:0; border-radius:50% 50% 0 0; content:''; }
        .field-lines::after { right:17%; left:17%; height:68%; }
        .farm-sprout { position:absolute; right:28%; bottom:29%; z-index:1; width:100px; height:155px; color:#f5f5cc; filter:drop-shadow(0 5px 7px #174a342e); }
        .farm-sprout svg { width:100%; height:100%; }
        .floating-card { position:absolute; z-index:2; display:flex; align-items:center; gap:12px; padding:13px 16px; border:1px solid #ffffffd9; border-radius:16px; background:#ffffffed; box-shadow:0 13px 34px #174a341a; backdrop-filter:blur(8px); }
        .floating-card.balance { top:18%; left:0; }
        .floating-card.support { right:-1%; bottom:19%; }
        .floating-icon { display:grid; place-items:center; width:39px; height:39px; border-radius:12px; background:#eaf5ed; color:#28734b; font-size:1.1rem; }
        .floating-icon.gold { background:#fbf2d9; color:#a67b24; }
        .floating-copy small,.floating-copy strong { display:block; }
        .floating-copy small { color:#859187; font-size:.67rem; font-weight:600; }
        .floating-copy strong { margin-top:3px; color:#203c2b; font:700 .84rem 'Manrope',sans-serif; }
        .landing-benefits { width:min(1180px,calc(100% - 48px)); margin:0 auto; padding:26px 0 68px; }
        .benefit-heading { margin:0 0 20px; color:#284936; font:700 .86rem 'Manrope',sans-serif; }
        .benefit-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; }
        .benefit { display:flex; align-items:flex-start; gap:13px; padding:19px; border:1px solid #e5ece6; border-radius:15px; background:#fff; box-shadow:0 5px 16px #1e402b08; }
        .benefit-icon { display:grid; flex:0 0 39px; place-items:center; width:39px; height:39px; border-radius:12px; background:#eef6ef; color:#337d4c; font-size:1.03rem; }
        .benefit strong { display:block; color:#294534; font-size:.82rem; }
        .benefit p { margin:5px 0 0; color:#79877d; font-size:.75rem; line-height:1.55; }
        .landing-footer { display:flex; justify-content:space-between; width:min(1180px,calc(100% - 48px)); margin:auto; padding:19px 0 25px; border-top:1px solid #e3ebe4; color:#87948a; font-size:.72rem; }
        @media(max-width:850px) { .landing-hero { grid-template-columns:1fr  .88fr; gap:16px; min-height:515px; } .hero-title { font-size:clamp(2.5rem,6vw,3.6rem); } .farm-art { transform:scale(.92); } }
        @media(max-width:650px) { .landing-nav,.landing-hero,.landing-benefits,.landing-footer { width:calc(100% - 36px); } .landing-nav { height:72px; } .landing-nav-actions { gap:3px; } .landing-link { padding:9px; } .landing-nav .landing-button { min-height:39px; padding:0 12px; font-size:.79rem; } .landing-hero { display:flex; flex-direction:column; align-items:stretch; gap:18px; margin-top:8px; padding:34px 0 30px; } .landing-hero::before { top:42%; right:-35vw; width:130vw; height:58%; } .hero-title { font-size:clamp(2.75rem,13vw,4rem); } .hero-description { font-size:.94rem; } .hero-visual { width:100%; max-width:430px; margin:0 auto; } .farm-art { transform:none; } .floating-card.balance { left:-2%; } .floating-card.support { right:-2%; } .landing-benefits { padding:18px 0 45px; } .benefit-grid { grid-template-columns:1fr; } .benefit { padding:16px; } .landing-footer { gap:12px; flex-direction:column; } }
        @media(prefers-reduced-motion:reduce) { *,*::before,*::after { scroll-behavior:auto!important; transition:none!important; animation:none!important; } }
    </style>
</head>
<body class="landing">
    @php($user = auth()->user())
    @php($homeRoute = $user ? ($user->role === 'farmer' ? route('farmer.dashboard') : route('merchant.dashboard')) : route('login'))
    <header class="landing-nav">
        <a class="landing-logo" href="{{ url('/') }}" aria-label="Fertilizer Subsidy home">
            <img class="philrice-brand-logo landing-logo-mark me-2" src="{{ asset('images/philrice-emblem-small.jpg') }}" alt="PhilRice logo">
            <span class="landing-logo-text">Fertilizer Subsidy<small>Support for local growers</small></span>
        </a>
        <nav class="landing-nav-actions" aria-label="Main navigation">
            @if ($user)
                <a class="landing-link" href="{{ $homeRoute }}">Go to your account</a>
            @else
                <a class="landing-link" href="{{ route('login') }}">Log in</a>
                @if (Route::has('register'))
                    <a class="landing-button" href="{{ route('register') }}">Create account <i class="bi bi-arrow-up-right"></i></a>
                @endif
            @endif
        </nav>
    </header>

    <main>
        <section class="landing-hero">
            <div class="hero-copy">
                <div class="hero-kicker"><i class="bi bi-sun"></i> A simpler way to access fertilizer support</div>
                <h1 class="hero-title">Helping every farm <span>grow stronger.</span></h1>
                <p class="hero-description">A shared space for farmers and fertilizer merchants to manage subsidy information, products, and orders with more confidence.</p>
                <div class="hero-actions">
                    @if ($user)
                        <a class="landing-button" href="{{ $homeRoute }}">Open your workspace <i class="bi bi-arrow-right"></i></a>
                    @else
                        <a class="landing-button" href="{{ route('login') }}">Get started <i class="bi bi-arrow-right"></i></a>
                        @if (Route::has('register'))
                            <a class="landing-button secondary" href="{{ route('register') }}">Create an account</a>
                        @endif
                    @endif
                </div>
                <div class="hero-note"><i class="bi bi-shield-check"></i> One simple place for subsidy and order information</div>
            </div>

            <div class="hero-visual" aria-hidden="true">
                <div class="farm-art">
                    <div class="farm-glow"></div>
                    <div class="farm-sun"></div>
                    <div class="farm-hill back"></div>
                    <div class="farm-hill mid"></div>
                    <div class="farm-hill front"><div class="field-lines"></div></div>
                    <div class="farm-sprout">
                        <svg viewBox="0 0 100 155" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M50 150V49" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
                            <path d="M49 91C23 91 9 77 9 52C34 52 49 66 49 91Z" fill="currentColor"/>
                            <path d="M51 69C51 43 66 28 91 28C91 53 77 69 51 69Z" fill="#fffde0"/>
                            <path d="M22 150H78" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="floating-card balance">
                        <span class="floating-icon"><i class="bi bi-wallet2"></i></span>
                        <span class="floating-copy"><small>Subsidy information</small><strong>Easy to keep track</strong></span>
                    </div>
                    <div class="floating-card support">
                        <span class="floating-icon gold"><i class="bi bi-bag-check"></i></span>
                        <span class="floating-copy"><small>Fertilizer orders</small><strong>Clear and organized</strong></span>
                    </div>
                </div>
            </div>
        </section>

        <section class="landing-benefits" aria-labelledby="benefit-heading">
            <h2 class="benefit-heading" id="benefit-heading">Made for the people who help farms thrive</h2>
            <div class="benefit-grid">
                <article class="benefit">
                    <span class="benefit-icon"><i class="bi bi-person-vcard"></i></span>
                    <div><strong>For farmers</strong><p>Check subsidy details and keep fertilizer orders in one place.</p></div>
                </article>
                <article class="benefit">
                    <span class="benefit-icon"><i class="bi bi-shop"></i></span>
                    <div><strong>For merchants</strong><p>Manage products and prepare orders for verified beneficiaries.</p></div>
                </article>
                <article class="benefit">
                    <span class="benefit-icon"><i class="bi bi-arrow-left-right"></i></span>
                    <div><strong>Better coordination</strong><p>Keep essential subsidy and order information easier to follow.</p></div>
                </article>
            </div>
        </section>
    </main>

    <footer class="landing-footer">
        <span>Fertilizer Subsidy</span>
        <span>Supporting local growers, one season at a time.</span>
    </footer>
</body>
</html>
