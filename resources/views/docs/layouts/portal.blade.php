<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Documentation') - {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <style>
        :root {
            --primary-color: #4F46E5;
            --primary-hover: #4338CA;
            --secondary-color: #64748B;
            --bg-color: #F8FAFC;
            --surface-color: #FFFFFF;
            --text-main: #1E293B;
            --text-secondary: #64748B;
            --border-color: #E2E8F0;
            --danger-color: #EF4444;
            --success-color: #10B981;
            --sidebar-width: 280px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* Utility Classes */
        .flex {
            display: flex;
        }

        .flex-col {
            flex-direction: column;
        }

        .items-center {
            align-items: center;
        }

        .justify-between {
            justify-content: space-between;
        }

        .justify-center {
            justify-content: center;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .gap-4 {
            gap: 1rem;
        }

        .gap-6 {
            gap: 1.5rem;
        }

        .mb-2 {
            margin-bottom: 0.5rem;
        }

        .mb-4 {
            margin-bottom: 1rem;
        }

        .mb-6 {
            margin-bottom: 1.5rem;
        }

        .mt-2 {
            margin-top: 0.5rem;
        }

        .mt-4 {
            margin-top: 1rem;
        }

        .py-4 {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .px-4 {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .py-6 {
            padding-top: 1.5rem;
            padding-bottom: 1.5rem;
        }

        .px-6 {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }

        .text-sm {
            font-size: 0.875rem;
        }

        .text-lg {
            font-size: 1.125rem;
        }

        .text-xl {
            font-size: 1.25rem;
        }

        .text-2xl {
            font-size: 1.5rem;
        }

        .font-bold {
            font-weight: 700;
        }

        .font-medium {
            font-weight: 500;
        }

        .font-semibold {
            font-weight: 600;
        }

        .text-center {
            text-align: center;
        }

        .w-full {
            width: 100%;
        }

        .hidden {
            display: none;
        }

        /* Header */
        .docs-header {
            background: var(--surface-color);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .docs-brand {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-main);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .docs-brand:hover {
            color: var(--primary-color);
        }

        /* Search Box */
        .docs-search {
            flex: 1;
            max-width: 500px;
            margin: 0 2rem;
        }

        .docs-search input {
            width: 100%;
            padding: 0.625rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-family: inherit;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .docs-search input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        /* Layout */
        .docs-layout {
            display: flex;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Sidebar */
        .docs-sidebar {
            width: var(--sidebar-width);
            background: var(--surface-color);
            border-right: 1px solid var(--border-color);
            height: calc(100vh - 73px);
            position: sticky;
            top: 73px;
            overflow-y: auto;
            padding: 1.5rem 0;
            flex-shrink: 0;
        }

        .sidebar-section {
            padding: 0 1.5rem;
            margin-bottom: 2rem;
        }

        .sidebar-title {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            margin-bottom: 0.75rem;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-nav li {
            margin-bottom: 0.25rem;
        }

        .sidebar-nav a {
            display: block;
            padding: 0.5rem 0.75rem;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .sidebar-nav a:hover {
            background-color: var(--bg-color);
            color: var(--text-main);
        }

        .sidebar-nav a.active {
            background-color: rgba(79, 70, 229, 0.1);
            color: var(--primary-color);
            font-weight: 500;
        }

        /* Main Content */
        .docs-main {
            flex: 1;
            padding: 2rem 3rem;
            min-height: calc(100vh - 73px);
        }

        .docs-content {
            max-width: 800px;
        }

        /* Cards */
        .card {
            background: var(--surface-color);
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        /* Links */
        a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.2s;
        }

        a:hover {
            color: var(--primary-hover);
        }

        /* Breadcrumbs */
        .breadcrumbs {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }

        .breadcrumbs a {
            color: var(--text-secondary);
        }

        .breadcrumbs a:hover {
            color: var(--primary-color);
        }

        .breadcrumb-separator {
            color: var(--border-color);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
            text-decoration: none;
            font-size: 0.875rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            padding: 0.5rem;
            cursor: pointer;
            color: var(--text-main);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .docs-sidebar {
                position: fixed;
                left: -100%;
                top: 73px;
                z-index: 99;
                transition: left 0.3s;
                box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
            }

            .docs-sidebar.open {
                left: 0;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .docs-search {
                margin: 0 1rem;
            }

            .docs-main {
                padding: 1.5rem;
            }
        }

        @media (max-width: 640px) {
            .docs-search {
                display: none;
            }

            .docs-main {
                padding: 1rem;
            }

            .header-container {
                padding: 0 1rem;
            }
        }

        /* Scrollbar */
        .docs-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .docs-sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .docs-sidebar::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 3px;
        }

        .docs-sidebar::-webkit-scrollbar-thumb:hover {
            background: var(--text-secondary);
        }
    </style>
    @stack('styles')
</head>

<body>
    <div id="vue-app">
        <!-- Header -->
        <header class="docs-header">
            <div class="header-container">
                <button class="mobile-menu-toggle" onclick="toggleSidebar()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 12h18M3 6h18M3 18h18"/>
                    </svg>
                </button>

                <a href="{{ route('docs.index') }}" class="docs-brand">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                    </svg>
                    <span>Documentation</span>
                </a>

                <div class="docs-search">
                    @include('docs.partials.search')
                </div>

            <nav class="flex items-center gap-4">
                <a href="{{ url('/') }}" class="btn btn-link" style="color: var(--text-secondary);">Back to App</a>
            </nav>
        </div>
    </header>

    <!-- Layout -->
    <div class="docs-layout">
        <!-- Sidebar -->
        <aside class="docs-sidebar" id="docs-sidebar">
            <!-- Getting Started -->
            <div class="sidebar-section">
                <div class="sidebar-title">Getting Started</div>
                <ul class="sidebar-nav">
                    <li><a href="{{ route('docs.index') }}" class="{{ request()->routeIs('docs.index') ? 'active' : '' }}">Overview</a></li>
                    <li><a href="#installation">Installation</a></li>
                    <li><a href="#quick-start">Quick Start</a></li>
                    <li><a href="#configuration">Configuration</a></li>
                </ul>
            </div>

            <!-- Features -->
            <div class="sidebar-section">
                <div class="sidebar-title">Features</div>
                <ul class="sidebar-nav">
                    <li><a href="#leads">Leads Management</a></li>
                    <li><a href="#contacts">Contacts</a></li>
                    <li><a href="#products">Products</a></li>
                    <li><a href="#quotes">Quotes</a></li>
                </ul>
            </div>

            <!-- API Reference -->
            <div class="sidebar-section">
                <div class="sidebar-title">API Reference</div>
                <ul class="sidebar-nav">
                    <li><a href="#api-overview">Overview</a></li>
                    <li><a href="#api-authentication">Authentication</a></li>
                    <li><a href="#api-endpoints">Endpoints</a></li>
                    <li><a href="#api-examples">Examples</a></li>
                </ul>
            </div>

            <!-- Guides -->
            <div class="sidebar-section">
                <div class="sidebar-title">Guides</div>
                <ul class="sidebar-nav">
                    <li><a href="#user-guide">User Guide</a></li>
                    <li><a href="#admin-guide">Admin Guide</a></li>
                    <li><a href="#integration">Integration</a></li>
                </ul>
            </div>

            <!-- Troubleshooting -->
            <div class="sidebar-section">
                <div class="sidebar-title">Support</div>
                <ul class="sidebar-nav">
                    <li><a href="#troubleshooting">Troubleshooting</a></li>
                    <li><a href="#faq">FAQ</a></li>
                    <li><a href="#contact">Contact Us</a></li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="docs-main">
            @yield('content')
        </main>
    </div>

    <!-- Overlay for mobile sidebar -->
    <div id="sidebar-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 98; top: 73px;" onclick="toggleSidebar()"></div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('docs-sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            sidebar.classList.toggle('open');
            overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
        }

        // Active link highlighting
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const links = document.querySelectorAll('.sidebar-nav a');

            links.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                }
            });
        });
    </script>
    @stack('scripts')
</div>
</body>

</html>
