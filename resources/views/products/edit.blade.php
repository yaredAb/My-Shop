@extends('layouts.app')

@section('content')

    <div class="form-container">
        <div class="form-wrapper">
            <h2>Edit Product</h2>
            <form action="{{route('products.update', $product)}}" method="POST">
                @csrf
                @method('PUT')
                <p>Name:</p>
                <input type="text" name="name" value="{{ $product->name }}" required>
                <p>Bought Price:</p>
                <input type="number" step="0.01" name="bought_price" value="{{ $product->bought_price }}" required>
                <p>Sale Price:</p>
                <input type="number" step="0.01" name="sale_price" value="{{$product->sale_price}}" required>
                <p>Quantity:</p>
                <input type="number" name="quantity" value="{{$product->quantity}}" required>
                <p>Stock Threshold:</p>
                <input type="number" name="stock_threshold" value="{{$product->stock_threshold}}" required>
                <p>Category:</p>
                <select name="category_id">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <p>
                    <label>
<<<<<<< HEAD
                        <input type="checkbox" name="has_expiry" id="has_expiry">
=======
                        <input type="checkbox" name="has_expiry" id="has_expiry" {{$product->has_expiry ? 'checked' : ''}}>
>>>>>>> responsivness
                        Has Expiry Date?
                    </label>
                </p>
                <div id="expiry_date_wrapper" style="display: none;">
                    <p>Expiry Date:</p>
<<<<<<< HEAD
                    <input type="date" name="expiry_date" id="expiry_date" value="{{ old('expiry_date') }}">
=======
                    <input type="date" name="expiry_date" id="expiry_date" value="{{ $product->expiry_date }}">
>>>>>>> responsivness
                </div>
                <button type="submit">Save</button>
            </form>
        </div>
    </div>

    @section('scripts')
        <script>
            document.getElementById('has_expiry').addEventListener('change', function () {
                const wrapper = document.getElementById('expiry_date_wrapper');
                wrapper.style.display = this.checked ? 'block' : 'none';
            })
        </script>
    @endsection
@endsection
@section('script')
    <script>
        document.getElementById('has_expiry').addEventListener('change', function () {
            const wrapper = document.getElementById('expiry_date_wrapper');
            wrapper.style.display = this.checked ? 'block' : 'none';
        })
    </script>
@endsection
