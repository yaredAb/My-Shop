
@php
    $user = Illuminate\Support\Facades\Auth::user();
    $user_role = $user->privilage;

    $background_color = \App\Models\Setting::getValue('background_color');
    $primary_color = \App\Models\Setting::getValue('primary_color');
    $secondary_color = \App\Models\Setting::getValue('secondary_color');
    $button_color = \App\Models\Setting::getValue('button_color');
    $site_title = \App\Models\Setting::getValue('site_title');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <div class="wrapper">
        <nav>
            <a href="/" class="logo">{{$site_title}}</a>
            <ul class="nav-links">
                <li><a href="{{route('products.index')}}">Shop</a></li>
                <li><a href="{{route('products.create')}}">Add Product</a></li>
                <li><a href="{{route('categories.create')}}">Add Category</a></li>
                <li><a href="{{route('products.list')}}">Products</a></li>
                @if ($user_role === 'admin')
                    <li><a href="{{route('categories.index')}}">Categories</a></li>
                    <li><a href="{{route('userList')}}">Users</a></li>
                    <li><a href="{{route('sales.report')}}">Report</a></li>
                    <li><a href="{{route('sale.index')}}">All Orders</a></li>
                    <li><a href="{{route('settings')}}">Settings</a></li>
                @endif
            </ul>
        </nav>

        @php
            use App\Models\Setting;
            use Carbon\Carbon;

            $dailyTime = Setting::getValue('daily_hour');
            if ($dailyTime && Carbon::hasFormat($dailyTime, 'H:i')) {
                $scheduled_time = Carbon::createFromFormat('H:i', $dailyTime);
            } else {
                $scheduled_time = Carbon::createFromFormat('H:i', "00:00");
            }

            $currentTime = Carbon::now()->format('H:i');

        @endphp
        @if ($dailyTime <= $currentTime && $user_role === 'admin')
            <a href="{{route('dailyReport')}}" class="sendDailyReport">Export Daily Report</a>
        @endif

        @yield('content')
    </div>
    @yield('scripts')
</body>
</html>
