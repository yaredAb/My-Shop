<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{csrf_token()}}">
    @vite('resources/css/app.css')
    <title>Document</title>
    <link rel="stylesheet" href="{{asset('css/style.css')}}">
</head>

<style>
    :root {
        --bg: {{ $background_color }};
        --primary: {{ $primary_color }};
        --secondary: {{ $secondary_color }};
        --top: {{ $button_color }};
    }
</style>
<body>

@yield('childContent')
@yield('scripts')
</body>
</html>
