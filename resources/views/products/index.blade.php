@extends('layouts.app')

@section('content')
    <div class="total-sale">
        <h2>Today's Sale: <span>{{number_format($todaySale)}} Birr</span></h2>
        <p>{{now()->format('M d, Y')}}</p>
    </div>

    <div class="filtering-section">
        <form action="{{route('products.index')}}" method="GET" class="search-form">
            <input type="search" name="product_name" placeholder="Search Product....">
        </form>

        <form action="{{route('products.index')}}" method="GET" class="categories">
            <input type="hidden" name="product_name" value="{{request('product_name')}}">
            <button type="submit" name="category_id" value="">All</button>
            @foreach ($categories as $category)
                <button name="category_id" value="{{$category->id}}">{{$category->name}}</button>
            @endforeach
        </form>
    </div>

    <div class="product-section">
        <div class="products-wrapper">
            <div class="products-row">
                @foreach ($products as $product)
                    <div class="product">
                        @if ($product->category && $product->category->image)
                            <img src="{{asset('storage/'. $product->category->image)}}" alt="{{$product->name}}">
                        @else
                            <div class="no-image">
                                <h3>{{$product->name}}</h3>
                            </div>
                        @endif

                        <p>{{$product->name}}</p>
                        <div class="stock-quantity">
                            <span><strong>QTY: </strong>{{$product->quantity}}</span>
                            @if ($product->quantity <= $product->stock_threshold)
                                <span class="low-stock">Low Stock!</span>
                            @endif
                        </div>
                        <span class="price-section">{{number_format($product->sale_price)}} Birr</span>
                        <form action="{{route('cart.add', $product->id)}}" method="POST">
                            @csrf
                            <button type="submit">Add to cart</button>
                        </form>
                @endforeach
                @php
                    $cart = session('cart', []);
                @endphp

                @if (count($cart) > 0)
                    @foreach ($cart as $id=>$item)
                        <div class="cart-product">
                            <p class="cart-name">{{$item['name']}}</p>
                            <div class="price-section">

                                    <button class="update-cart" data-id="{{$id}}" data-action="decrease">-</button>
                                    <span>{{$item['quantity']}}</span>
                                    <button class="update-cart" data-id="{{$id}}" data-action="increase">+</button>
                                    <span> x {{number_format($item['price'])}} = {{number_format($item['price'] * $item['quantity'])}}</span>

                            </div>
                        </div>
                    @endforeach

                    @php

                        $total = 0;
                        foreach ($cart as $item) {
                            $total += $item['price'] * $item['quantity'];
                        }
                    @endphp
                    <div class="cart-total">
                        <hr>
                        <p class="total-cart">Total: {{number_format($total)}} Birr</p>
                    </div>

                    <form action="{{route('checkout')}}" method="POST">
                        @csrf
                        <button type="submit" class="purchase-btn">Purchase</button>
                    </form>

                    @else
                        <p>No product in the cart</p>
                @endif
            </div>
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

