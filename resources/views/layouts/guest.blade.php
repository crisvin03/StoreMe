<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-white antialiased bg-[#0A2540]">
    <div class="min-h-screen flex flex-col justify-center items-center pt-6 sm:pt-0">
        
   
        <div class="mb-4">
            <a href="/">
                <img src="{{ asset('images/storeme-logo.png') }}" alt="StoreMe Logo" class="w-40 h-auto">
            </a>
        </div>

        
        <div class="w-full sm:max-w-md px-6 py-8 bg-[#1B4965] text-white rounded-2xl shadow-lg">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
