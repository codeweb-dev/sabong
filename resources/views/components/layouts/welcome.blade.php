<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark scroll-smooth">

<head>
    @include('partials.head')
</head>

<body>
    {{ $slot }}
    @fluxScripts

    <x-toaster-hub />
</body>

</html>
