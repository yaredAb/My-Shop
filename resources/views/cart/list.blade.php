@extends('layouts.app')
@php
$cart = session('cart', []);
@endphp

@section('content')
    <div class="items-container">
        <h1>Cart</h1>
        <hr>
        <div class="cart-body-wrapper">
            @if(count($cart) > 0)
                @foreach($cart as $id=>$item)
                    <div class="cart-body">
                        <p>{{$item['name']}}</p>
                        <div class="cart-qty">
                            <button class="update-cart" data-id="{{$id}}" data-action="decrease">-</button>
                            <span>{{$item['quantity']}}</span>
                            <button class="update-cart" data-id="{{$id}}" data-action="increase">+</button>
                            <span> x {{number_format($item['price'])}} = {{number_format($item['price'] * $item['quantity'])}}</span>
                        </div>
                    </div>
                @endforeach
                <hr>
                @php
                    $cart = session('cart', []);
                    $total = 0;
                    foreach ($cart as $item) {
                        $total += $item['price'] * $item['quantity'];
                    }
                @endphp

                <p class="total-cart">Total: {{number_format($total)}} Birr</p>
                <form action="{{route('checkout')}}" method="POST">
                    @csrf
                    <button type="submit" class="purchase">Purchase</button>
                </form>
                <hr>
                <p class="total-cart"></p>

            @else
                <p class="message">Your cart is empty.</p>
            @endif

        </div>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const updateButtons = document.querySelectorAll('.update-cart');

            updateButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.getAttribute('data-id')
                    const action = this.getAttribute('data-action')


                    fetch("{{route('cart.update')}}", {
                        method: 'POST',
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({id, action})
                    })
                        .then(response => response.json())
                        .then(data => {
                            if(data.success) {
                                location.reload();
                            }
                        })
                })
            })
        })
    </script>
@endsection
