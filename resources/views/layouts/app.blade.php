<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'SegHIS Patient Portal' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-sans antialiased text-slate-800">
    <div class="flex min-h-screen">
        @include('layouts.sidebar')
        <div class="flex-1 flex flex-col">
            @include('layouts.topbar')
            <main class="flex-1 p-6">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>

</html>
