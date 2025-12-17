<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ProjectMan') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    </head>
    
    <body class="font-sans antialiased bg-light">
        <div class="bg-light">
            
            @include('layouts.navigation')

            @isset($header)
            <header class="bg-white shadow-sm border-bottom py-3">
                <div class="container">
                    {{ $header }}
                </div>
            </header>
            @endisset

            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>