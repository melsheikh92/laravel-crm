<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ProvenSuccess CRM - Simple, Powerful, Affordable</title>
    <link rel="icon" type="image/png" href="{{ asset('hamzah_logo.png') }}">

    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;800&display=swap"
        rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            /* Brand Colors: Rich Purple Theme */
            --primary: 265 50% 40%;
            /* Rich Purple */
            --primary-dark: 265 60% 25%;
            /* darker purple for hover/footer */
            --primary-light: 265 70% 96%;
            /* very light purple tint for backgrounds */
            --accent: 38 95% 55%;
            /* Orange/Gold for CTAs/Highlights */

            --text-main: #1f2937;
            --text-muted: #6b7280;
            --white: #ffffff;
            --glass: rgba(255, 255, 255, 0.9);

            --radius-btn: 50px;
            --radius-card: 24px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-purple: 0 10px 40px -10px hsla(265, 50%, 40%, 0.3);
        }

        /* RESET & BASE */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            background-color: var(--white);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        h1,
        h2,
        h3,
        h4,
        h5 {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            line-height: 1.2;
            color: hsl(var(--primary-dark));
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
        }

        ul {
            list-style: none;
        }

        /* UTILITIES */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .text-center {
            text-align: center;
        }

        .text-primary {
            color: hsl(var(--primary));
        }

        /* BUTTONS */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 32px;
            border-radius: var(--radius-btn);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
            border: 2px solid transparent;
            gap: 8px;
        }

        .btn-primary {
            background-color: hsl(var(--primary));
            color: var(--white);
            box-shadow: 0 4px 14px 0 hsla(265, 50%, 40%, 0.2);
        }

        .btn-primary:hover {
            background-color: hsl(var(--primary-dark));
            transform: translateY(-2px);
            box-shadow: 0 8px 24px 0 hsla(265, 50%, 40%, 0.3);
        }

        .btn-secondary {
            background-color: hsl(var(--primary-light));
            color: hsl(var(--primary));
        }

        .btn-secondary:hover {
            background-color: hsla(265, 50%, 40%, 0.1);
            color: hsl(var(--primary-dark));
        }

        .btn-outline {
            background-color: transparent;
            border: 2px solid #e5e7eb;
            color: var(--text-main);
        }

        .btn-outline:hover {
            border-color: hsl(var(--primary));
            color: hsl(var(--primary));
            background-color: white;
        }

        /* HEADER */
        header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 16px 0;
            transition: all 0.3s ease;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: hsl(var(--primary));
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo img {
            height: 36px;
            width: auto;
        }

        .nav-links {
            display: flex;
            gap: 32px;
            align-items: center;
        }

        .nav-links a.link {
            font-weight: 500;
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .nav-links a.link:hover {
            color: hsl(var(--primary));
        }

        /* HERO SECTION */
        .hero {
            padding-top: 160px;
            padding-bottom: 80px;
            background: radial-gradient(circle at 50% 0%, hsl(265, 70%, 96%) 0%, var(--white) 60%);
            position: relative;
            overflow: hidden;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 100px;
            font-size: 0.85rem;
            color: hsl(var(--primary));
            font-weight: 600;
            margin-bottom: 32px;
            box-shadow: var(--shadow-sm);
        }

        .hero h1 {
            font-size: 4rem;
            margin-bottom: 24px;
            letter-spacing: -0.02em;
        }

        .hero h1 span {
            background: linear-gradient(135deg, hsl(var(--primary)) 0%, hsl(280, 70%, 50%) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: 1.25rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto 40px;
        }

        .hero-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-direction: column;
            align-items: center;
        }

        .btn-row {
            display: flex;
            gap: 16px;
        }

        .hero-trust-text {
            margin-top: 16px;
            color: var(--text-muted);
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Hero Dashboard Image */
        .hero-dashboard {
            margin-top: 80px;
            position: relative;
            z-index: 10;
        }

        .dashboard-frame {
            background: var(--white);
            border-radius: 16px;
            padding: 12px;
            box-shadow: var(--shadow-purple);
            border: 1px solid rgba(0, 0, 0, 0.08);
            max-width: 1100px;
            margin: 0 auto;
        }

        .browser-header {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
            padding-left: 12px;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .dot-red {
            background: #ff5f56;
        }

        .dot-yellow {
            background: #ffbd2e;
        }

        .dot-green {
            background: #27c93f;
        }

        .dashboard-img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            display: block;
            background: #f3f4f6;
            /* Placeholder color before image loads */
            min-height: 400px;
            object-fit: cover;
            object-position: top;
        }

        /* TRUST LOGOS */
        .trusted-by {
            padding: 40px 0;
            border-bottom: 1px solid #f3f4f6;
            background: var(--white);
        }

        .logo-scroll {
            display: flex;
            justify-content: center;
            gap: 60px;
            flex-wrap: wrap;
            opacity: 0.6;
            filter: grayscale(100%);
            margin: 0 auto;
            max-width: 900px;
        }

        .logo-item {
            font-weight: 700;
            font-size: 1.2rem;
            color: #9ca3af;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* FEATURES GRID */
        .section {
            padding: 100px 0;
        }

        .bg-subtle {
            background-color: #f9fafb;
        }

        .section-header {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 60px;
        }

        .section-header h2 {
            font-size: 2.75rem;
            margin-bottom: 16px;
        }

        .section-header p {
            font-size: 1.125rem;
            color: var(--text-muted);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 32px;
        }

        .feature-card {
            background: var(--white);
            padding: 40px;
            border-radius: var(--radius-card);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: transparent;
        }

        .icon-box {
            width: 56px;
            height: 56px;
            background: hsl(var(--primary-light));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: hsl(var(--primary));
            font-size: 1.5rem;
            margin-bottom: 24px;
        }

        .feature-card h3 {
            font-size: 1.25rem;
            margin-bottom: 12px;
        }

        .feature-card p {
            color: var(--text-muted);
            font-size: 1rem;
            line-height: 1.6;
        }

        /* ZIG ZAG / ALTERNATE */
        .feature-split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
            margin-bottom: 120px;
        }

        .feature-split:last-child {
            margin-bottom: 0;
        }

        @media (max-width: 900px) {
            .feature-split {
                grid-template-columns: 1fr;
                gap: 40px;
                text-align: center;
            }

            .feature-split.reversed {
                direction: ltr;
            }

            /* Force text order on mobile if needed, but grid usually handles it stacking */
        }

        .split-content h3 {
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .split-content p {
            color: var(--text-muted);
            margin-bottom: 32px;
            font-size: 1.1rem;
        }

        .split-image {
            background: #f3f4f6;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        .split-image img {
            width: 100%;
            height: auto;
            display: block;
        }

        /* CTA SECTION */
        .cta-section {
            background: hsl(var(--primary));
            color: var(--white);
            padding: 100px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            top: -50%;
            left: 50%;
            transform: translateX(-50%);
        }

        .cta-content {
            position: relative;
            z-index: 2;
            max-width: 700px;
            margin: 0 auto;
        }

        .cta-content h2 {
            color: var(--white);
            margin-bottom: 24px;
            font-size: 3rem;
        }

        .cta-content p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.25rem;
            margin-bottom: 40px;
        }

        .cta-btn-white {
            background: var(--white);
            color: hsl(var(--primary));
            padding: 16px 40px;
            font-size: 1.1rem;
        }

        .cta-btn-white:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            background: white;
            color: hsl(var(--primary-dark));
        }

        /* DEMO FORM OVERLAY in separate page traditionally, but keeping simple form here */
        .demo-form-container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
        }

        .form-input {
            width: 100%;
            padding: 14px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 16px;
            font-family: inherit;
            font-size: 1rem;
        }

        .form-input:focus {
            outline: 2px solid hsl(var(--primary));
            border-color: transparent;
        }

        /* FOOTER */
        footer {
            background: #111827;
            color: #d1d5db;
            padding: 80px 0 30px;
            font-size: 0.95rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 60px;
            margin-bottom: 60px;
        }

        .footer-brand h4 {
            color: white;
            margin-bottom: 16px;
            font-size: 1.5rem;
        }

        .footer-col h5 {
            color: white;
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 1rem;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a:hover {
            color: white;
        }

        .footer-bottom {
            border-top: 1px solid #374151;
            padding-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* INTERACTIVE FEATURES SHOWCASE */
        .interactive-features {
            display: grid;
            grid-template-columns: 450px 1fr;
            gap: 60px;
            align-items: start;
            margin-top: 60px;
        }

        .features-menu {
            display: flex;
            flex-direction: column;
            gap: 16px;
            position: sticky;
            top: 120px;
        }

        .feature-menu-item {
            background: white;
            padding: 24px;
            border-radius: 16px;
            border: 2px solid transparent;
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 16px;
            align-items: center;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .feature-menu-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: hsl(var(--primary));
            transform: scaleY(0);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .feature-menu-item:hover {
            transform: translateX(8px);
            box-shadow: var(--shadow-md);
            border-color: hsl(var(--primary-light));
        }

        .feature-menu-item.active {
            background: linear-gradient(135deg, hsl(var(--primary-light)) 0%, white 100%);
            border-color: hsl(var(--primary));
            box-shadow: var(--shadow-purple);
            transform: translateX(12px);
        }

        .feature-menu-item.active::before {
            transform: scaleY(1);
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            background: hsl(var(--primary-light));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: hsl(var(--primary));
            font-size: 1.3rem;
            transition: all 0.3s ease;
        }

        .feature-menu-item.active .feature-icon {
            background: hsl(var(--primary));
            color: white;
            transform: scale(1.1);
        }

        .feature-info h4 {
            font-size: 1.1rem;
            margin-bottom: 4px;
            color: hsl(var(--primary-dark));
            font-weight: 700;
        }

        .feature-info p {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin: 0;
            line-height: 1.4;
        }

        .feature-menu-item .arrow {
            color: var(--text-muted);
            font-size: 0.9rem;
            transition: all 0.3s ease;
            opacity: 0;
        }

        .feature-menu-item:hover .arrow,
        .feature-menu-item.active .arrow {
            opacity: 1;
            color: hsl(var(--primary));
        }

        .feature-menu-item.active .arrow {
            transform: translateX(4px);
        }

        .features-showcase {
            position: relative;
            min-height: 600px;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .showcase-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transform: scale(0.95);
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: none;
        }

        .showcase-image.active {
            opacity: 1;
            transform: scale(1);
            pointer-events: auto;
        }

        .showcase-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: top;
            display: block;
        }

        .image-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, transparent 100%);
            padding: 60px 40px 40px;
            color: white;
            transform: translateY(20px);
            opacity: 0;
            transition: all 0.4s ease 0.2s;
        }

        .showcase-image.active .image-caption {
            transform: translateY(0);
            opacity: 1;
        }

        .image-caption h5 {
            font-size: 1.5rem;
            margin-bottom: 8px;
            color: white;
        }

        .image-caption p {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
        }

        /* MOBILE */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .nav-links {
                display: none;
            }

            /* Simplified mobile nav hidden for now */
            .footer-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .hero-dashboard {
                margin-top: 40px;
            }

            .btn-row {
                flex-direction: column;
                width: 100%;
            }

            .btn {
                width: 100%;
            }

            .interactive-features {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .features-menu {
                position: static;
                order: 2;
            }

            .features-showcase {
                order: 1;
                min-height: 400px;
            }

            .feature-menu-item {
                padding: 20px;
            }

            .feature-menu-item:hover,
            .feature-menu-item.active {
                transform: translateX(0);
            }
        }
    </style>

