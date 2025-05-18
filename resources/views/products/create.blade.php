@extends('layouts.app')

@section('content')
        <div class="form-container">
            @if ($errors->any())
                <div class="error-messages">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li style="color: red">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-wrapper">
                <h2>Create Product</h2>
                <form action="{{route('products.store')}}" method="POST">
                    @csrf
                    <p>Name:</p>
                    <input type="text" name="name">
                    <p>Bought Price:</p>
                    <input type="number" step="0.01" name="bought_price">
                    <p>Sale Price:</p>
                    <input type="number" step="0.01" name="sale_price">
                    <p>Quantity:</p>
                    <input type="number" name="quantity">
                    <p>Stock Threshold:</p>
                    <input type="number" name="stock_threshold">
                    <p>Category:</p>
                    <select name="category_id">
                        <option value="">--select category--</option>
                        @foreach ($categories as $category)
                            <option value="{{$category->id}}">{{$category->name}}</option>
                        @endforeach
                    </select>
                    <p>
                        <label>
                            <input type="checkbox" name="has_expiry" id="has_expiry">
                            Has Expiry Date?
                        </label>
                    </p>
                    <div id="expiry_date_wrapper" style="display: none;">
                        <p>Expiry Date:</p>
                        <input type="date" name="expiry_date" id="expiry_date" value="{{ old('expiry_date') }}">
                    </div>
                    <button type="submit">Save</button>
                </form>
            </div>
        </div>
<<<<<<< HEAD

    @section('scripts')
=======
@endsection

@section('script')
>>>>>>> responsivness
        <script>
            document.getElementById('has_expiry').addEventListener('change', function () {
                const wrapper = document.getElementById('expiry_date_wrapper');
                wrapper.style.display = this.checked ? 'block' : 'none';
            })
        </script>
<<<<<<< HEAD
    @endsection
=======
>>>>>>> responsivness
@endsection
