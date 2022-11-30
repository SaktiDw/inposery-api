<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="/resources/css/uicons-regular-rounded/webfonts/uicons-regular-rounded.css">
    <!-- Scripts -->
    @vite(['resources/css/app.css',"/resources/css/uicons-regular-rounded/webfonts/uicons-regular-rounded.css", 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <!-- Page Heading -->
        <div class="bg-slate-100 text-slate-800 dark:bg-slate-900 dark:text-white">
            <x-navigation x-data="{toggle}" />
            <div class="flex h-screen overflow-hidden">
                <x-sidebar>
                    <x-sidebaritem icon="fi-rr-apps" href="{{ '' }}" active="{{ '' }}">Dashboard</x-sidebaritem>
                    <x-sidebaritem icon="fi-rr-boxes" href="{{ '' }}" active="{{ '' }}">Product</x-sidebaritem>
                    <x-sidebaritem icon="fi-rr-money-check-edit" href="{{ route('store.transactions') }}" active="{{ request()->routeIs('store.transactions') ? 'active' : '' }}">Transaction</x-sidebaritem>
                    <x-sidebaritem icon="fi-rr-store-alt" href="{{ '' }}" active="{{ '' }}">Cashier</x-sidebaritem>
                    <x-sidebaritem icon="fi-rr-credit-card" href="{{ '' }}" active="{{ '' }}">Receipts</x-sidebaritem>
                </x-sidebar>
                <main class="flex flex-col gap-4 w-full min-h-screen h-full overflow-x-hidden overflow-y-auto pt-20 pb-4 px-8 relative">
                    {{ $slot }}
                </main>
            </div>
        </div>

    </div>
</body>

</html>