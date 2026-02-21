<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etus Framework — Lightweight PHP Framework</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Mono:wght@400;500&family=Outfit:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --ink: #0d0d0d;
            --paper: #f5f0e8;
            --cream: #ede8dc;
            --accent: #c84b2f;
            --muted: #7a7268;
            --border: #d8d2c4;
            --mono-bg: #1a1814;
            --mono-fg: #e8e0d0;
            --mono-key: #c84b2f;
            --mono-str: #8fbe8f;
            --mono-cmt: #6b6358;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--paper);
            color: var(--ink);
            font-size: 16px;
            line-height: 1.6;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 100;
            opacity: 0.6;
        }

        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 3rem;
            border-bottom: 1px solid var(--border);
            background: rgba(245, 240, 232, 0.92);
            backdrop-filter: blur(12px);
        }

        .nav-logo {
            font-family: 'DM Serif Display', serif;
            font-size: 1.4rem;
            letter-spacing: -0.02em;
            color: var(--ink);
            text-decoration: none;
        }

        .nav-logo span {
            color: var(--accent);
        }

        .nav-links {
            display: flex;
            gap: 2.5rem;
            list-style: none;
        }

        .nav-links a {
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .nav-links a:hover {
            color: var(--ink);
        }

        .nav-badge {
            font-family: 'DM Mono', monospace;
            font-size: 0.75rem;
            background: var(--mono-bg);
            color: var(--mono-fg);
            padding: 0.3rem 0.75rem;
            border-radius: 2px;
        }

        .hero {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            padding-top: 5rem;
        }

        .hero-left {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 5rem 4rem 5rem 3rem;
            border-right: 1px solid var(--border);
        }

        .hero-eyebrow {
            font-family: 'DM Mono', monospace;
            font-size: 0.75rem;
            letter-spacing: 0.15em;
            color: var(--accent);
            text-transform: uppercase;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .hero-eyebrow::before {
            content: '';
            display: block;
            width: 2rem;
            height: 1px;
            background: var(--accent);
        }

        h1 {
            font-family: 'DM Serif Display', serif;
            font-size: clamp(3rem, 5vw, 5.5rem);
            line-height: 1.0;
            letter-spacing: -0.03em;
            color: var(--ink);
            margin-bottom: 1.5rem;
        }

        h1 em {
            font-style: italic;
            color: var(--accent);
        }

        .hero-sub {
            font-size: 1.1rem;
            font-weight: 300;
            color: var(--muted);
            max-width: 38ch;
            line-height: 1.7;
            margin-bottom: 3rem;
        }

        .hero-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            text-decoration: none;
            padding: 0.85rem 1.75rem;
            border: 1px solid;
            transition: all 0.2s;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--ink);
            color: var(--paper);
            border-color: var(--ink);
        }

        .btn-primary:hover {
            background: var(--accent);
            border-color: var(--accent);
        }

        .btn-secondary {
            background: transparent;
            color: var(--ink);
            border-color: var(--border);
        }

        .btn-secondary:hover {
            border-color: var(--ink);
        }

        .hero-right {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 5rem 3rem 5rem 4rem;
            background: var(--cream);
        }

        .code-window {
            background: var(--mono-bg);
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.2);
        }

        .code-titlebar {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.85rem 1rem;
            background: #141210;
            border-bottom: 1px solid #2a2620;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .dot-r {
            background: #ff5f56;
        }

        .dot-y {
            background: #ffbd2e;
        }

        .dot-g {
            background: #27c93f;
        }

        .code-filename {
            font-family: 'DM Mono', monospace;
            font-size: 0.7rem;
            color: var(--mono-cmt);
            margin-left: 0.5rem;
        }

        .code-body {
            padding: 1.5rem;
            font-family: 'DM Mono', monospace;
            font-size: 0.8rem;
            line-height: 1.8;
            color: var(--mono-fg);
            overflow-x: auto;
        }

        .k {
            color: var(--mono-key);
        }

        .s {
            color: var(--mono-str);
        }

        .c {
            color: var(--mono-cmt);
            font-style: italic;
        }

        .v {
            color: #8ecae6;
        }

        .f {
            color: #e9c46a;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        .stat {
            padding: 3rem;
            border-right: 1px solid var(--border);
            opacity: 0;
            transform: translateY(16px);
            transition: all 0.5s;
        }

        .stat:last-child {
            border-right: none;
        }

        .stat.visible {
            opacity: 1;
            transform: none;
        }

        .stat-number {
            font-family: 'DM Serif Display', serif;
            font-size: 3.5rem;
            color: var(--ink);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-number span {
            color: var(--accent);
        }

        .stat-label {
            font-size: 0.8rem;
            font-weight: 500;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .features {
            padding: 7rem 3rem;
        }

        .section-header {
            display: flex;
            align-items: baseline;
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .section-label {
            font-family: 'DM Mono', monospace;
            font-size: 0.7rem;
            letter-spacing: 0.15em;
            color: var(--muted);
            text-transform: uppercase;
            white-space: nowrap;
        }

        .section-rule {
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        h2 {
            font-family: 'DM Serif Display', serif;
            font-size: clamp(2rem, 3.5vw, 3rem);
            letter-spacing: -0.02em;
            line-height: 1.1;
            margin-bottom: 1rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0;
            border: 1px solid var(--border);
            margin-top: 3rem;
        }

        .feature {
            padding: 2.5rem;
            border-right: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            opacity: 0;
            transform: translateY(12px);
            transition: all 0.4s;
        }

        .feature:nth-child(3n) {
            border-right: none;
        }

        .feature:nth-last-child(-n+3) {
            border-bottom: none;
        }

        .feature.visible {
            opacity: 1;
            transform: none;
        }

        .feature:hover {
            background: var(--cream);
        }

        .feature-icon {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            display: block;
        }

        .feature-title {
            font-family: 'DM Serif Display', serif;
            font-size: 1.2rem;
            margin-bottom: 0.75rem;
        }

        .feature-desc {
            font-size: 0.9rem;
            color: var(--muted);
            line-height: 1.7;
        }

        .architecture {
            padding: 7rem 3rem;
            background: var(--cream);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        .arch-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5rem;
            align-items: start;
        }

        .arch-tree {
            font-family: 'DM Mono', monospace;
            font-size: 0.78rem;
            line-height: 2;
            background: var(--mono-bg);
            color: var(--mono-fg);
            padding: 2rem;
            border-radius: 4px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .arch-tree .dir {
            color: #8ecae6;
        }

        .arch-tree .note {
            color: var(--mono-cmt);
        }

        .arch-tree .hl {
            color: var(--mono-str);
        }

        .layer-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-top: 2.5rem;
        }

        .layer {
            display: flex;
            gap: 1.25rem;
            align-items: flex-start;
        }

        .layer-num {
            font-family: 'DM Mono', monospace;
            font-size: 0.7rem;
            color: var(--accent);
            background: rgba(200, 75, 47, 0.08);
            border: 1px solid rgba(200, 75, 47, 0.2);
            padding: 0.15rem 0.5rem;
            border-radius: 2px;
            white-space: nowrap;
            margin-top: 0.15rem;
        }

        .layer-title {
            font-weight: 500;
            margin-bottom: 0.25rem;
            font-size: 0.95rem;
        }

        .layer-desc {
            font-size: 0.85rem;
            color: var(--muted);
        }

        .middleware {
            padding: 7rem 3rem;
        }

        .mw-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-top: 3rem;
        }

        .mw-card {
            padding: 2rem;
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
            transition: all 0.2s;
        }

        .mw-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 3px;
            height: 100%;
            background: var(--accent);
            transform: scaleY(0);
            transform-origin: bottom;
            transition: transform 0.3s;
        }

        .mw-card:hover::before {
            transform: scaleY(1);
        }

        .mw-card:hover {
            border-color: rgba(200, 75, 47, 0.3);
        }

        .mw-tag {
            font-family: 'DM Mono', monospace;
            font-size: 0.72rem;
            color: var(--accent);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
        }

        .mw-title {
            font-family: 'DM Serif Display', serif;
            font-size: 1.15rem;
            margin-bottom: 0.5rem;
        }

        .mw-desc {
            font-size: 0.85rem;
            color: var(--muted);
            line-height: 1.7;
        }

        .quickstart {
            padding: 7rem 3rem;
            background: var(--ink);
            color: var(--paper);
        }

        .quickstart h2 {
            color: var(--paper);
        }

        .qs-steps {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0;
            margin-top: 3rem;
            border: 1px solid #2a2620;
        }

        .qs-step {
            padding: 2.5rem;
            border-right: 1px solid #2a2620;
        }

        .qs-step:last-child {
            border-right: none;
        }

        .qs-num {
            font-family: 'DM Serif Display', serif;
            font-size: 4rem;
            color: #2a2620;
            line-height: 1;
            margin-bottom: 1.5rem;
        }

        .qs-title {
            font-weight: 500;
            font-size: 1rem;
            margin-bottom: 1rem;
            color: var(--paper);
        }

        .qs-code {
            font-family: 'DM Mono', monospace;
            font-size: 0.75rem;
            background: #141210;
            color: var(--mono-str);
            padding: 0.75rem 1rem;
            border-radius: 2px;
            line-height: 1.8;
        }

        .qs-desc {
            font-size: 0.85rem;
            color: #6b6358;
            margin-top: 1rem;
            line-height: 1.7;
        }

        footer {
            padding: 3rem;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .footer-logo {
            font-family: 'DM Serif Display', serif;
            font-size: 1.2rem;
        }

        .footer-logo span {
            color: var(--accent);
        }

        .footer-meta {
            font-family: 'DM Mono', monospace;
            font-size: 0.72rem;
            color: var(--muted);
        }

        .footer-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .footer-links a {
            font-size: 0.8rem;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: var(--ink);
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-eyebrow {
            animation: fadeUp 0.6s ease both;
        }

        h1 {
            animation: fadeUp 0.6s 0.1s ease both;
        }

        .hero-sub {
            animation: fadeUp 0.6s 0.2s ease both;
        }

        .hero-actions {
            animation: fadeUp 0.6s 0.3s ease both;
        }

        .hero-right {
            animation: fadeUp 0.6s 0.2s ease both;
        }

        @media (max-width: 900px) {
            nav {
                padding: 1rem 1.5rem;
            }

            .nav-links {
                display: none;
            }

            .hero {
                grid-template-columns: 1fr;
            }

            .hero-left {
                padding: 5rem 1.5rem 3rem;
                border-right: none;
                border-bottom: 1px solid var(--border);
            }

            .hero-right {
                padding: 3rem 1.5rem;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .stat {
                border-right: none;
                border-bottom: 1px solid var(--border);
            }

            .features,
            .architecture,
            .middleware,
            .quickstart {
                padding: 4rem 1.5rem;
            }

            .features-grid,
            .mw-grid,
            .qs-steps {
                grid-template-columns: 1fr;
            }

            .feature {
                border-right: none;
            }

            .arch-grid {
                grid-template-columns: 1fr;
                gap: 2.5rem;
            }

            .qs-step {
                border-right: none;
                border-bottom: 1px solid #2a2620;
            }

            footer {
                flex-direction: column;
                gap: 1.5rem;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <nav>
        <a class="nav-logo" href="#">Etus<span>.</span></a>
        <ul class="nav-links">
            <li><a href="#features">Features</a></li>
            <li><a href="#architecture">Architecture</a></li>
            <li><a href="#middleware">Middleware</a></li>
            <li><a href="#quickstart">Get Started</a></li>
        </ul>
        <span class="nav-badge">v1.0.0 — PHP 8.2+</span>
    </nav>

    <section class="hero">
        <div class="hero-left">
            <p class="hero-eyebrow">Open source PHP framework</p>
            <h1>Build fast.<br><em>Stay lean.</em></h1>
            <p class="hero-sub">Etus is a lightweight, expressive PHP framework designed for developers who want full control without the weight of a full-stack monolith.</p>
            <div class="hero-actions">
                <a href="#quickstart" class="btn btn-primary">Get started →</a>
                <a href="#features" class="btn btn-secondary">Explore features</a>
            </div>
        </div>
        <div class="hero-right">
            <div class="code-window">
                <div class="code-titlebar">
                    <div class="dot dot-r"></div>
                    <div class="dot dot-y"></div>
                    <div class="dot dot-g"></div>
                    <span class="code-filename">routes/web.php</span>
                </div>
                <div class="code-body"><span class="c">// Register routes with middleware</span>
                    <span class="k">return</span> <span class="f">function</span>(<span class="v">Router</span> <span class="v">$router</span>) {

                    <span class="v">$router</span>-><span class="f">get</span>(<span class="s">'/'</span>, [<span class="v">HomeController</span>::<span class="k">class</span>, <span class="s">'index'</span>]);

                    <span class="v">$router</span>-><span class="f">get</span>(<span class="s">'/dashboard'</span>,
                    [<span class="v">DashboardController</span>::<span class="k">class</span>, <span class="s">'index'</span>],
                    [<span class="s">'auth'</span>, <span class="s">'log'</span>]
                    );

                    <span class="v">$router</span>-><span class="f">post</span>(<span class="s">'/login'</span>,
                    [<span class="v">AuthController</span>::<span class="k">class</span>, <span class="s">'store'</span>],
                    [<span class="s">'guest'</span>, <span class="s">'csrf'</span>]
                    );
                    };
                </div>
            </div>
            <div class="code-window" style="margin-top:1.25rem;">
                <div class="code-titlebar">
                    <div class="dot dot-r"></div>
                    <div class="dot dot-y"></div>
                    <div class="dot dot-g"></div>
                    <span class="code-filename">app/Controllers/HomeController.php</span>
                </div>
                <div class="code-body"><span class="k">class</span> <span class="v">HomeController</span> <span class="k">extends</span> <span class="v">BaseController</span>
                    {
                    <span class="k">protected</span> <span class="f">array</span> <span class="v">$middleware</span> = [<span class="s">'auth'</span>];

                    <span class="k">public function</span> <span class="f">index</span>(): <span class="v">Response</span>
                    {
                    <span class="k">return</span> <span class="f">view</span>(<span class="s">'home.index'</span>, [
                    <span class="s">'title'</span> => <span class="s">'Dashboard'</span>,
                    <span class="s">'users'</span> => (<span class="k">new</span> <span class="v">User</span>)-><span class="f">all</span>(),
                    ]);
                    }
                    }
                </div>
            </div>
        </div>
    </section>

    <div class="stats">
        <div class="stat">
            <div class="stat-number">4<span>+</span></div>
            <div class="stat-label">Middleware layers</div>
        </div>
        <div class="stat">
            <div class="stat-number">2<span>+</span></div>
            <div class="stat-label">Database drivers</div>
        </div>
        <div class="stat">
            <div class="stat-number">PHP<span> 8.2</span></div>
            <div class="stat-label">Minimum requirement</div>
        </div>
    </div>

    <section class="features" id="features">
        <div class="section-header"><span class="section-label">01 — Features</span>
            <div class="section-rule"></div>
        </div>
        <h2>Everything you need.<br>Nothing you don't.</h2>
        <div class="features-grid">
            <div class="feature"><span class="feature-icon">⚡</span>
                <div class="feature-title">FastRoute dispatcher</div>
                <p class="feature-desc">Blazing fast route resolution powered by nikic/fast-route. Zero overhead routing with full HTTP method support.</p>
            </div>
            <div class="feature"><span class="feature-icon">🔒</span>
                <div class="feature-title">Middleware pipeline</div>
                <p class="feature-desc">Apply middleware globally, per-route, per-controller, or per-method. Built-in Auth, Guest, CSRF, and Request Logger.</p>
            </div>
            <div class="feature"><span class="feature-icon">🗄️</span>
                <div class="feature-title">Multi-driver database</div>
                <p class="feature-desc">SQLite and MySQL out of the box. Fluent query builder, audit fields, and activity logging — all configurable per model.</p>
            </div>
            <div class="feature"><span class="feature-icon">🎨</span>
                <div class="feature-title">PHP template views</div>
                <p class="feature-desc">Plain PHP templates with layout support via a $layout variable. Dot notation resolves to nested directories.</p>
            </div>
            <div class="feature"><span class="feature-icon">🌱</span>
                <div class="feature-title">Environment config</div>
                <p class="feature-desc">vlucas/phpdotenv powers clean .env-based configuration with a typed env() helper for safe value casting.</p>
            </div>
            <div class="feature"><span class="feature-icon">📋</span>
                <div class="feature-title">Activity logging</div>
                <p class="feature-desc">Automatic create/update/delete audit trails with configurable user attribution. Enable or disable per model.</p>
            </div>
            <div class="feature"><span class="feature-icon">🛡️</span>
                <div class="feature-title">CSRF protection</div>
                <p class="feature-desc">Token-based CSRF on all state-changing requests. csrf_field() helper for forms with automatic rotation.</p>
            </div>
            <div class="feature"><span class="feature-icon">📦</span>
                <div class="feature-title">PSR-4 autoloading</div>
                <p class="feature-desc">Clean namespace separation between framework and app code. Composer-managed with files-based helper autoloading.</p>
            </div>
            <div class="feature"><span class="feature-icon">🔍</span>
                <div class="feature-title">Path validation</div>
                <p class="feature-desc">Boot-time path verification catches missing files immediately with clear error messages before a request hits.</p>
            </div>
        </div>
    </section>

    <section class="architecture" id="architecture">
        <div class="section-header"><span class="section-label">02 — Architecture</span>
            <div class="section-rule"></div>
        </div>
        <div class="arch-grid">
            <div>
                <h2>Layered, clean,<br>and deliberate.</h2>
                <div class="layer-list">
                    <div class="layer"><span class="layer-num">01</span>
                        <div>
                            <div class="layer-title">Entry point</div>
                            <div class="layer-desc">public/index.php defines constants, starts session, loads the autoloader, and boots the kernel.</div>
                        </div>
                    </div>
                    <div class="layer"><span class="layer-num">02</span>
                        <div>
                            <div class="layer-title">Bootstrap</div>
                            <div class="layer-desc">Wires .env, view path, middleware registry, database connection, and routes. Returns a configured Kernel.</div>
                        </div>
                    </div>
                    <div class="layer"><span class="layer-num">03</span>
                        <div>
                            <div class="layer-title">Middleware pipeline</div>
                            <div class="layer-desc">Global → route → controller → method middleware run in order, wrapping the controller call symmetrically.</div>
                        </div>
                    </div>
                    <div class="layer"><span class="layer-num">04</span>
                        <div>
                            <div class="layer-title">Controller → View</div>
                            <div class="layer-desc">Controllers return Response objects. view() renders PHP templates with optional layout wrapping.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="arch-tree">
                <span class="dir">project/</span>
                ├── <span class="dir">app/</span>
                │ ├── <span class="dir">config/</span>
                │ │ ├── <span class="hl">database.php</span>
                │ │ └── <span class="hl">helpers.php</span>
                │ ├── <span class="dir">Controllers/</span>
                │ ├── <span class="dir">Models/</span>
                │ └── <span class="dir">views/</span>
                │ └── <span class="dir">layouts/</span>
                ├── <span class="dir">framework/</span>
                │ ├── <span class="dir">Controllers/</span>
                │ ├── <span class="dir">Database/</span>
                │ │ ├── Connection.php
                │ │ ├── Model.php
                │ │ ├── QueryBuilder.php
                │ │ └── ActivityLogger.php
                │ ├── <span class="dir">Http/</span>
                │ │ ├── Kernel.php
                │ │ ├── Request.php
                │ │ ├── Response.php
                │ │ └── <span class="dir">Middleware/</span>
                │ ├── <span class="dir">Routing/</span>
                │ └── <span class="dir">View/</span>
                ├── <span class="dir">bootstrap/</span>
                │ └── <span class="hl">app.php</span>
                ├── <span class="dir">routes/</span>
                │ └── <span class="hl">web.php</span>
                ├── <span class="dir">public/</span>
                │ └── <span class="hl">index.php</span>
                └── <span class="hl">.env</span>
            </div>
        </div>
    </section>

    <section class="middleware" id="middleware">
        <div class="section-header"><span class="section-label">03 — Middleware</span>
            <div class="section-rule"></div>
        </div>
        <h2>Four levels of control.</h2>
        <div class="mw-grid">
            <div class="mw-card">
                <div class="mw-tag">Level 1 — Global</div>
                <div class="mw-title">Every request</div>
                <p class="mw-desc">Add aliases to Kernel::$middleware. Runs before routing resolves. Perfect for request logging on all routes.</p>
            </div>
            <div class="mw-card">
                <div class="mw-tag">Level 2 — Route</div>
                <div class="mw-title">Per route</div>
                <p class="mw-desc">Pass middleware as the third argument in web.php. Clean and readable, co-located with the route definition.</p>
            </div>
            <div class="mw-card">
                <div class="mw-tag">Level 3 — Controller</div>
                <div class="mw-title">Whole controller</div>
                <p class="mw-desc">Declare $middleware on your controller class. Applied to every method, overridable with only/except.</p>
            </div>
            <div class="mw-card">
                <div class="mw-tag">Level 4 — Method</div>
                <div class="mw-title">Per method</div>
                <p class="mw-desc">Use $middlewareOnly and $middlewareExcept for surgical, per-action middleware control on individual methods.</p>
            </div>
        </div>
    </section>

    <section class="quickstart" id="quickstart">
        <div class="section-header"><span class="section-label" style="color:#6b6358;">04 — Get started</span>
            <div class="section-rule" style="background:#2a2620;"></div>
        </div>
        <h2>Up in three steps.</h2>
        <div class="qs-steps">
            <div class="qs-step">
                <div class="qs-num">01</div>
                <div class="qs-title">Install dependencies</div>
                <div class="qs-code">composer install</div>
                <p class="qs-desc">Installs FastRoute, phpdotenv, and symfony/var-dumper via Composer.</p>
            </div>
            <div class="qs-step">
                <div class="qs-num">02</div>
                <div class="qs-title">Configure environment</div>
                <div class="qs-code">cp .env.example .env<br><br>DB_DRIVER=sqlite<br>DB_DATABASE=database/db.sqlite</div>
                <p class="qs-desc">Set your database driver, credentials, and app settings in the .env file.</p>
            </div>
            <div class="qs-step">
                <div class="qs-num">03</div>
                <div class="qs-title">Start the server</div>
                <div class="qs-code">composer run start</div>
                <p class="qs-desc">Starts PHP's built-in server at localhost:8500. Visit it in your browser.</p>
            </div>
        </div>
    </section>

    <footer>
        <div class="footer-logo">Etus<span>.</span></div>
        <ul class="footer-links">
            <li><a href="#features">Features</a></li>
            <li><a href="#architecture">Architecture</a></li>
            <li><a href="#quickstart">Get Started</a></li>
        </ul>
        <div class="footer-meta">v1.0.0 · PHP 8.2+ · MIT License · © 2026 ETUS</div>
    </footer>

    <script>
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, i) => {
                if (entry.isIntersecting) {
                    setTimeout(() => entry.target.classList.add('visible'), i * 80);
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1
        });
        document.querySelectorAll('.stat, .feature').forEach(el => observer.observe(el));
    </script>
</body>

</html>