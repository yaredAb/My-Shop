@extends('layouts.clean')

@section('childContent')
    <div class="wrapper">
<<<<<<< HEAD
        @include('components.navigation')
=======
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
>>>>>>> 56e3740c8eae31e31850f3fdaf96f1f11f2d4aa0

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

    @yield('script')
@endsection

@section('scripts')
    <script>

        const toggle_button = document.getElementById('toggle-menu')
        const menu_container = document.querySelector('.nav-links')

        let isMenuOpen = false
        toggle_button.addEventListener('click', () => {
            isMenuOpen = !isMenuOpen

            menu_container.classList.toggle('open', isMenuOpen)
            toggle_button.src = isMenuOpen ? "{{asset('img/cancel2.png')}}" : "{{asset('img/menu2.png')}}";
        })


        const toggle_cart = document.getElementById('toggleCart')
        const cart_container = document.querySelector('.mobile-cart')

        let isCartOpen = false
        toggle_cart.addEventListener('click', () => {
            isCartOpen = !isCartOpen

            cart_container.classList.toggle('open')
            toggle_cart.style.transform = isCartOpen ? 'rotate(0deg)' : 'rotate(180deg)'
        })

    </script>
@endsection


