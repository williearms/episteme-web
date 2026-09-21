<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Episteme — Knowledge into capability.' }}</title>
    <meta name="description" content="{{ $description ?? 'Episteme is an education marketplace for courses, workshops, webinars, and mentoring designed to turn learning into practical capability.' }}">

    {{-- Fonts loaded directly via CDN, matching the Stitch reference. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=JetBrains+Mono:wght@400;500;600&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{ $head ?? '' }}
</head>
<body class="bg-background text-text antialiased font-sans text-body-md selection:bg-accent-soft selection:text-primary">
    <x-layout.navbar />

    <main>
        {{ $slot }}
    </main>

    <x-layout.footer />
</body>
</html>
