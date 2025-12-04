@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Product Inventory</h1>
        <a href="{{ route('products.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Add New Product
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Search Form -->
    <form method="GET" action="{{ route('products.index') }}" class="mb-6">
        <div class="flex gap-2">
            <input 
                type="text" 
                name="search" 
                placeholder="Search by product name..." 
                value="{{ request('search') }}"
                class="flex-1 px-4 py-2 border rounded"
            >
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                Search
            </button>
            @if(request('search'))
                <a href="{{ route('products.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Clear
                </a>
            @endif
        </div>
    </form>

    <div id="example"></div>

    <script>
        window.__PRODUCTS__ = @json($products);
    </script>

</div>
@endsection
