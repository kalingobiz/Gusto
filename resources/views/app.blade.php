<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Gusto Prime — Enterprise-grade restaurant POS, kitchen display, and operations management system.">
        <meta name="theme-color" content="#f97316">
        <meta name="application-name" content="Gusto Prime">

        <!-- Open Graph -->
        <meta property="og:title" content="Gusto Prime">
        <meta property="og:description" content="Enterprise-grade restaurant POS and kitchen management system.">
        <meta property="og:type" content="website">
        <meta property="og:image" content="/mylogo.png">

        <!-- PWA / Safari -->
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="Gusto Prime">

        <link rel="icon" type="image/x-icon" href="/logo.ico">

        <title inertia>{{ config('app.name', 'Gusto') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