</head>

<body>

    <header>
        <div class="container">
            <nav>
                <a href="#" class="logo">
                    <img src="{{ asset('hamzah_logo.png') }}" alt="Hamzah LLC">
                    ProvenSuccess
                </a>
                <ul class="nav-links">
                    <li><a href="#features" class="link">Features</a></li>
                    <li><a href="#about" class="link">Solutions</a></li>
                    <li><a href="#pricing" class="link">Pricing</a></li>
                </ul>
                <div class="nav-actions" style="display: flex; gap: 12px; align-items: center;">
                    @if(Route::has('admin.session.create'))
                        <a href="{{ route('admin.session.create') }}" class="link"
                            style="margin-right: 12px; font-weight: 600;">Sign In</a>
                    @endif
                    <a href="#book-demo" class="btn btn-primary" style="padding: 10px 24px; font-size: 0.9rem;">Get
                        Started</a>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero text-center">
            <div class="container">
                <div class="hero-badge">
                    <i class="fa-solid fa-sparkles"></i>
                    <span>New Feature: AI Sales Assistant</span>
                </div>
                <h1>The CRM that turns <br><span>relationships into revenue.</span></h1>
                <p>Simple enough for startups, powerful enough for enterprises. Organize leads, automate follow-ups, and
                    close more deals with ProvenSuccess.</p>

                <div class="hero-actions">
                    <div class="btn-row">
                        <a href="#book-demo" class="btn btn-primary">Start Free Trial <i
                                class="fa-solid fa-arrow-right"></i></a>
                        <a href="#demo-video" class="btn btn-outline"><i class="fa-solid fa-play"></i> Watch Demo</a>
                    </div>
                    <div class="hero-trust-text">
                        <i class="fa-solid fa-check-circle text-primary"></i> No credit card required &middot; 14-day
                        free trial
                    </div>
                </div>

                <div class="hero-dashboard">
                    <div class="dashboard-frame">
                        <div class="browser-header">
                            <div class="dot dot-red"></div>
                            <div class="dot dot-yellow"></div>
                            <div class="dot dot-green"></div>
                        </div>
                        <!-- Using the existing asset or a placeholder if looks better -->
                        <img src="{{ asset('ai_crm_dashboard.png') }}" alt="CRM Dashboard" class="dashboard-img">
                    </div>
                </div>
            </div>
        </section>

        <!-- Trusted By Strip -->
        <div class="trusted-by">
            <div class="container text-center">
                <p
                    style="margin-bottom: 24px; font-weight: 600; font-size: 0.85rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 1px;">
                    Trusted by high-growth teams at</p>
                <div class="logo-scroll">
                    <div class="logo-item"><i class="fa-brands fa-stripe"></i> STRIPE</div>
                    <div class="logo-item"><i class="fa-brands fa-airbnb"></i> AIRBNB</div>
                    <div class="logo-item"><i class="fa-brands fa-spotify"></i> SPOTIFY</div>
                    <div class="logo-item"><i class="fa-brands fa-slack"></i> SLACK</div>
                    <div class="logo-item"><i class="fa-brands fa-google"></i> GOOGLE</div>
                </div>
            </div>
        </div>

        <!-- Interactive Features Showcase -->
        <section id="features" class="section bg-subtle">
            <div class="container">
                <div class="section-header">
                    <h2>Everything you need to grow</h2>
                    <p>Stop juggling spreadsheets and disconnected tools. ProvenSuccess gives you one central command
                        center for your entire business.</p>
                </div>

                <div class="interactive-features">
                    <div class="features-menu">
                        <div class="feature-menu-item active" data-feature="leads">
                            <div class="feature-icon">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div class="feature-info">
                                <h4>Lead Management</h4>
                                <p>Capture, track, and nurture leads from first contact to closed deal</p>
                            </div>
                            <i class="fa-solid fa-chevron-right arrow"></i>
                        </div>

                        <div class="feature-menu-item" data-feature="automation">
                            <div class="feature-icon">
                                <i class="fa-solid fa-bolt"></i>
                            </div>
                            <div class="feature-info">
                                <h4>Smart Automation</h4>
                                <p>Automate repetitive tasks and focus on what matters most</p>
                            </div>
                            <i class="fa-solid fa-chevron-right arrow"></i>
                        </div>

                        <div class="feature-menu-item" data-feature="pipeline">
                            <div class="feature-icon">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                            <div class="feature-info">
                                <h4>Visual Pipeline</h4>
                                <p>Drag-and-drop deals through your sales stages effortlessly</p>
                            </div>
                            <i class="fa-solid fa-chevron-right arrow"></i>
                        </div>

                        <div class="feature-menu-item" data-feature="analytics">
                            <div class="feature-icon">
                                <i class="fa-solid fa-chart-pie"></i>
                            </div>
                            <div class="feature-info">
                                <h4>Analytics & Insights</h4>
                                <p>Make data-driven decisions with real-time reporting</p>
                            </div>
                            <i class="fa-solid fa-chevron-right arrow"></i>
                        </div>

                        <div class="feature-menu-item" data-feature="collaboration">
                            <div class="feature-icon">
                                <i class="fa-solid fa-comments"></i>
                            </div>
                            <div class="feature-info">
                                <h4>Team Collaboration</h4>
                                <p>Work together seamlessly with built-in chat and channels</p>
                            </div>
                            <i class="fa-solid fa-chevron-right arrow"></i>
                        </div>

                        <div class="feature-menu-item" data-feature="ai">
                            <div class="feature-icon">
                                <i class="fa-solid fa-wand-magic-sparkles"></i>
                            </div>
                            <div class="feature-info">
                                <h4>AI Assistant</h4>
                                <p>Let AI help you write emails, score leads, and predict outcomes</p>
                            </div>
                            <i class="fa-solid fa-chevron-right arrow"></i>
                        </div>
                    </div>

                    <div class="features-showcase">
                        <div class="showcase-image active" data-feature="leads">
                            <img src="{{ asset('leads_dashboard.png') }}" alt="Lead Management Dashboard">
                            <div class="image-caption">
                                <h5>Powerful Lead Management</h5>
                                <p>Track every interaction and never miss a follow-up</p>
                            </div>
                        </div>

                        <div class="showcase-image" data-feature="automation">
                            <img src="{{ asset('automation_workflows.png') }}" alt="Automation Workflows">
                            <div class="image-caption">
                                <h5>Intelligent Automation</h5>
                                <p>Set it and forget it - let automation handle the routine</p>
                            </div>
                        </div>

                        <div class="showcase-image" data-feature="pipeline">
                            <img src="{{ asset('sales_pipeline.png') }}" alt="Sales Pipeline">
                            <div class="image-caption">
                                <h5>Visual Sales Pipeline</h5>
                                <p>See your entire sales process at a glance</p>
                            </div>
                        </div>

                        <div class="showcase-image" data-feature="analytics">
                            <img src="{{ asset('analytics_dashboard.png') }}" alt="Analytics Dashboard">
                            <div class="image-caption">
                                <h5>Actionable Analytics</h5>
                                <p>Turn data into revenue with powerful insights</p>
                            </div>
                        </div>

                        <div class="showcase-image" data-feature="collaboration">
                            <img src="{{ asset('collaboration_channels.png') }}" alt="Team Collaboration">
                            <div class="image-caption">
                                <h5>Seamless Collaboration</h5>
                                <p>Keep your team aligned and productive</p>
                            </div>
                        </div>

                        <div class="showcase-image" data-feature="ai">
                            <img src="{{ asset('ai_assistant.png') }}" alt="AI Assistant">
                            <div class="image-caption">
                                <h5>AI-Powered Intelligence</h5>
                                <p>Work smarter with AI that learns from your data</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Zig Zag Detailed Features -->
        <section class="section">
            <div class="container">
                <!-- Feature 1 -->
                <div class="feature-split">
                    <div class="split-content">
                        <div style="color: hsl(var(--primary)); font-weight: 700; margin-bottom: 12px;">COMPLETE
                            VISIBILITY</div>
                        <h3>Never lose track of a customer again</h3>
                        <p>See every email, call, meeting, and note in one timeline. When a customer calls, you'll know
                            exactly what was discussed last time.</p>
                        <ul style="margin-bottom: 32px;">
                            <li style="margin-bottom: 12px; display: flex; gap: 10px;"><i
                                    class="fa-solid fa-check text-primary"></i> <span>Unified interaction history</span>
                            </li>
                            <li style="margin-bottom: 12px; display: flex; gap: 10px;"><i
                                    class="fa-solid fa-check text-primary"></i> <span>One-click call logging</span></li>
                            <li style="margin-bottom: 12px; display: flex; gap: 10px;"><i
                                    class="fa-solid fa-check text-primary"></i> <span>Email sync (Gmail &
                                    Outlook)</span></li>
                        </ul>
                        <a href="#" class="btn btn-outline">Learn more</a>
                    </div>
                    <div class="split-image">
                        <img src="{{ asset('ai_lead_scoring.png') }}" alt="Customer 360 View">
                    </div>
                </div>

                <!-- Feature 2 (Reversed) -->
                <div class="feature-split reversed" style="margin-top: 100px;">
                    <div class="split-image">
                        <img src="{{ asset('smart_automation.png') }}" alt="Reporting & Analytics">
                    </div>
                    <div class="split-content">
                        <div style="color: hsl(var(--primary)); font-weight: 700; margin-bottom: 12px;">REAL-TIME
                            INSIGHTS</div>
                        <h3>Make data-driven decisions</h3>
                        <p>Stop guessing. Our built-in reporting suite gives you instant answers on team performance,
                            revenue forecasts, and marketing ROI.</p>
                        <a href="#" class="btn btn-outline">Explore Analytics</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA / Book Demo -->
        <section id="book-demo" class="cta-section">
            <div class="container cta-content">
                <h2>Ready to scale your sales?</h2>
                <p>Join 10,000+ businesses who use ProvenSuccess to build better relationships and close more deals.</p>

                @if(session('success'))
                    <div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="demo-form-container" style="text-align: left; color: #333;">
                    <h3
                        style="margin-bottom: 20px; font-size: 1.5rem; text-align: center; color: hsl(var(--primary-dark));">
                        Request a Personal Demo</h3>
                    <form action="{{ route('demo.request') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label style="font-size: 0.9rem; font-weight: 600; margin-bottom: 4px; display: block;">Full
                                Name</label>
                            <input type="text" name="name" class="form-input" placeholder="e.g. John Doe" required>
                        </div>
                        <div class="form-group">
                            <label style="font-size: 0.9rem; font-weight: 600; margin-bottom: 4px; display: block;">Work
                                Email</label>
                            <input type="email" name="email" class="form-input" placeholder="john@company.com" required>
                        </div>
                        <div class="form-group">
                            <label
                                style="font-size: 0.9rem; font-weight: 600; margin-bottom: 4px; display: block;">Company
                                Size</label>
                            <select name="company_size" class="form-input">
                                <option value="1-10">1-10 Employees</option>
                                <option value="11-50">11-50 Employees</option>
                                <option value="50+">50+ Employees</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Schedule Demo</button>
                    </form>
                </div>
            </div>
        </section>

    </main>

    <footer id="about">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <h4>ProvenSuccess</h4>
                    <p style="color: #9ca3af; line-height: 1.6;">The most intuitive CRM for growing businesses. Powered
                        by Hamzah LLC technology infrastructure.</p>
                </div>
                <div class="footer-col">
                    <h5>Product</h5>
                    <ul class="footer-links">
                        <li><a href="#">Features</a></li>
                        <li><a href="#">Pricing</a></li>
                        <li><a href="#">Integrations</a></li>
                        <li><a href="#">Changelog</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h5>Company</h5>
                    <ul class="footer-links">
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h5>Resources</h5>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">API Documentation</a></li>
                        <li><a href="#">System Status</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} Hamzah LLC. All rights reserved.</p>
                <div style="display: flex; gap: 24px;">
                    <a href="#" style="color: #9ca3af;"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#" style="color: #9ca3af;"><i class="fa-brands fa-linkedin"></i></a>
                    <a href="#" style="color: #9ca3af;"><i class="fa-brands fa-facebook"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Interactive Features Showcase
        document.addEventListener('DOMContentLoaded', function () {
            const menuItems = document.querySelectorAll('.feature-menu-item');
            const showcaseImages = document.querySelectorAll('.showcase-image');
            let autoRotateInterval = null;
            let restartTimeout = null;
            let currentIndex = 0;

            function switchFeature(featureName) {
                // Remove active class from all menu items and images
                menuItems.forEach(item => item.classList.remove('active'));
                showcaseImages.forEach(img => img.classList.remove('active'));

                // Add active class to selected items
                const selectedMenuItem = document.querySelector(`.feature-menu-item[data-feature="${featureName}"]`);
                const selectedImage = document.querySelector(`.showcase-image[data-feature="${featureName}"]`);

                if (selectedMenuItem && selectedImage) {
                    selectedMenuItem.classList.add('active');
                    selectedImage.classList.add('active');
                }
            }

            function startAutoRotate() {
                // Clear any existing interval first
                stopAutoRotate();

                autoRotateInterval = setInterval(() => {
                    currentIndex = (currentIndex + 1) % menuItems.length;
                    const nextFeature = menuItems[currentIndex].getAttribute('data-feature');
                    switchFeature(nextFeature);
                }, 5000); // Change every 5 seconds
            }

            function stopAutoRotate() {
                if (autoRotateInterval) {
                    clearInterval(autoRotateInterval);
                    autoRotateInterval = null;
                }
                // Also clear any pending restart timeout
                if (restartTimeout) {
                    clearTimeout(restartTimeout);
                    restartTimeout = null;
                }
            }

            // Add click event listeners to menu items
            menuItems.forEach((item, index) => {
                item.addEventListener('click', function () {
                    // Stop auto-rotation and clear any pending restarts
                    stopAutoRotate();

                    // Update current index to clicked item
                    currentIndex = index;
                    const featureName = this.getAttribute('data-feature');
                    switchFeature(featureName);

                    // Restart auto-rotate after 5 seconds, continuing from current index
                    restartTimeout = setTimeout(() => {
                        startAutoRotate();
                    }, 5000);
                });

                // Add hover effect
                item.addEventListener('mouseenter', function () {
                    stopAutoRotate();
                });

                item.addEventListener('mouseleave', function () {
                    // Only restart if not already scheduled
                    if (!restartTimeout && !autoRotateInterval) {
                        restartTimeout = setTimeout(() => {
                            startAutoRotate();
                        }, 3000);
                    }
                });
            });

            // Start auto-rotation on page load
            startAutoRotate();

            // Pause auto-rotation when user scrolls away from features section
            const featuresSection = document.getElementById('features');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) {
                        stopAutoRotate();
                    } else {
                        // Only start if not already running
                        if (!autoRotateInterval) {
                            startAutoRotate();
                        }
                    }
                });
            }, { threshold: 0.5 });

            if (featuresSection) {
                observer.observe(featuresSection);
            }
        });
    </script>

</body>

</html>