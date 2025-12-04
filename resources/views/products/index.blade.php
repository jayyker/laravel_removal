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

    <!-- Products Table -->
    @if($products->count())
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse border border-gray-300">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="border border-gray-300 px-4 py-2 text-left">ID</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Name</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">SKU</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Price</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Quantity</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Description</th>
                        <th class="border border-gray-300 px-4 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr class="hover:bg-gray-100">
                            <td class="border border-gray-300 px-4 py-2">{{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ $product->name }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ $product->sku }}</td>
                            <td class="border border-gray-300 px-4 py-2">${{ number_format($product->price, 2) }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ $product->quantity }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ Str::limit($product->description, 30) }}</td>
                            <td class="border border-gray-300 px-4 py-2 text-center">
                                <a href="{{ route('products.edit', $product) }}" class="text-blue-500 hover:text-blue-700 mr-2">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @else
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
            No products found. <a href="{{ route('products.create') }}" class="font-bold">Create one now</a>.
        </div>
    @endif
</div>
@endsection
