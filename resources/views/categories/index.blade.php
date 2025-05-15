@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="cat-header">
            <h2>All Categories</h2>
            <a href="{{ route('categories.create') }}" class="button">Add New Category</a>
        </div>

        <div class="cat-wrapper">
            @foreach ($categories as $category)
                <div class="category-card">
                    @if ($category->image)
                        <img src="{{asset('storage/'. $category->image)}}" alt="{{$category->name}}">
                    @else
                        <div class="no-image-2">
                            <h3>{{$category->name}}</h3>
                        </div>
                    @endif
                    <h3>{{ $category->name }}</h3>
                    <div class="cat-action">
                        <a href="{{route('categories.edit', $category)}}" class="editBtn"> Edit </a>

                        <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?')">
                            @csrf
                            @method('DELETE')
                            <button class="deleteBtn">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
