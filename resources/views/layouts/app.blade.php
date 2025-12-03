<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Product Inventory Manager')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <a href="{{ route('products.index') }}" class="text-2xl font-bold text-blue-600">
                    📦 Product Inventory
                </a>
                <ul class="flex gap-6">
                    <li><a href="{{ route('products.index') }}" class="text-gray-600 hover:text-blue-600">Products</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer class="bg-gray-200 text-center py-4 mt-12">
        <p class="text-gray-600">&copy; 2025 Product Inventory Manager. All rights reserved.</p>
    </footer>
</body>
</html>
