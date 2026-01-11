<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', core()->getConfigData('portal.general.settings.portal_name') ?? 'Customer Portal') - {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <style>
        :root {
            --primary-color:
                {{ core()->getConfigData('portal.general.settings.primary_color') ?? '#4F46E5' }}
            ;
            --primary-hover:
                {{ core()->getConfigData('portal.general.settings.primary_color') ?? '#4338CA' }}
            ;
            /* Ideal to have a darker shade logic */
            --secondary-color: #64748B;
            --bg-color:
                {{ core()->getConfigData('portal.general.settings.background_color') ?? '#F8FAFC' }}
            ;
            --surface-color: #FFFFFF;
            --text-main: #1E293B;
            --text-secondary: #64748B;
            --border-color: #E2E8F0;
            --danger-color: #EF4444;
            --success-color: #10B981;
        }

        /* Hover effect for primary buttons */
        .btn-primary:hover {
            filter: brightness(0.9);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
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

        .min-h-screen {
            min-height: 100vh;
        }

        .py-4 {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .px-4 {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .mb-4 {
            margin-bottom: 1rem;
        }

        .mt-4 {
            margin-top: 1rem;
        }

        .text-sm {
            font-size: 0.875rem;
        }

        .font-bold {
            font-weight: 700;
        }

        .font-medium {
            font-weight: 500;
        }

        .text-center {
            text-align: center;
        }

        .w-full {
            width: 100%;
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
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-link {
            color: var(--primary-color);
            background: none;
            padding: 0;
        }

        .btn-link:hover {
            text-decoration: underline;
        }


        /* Forms */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-main);
        }

        .form-control {
            width: 100%;
            padding: 0.625rem;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            font-family: inherit;
            color: var(--text-main);
            box-sizing: border-box;
            /* Fix width issues */
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
        }

        .invalid-feedback {
            color: var(--danger-color);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Cards */
        .card {
            background: var(--surface-color);
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
        }

        /* Navbar */
        .navbar {
            background: var(--surface-color);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
        }

        .nav-link {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            margin-right: 1.5rem;
            transition: color 0.2s;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--primary-color);
        }

        .nav-brand {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-main);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-brand img {
            max-height: 40px;
            width: auto;
        }

        /* Alert */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }

        .alert-error {
            background-color: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }
    </style>
    @stack('styles')
</head>

<body>
    @auth('portal')
        <header class="navbar">
            <div class="container flex items-center justify-between">
                <a href="{{ route('portal.dashboard') }}" class="nav-brand">
                    @if($logo = core()->getConfigData('portal.general.settings.logo'))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($logo) }}" alt="{{ core()->getConfigData('portal.general.settings.portal_name') }}">
                    @else
                        {{ core()->getConfigData('portal.general.settings.portal_name') ?? config('app.name') . ' Portal' }}
                    @endif
                </a>

                <nav class="flex items-center">
                    <a href="{{ route('portal.dashboard') }}"
                        class="nav-link {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">Dashboard</a>

                    <a href="{{ route('portal.tickets.index') }}"
                        class="nav-link {{ request()->routeIs('portal.tickets*') ? 'active' : '' }}">Tickets</a>

                    <a href="{{ route('portal.quotes.index') }}"
                        class="nav-link {{ request()->routeIs('portal.quotes*') ? 'active' : '' }}">Quotes</a>

                    <a href="{{ route('portal.kb.index') }}"
                        class="nav-link {{ request()->routeIs('portal.kb*') ? 'active' : '' }}">Knowledge Base</a>

                    <a href="{{ route('portal.profile.edit') }}"
                        class="nav-link {{ request()->routeIs('portal.profile*') ? 'active' : '' }}">My Profile</a>

                    <form action="{{ route('portal.logout') }}" method="POST" class="ml-4" style="margin-left: 1rem;">
                        @csrf
                        <button type="submit" class="btn btn-link">Logout</button>
                    </form>
                </nav>
            </div>
        </header>
    @endauth

    <main class="@auth('portal') container py-4 @else w-full @endauth">
        @yield('content')
    </main>
</body>

</html>