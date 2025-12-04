import React from 'react';
import ReactDOM from 'react-dom/client';

function ProductTable({ initialData }) {
    console.log('React received:', initialData);
    // Handle both array and paginated data structures
    let products = [];
    let offset = 0;
    
    if (Array.isArray(initialData)) {
        // Direct array of products
        products = initialData;
    } else if (initialData && Array.isArray(initialData.data)) {
        // Paginated format
        products = initialData.data;
        offset = initialData.from ? initialData.from - 1 : 0;
    } else if (initialData && typeof initialData === 'object') {
        // Try to find products in the object
        const values = Object.values(initialData);
        products = values.find(v => Array.isArray(v)) || [];
    }
    
    console.log('Products array:', products);

    if (!products.length) {
        return (
            <div className="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                No products found.
            </div>
        );
    }

    return (
        <div className="container mx-auto px-4 py-8">
            {/* Header */}
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-gray-800 mb-2">Product Inventory</h1>
                <h2 className="text-xl text-gray-600 mb-4">Product Inventory</h2>
                
                {/* Success message placeholder - you can make this dynamic */}
                <div className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    Product deleted successfully.
                </div>
                
                {/* Search bar */}
                <div className="flex items-center mb-6">
                    <input
                        type="text"
                        placeholder="Search by product name..."
                        className="w-full px-4 py-2 border border-gray-300 rounded-l focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <button className="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-r">
                        Search
                    </button>
                </div>
            </div>

            {/* Product Table */}
            <div className="overflow-x-auto bg-white rounded-lg shadow">
                <table className="min-w-full border-collapse">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">ID</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Name</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">SKU</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Price</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Quantity</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Description</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-200">
                        {products.map((product, index) => (
                            <tr key={product.id} className="hover:bg-gray-50">
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{offset + index + 1}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{product.name}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{product.sku}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
                                    ${parseFloat(product.price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{product.quantity}</td>
                                <td className="px-6 py-4 text-sm text-gray-500">{product.description}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">
                                    <div className="flex space-x-2">
                                        <button className="text-blue-600 hover:text-blue-900 font-medium">
                                            Edit
                                        </button>
                                        <span className="text-gray-300">|</span>
                                        <button className="text-red-600 hover:text-red-900 font-medium">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {/* Add New Product Button */}
            <div className="mt-8">
                <button className="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-medium">
                    Add New Product
                </button>
            </div>

            {/* Footer */}
            <div className="mt-12 pt-6 border-t border-gray-200 text-center text-gray-500 text-sm">
                © 2025 Product Inventory Manager. All rights reserved.
            </div>
        </div>
    );
}

export default ProductTable;

// Mount the React component
const exampleElement = document.getElementById('example');

if (exampleElement) {
    const mountComponent = () => {
        const initialData = window.__PRODUCTS__ || null;
        console.log('React received from window:', initialData);

        const root = ReactDOM.createRoot(exampleElement);
        root.render(
            <React.StrictMode>
                <ProductTable initialData={initialData} />
            </React.StrictMode>
        );
    };

    if (window.__PRODUCTS__) {
        mountComponent();
    } else {
        setTimeout(mountComponent, 200);
    }
}
