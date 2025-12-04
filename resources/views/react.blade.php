<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Inventory</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- React 18 -->
    <script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    
    <!-- Babel for JSX -->
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
</head>
<body>
    <div id="root"></div>

    <script type="text/babel">
        const { useState, useEffect } = React;

        function ProductInventoryApp() {
            const [products, setProducts] = useState([]);
            const [loading, setLoading] = useState(true);
            const [error, setError] = useState(null);
            const [searchTerm, setSearchTerm] = useState('');
            const [formData, setFormData] = useState({
                name: '',
                sku: '',
                price: '',
                quantity: '',
                description: ''
            });
            const [editingId, setEditingId] = useState(null);
            const [showForm, setShowForm] = useState(false);

            // Fetch products from Laravel API
            const fetchProducts = async () => {
                setLoading(true);
                try {
                    // Try different API endpoints
                    const endpoints = [
                        '/api/products',
                        'http://localhost:8000/api/products',
                        'http://127.0.0.1:8000/api/products'
                    ];
                    
                    let response = null;
                    for (const endpoint of endpoints) {
                        try {
                            const res = await fetch(endpoint);
                            if (res.ok) {
                                response = res;
                                console.log('Using endpoint:', endpoint);
                                break;
                            }
                        } catch (e) {
                            console.log('Failed endpoint:', endpoint, e.message);
                        }
                    }
                    
                    if (!response) {
                        throw new Error('Cannot connect to Laravel API');
                    }
                    
                    const data = await response.json();
                    console.log('API Response:', data);
                    
                    if (data.success && data.data) {
                        setProducts(data.data);
                    } else if (Array.isArray(data)) {
                        setProducts(data);
                    } else {
                        setProducts([]);
                    }
                    
                    setError(null);
                } catch (err) {
                    console.error('Fetch error:', err);
                    setError('Cannot connect to server. Make sure Laravel is running.');
                    setProducts([]);
                } finally {
                    setLoading(false);
                }
            };

            // Load products on component mount
            useEffect(() => {
                fetchProducts();
            }, []);

            // Handle form input changes
            const handleInputChange = (e) => {
                const { name, value } = e.target;
                setFormData(prev => ({
                    ...prev,
                    [name]: value
                }));
            };

            // Handle form submit (Create/Update)
            const handleSubmit = async (e) => {
                e.preventDefault();
                
                const url = editingId 
                    ? `/api/products/${editingId}` 
                    : '/api/products';
                
                const method = editingId ? 'PUT' : 'POST';
                
                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify(formData)
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Refresh the products list
                        await fetchProducts();
                        
                        // Reset form
                        setFormData({
                            name: '',
                            sku: '',
                            price: '',
                            quantity: '',
                            description: ''
                        });
                        setEditingId(null);
                        setShowForm(false);
                        
                        alert(data.message || 'Success!');
                    } else {
                        alert('Error: ' + (data.message || 'Operation failed'));
                    }
                } catch (error) {
                    alert('Network error: ' + error.message);
                }
            };

            // Handle edit
            const handleEdit = (product) => {
                setFormData({
                    name: product.name,
                    sku: product.sku,
                    price: product.price.toString(),
                    quantity: product.quantity.toString(),
                    description: product.description || ''
                });
                setEditingId(product.id);
                setShowForm(true);
            };

            // Handle delete
            const handleDelete = async (id) => {
                if (!confirm('Are you sure you want to delete this product?')) return;
                
                try {
                    console.log('Deleting product ID:', id);
                    
                    const response = await fetch(`/api/products/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    });
                    
                    const data = await response.json();
                    console.log('Delete response:', data);
                    
                    if (data.success) {
                        console.log('Delete successful, refreshing products...');
                        await fetchProducts();
                        alert('Product deleted and IDs renumbered!');
                    } else {
                        console.error('Delete failed:', data.message);
                        alert('Error: ' + (data.message || 'Delete failed'));
                    }
                } catch (error) {
                    console.error('Delete error:', error);
                    alert('Network error: ' + error.message);
                }
            };

            // Filter products based on search term
            const filteredProducts = products.filter(product => {
                if (!searchTerm.trim()) return true;
                
                const searchLower = searchTerm.toLowerCase();
                return (
                    product.name.toLowerCase().includes(searchLower) ||
                    product.sku.toLowerCase().includes(searchLower) ||
                    product.description?.toLowerCase().includes(searchLower)
                );
            });

            // Format price
            const formatPrice = (price) => {
                const num = parseFloat(price);
                return num.toLocaleString('en-US', {
                    style: 'currency',
                    currency: 'USD',
                    minimumFractionDigits: 2
                });
            };

            return (
                <div className="min-h-screen bg-gray-50">
                    {/* Header */}
                    <header className="bg-white shadow">
                        <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            <h1 className="text-3xl font-bold text-gray-900">Product Inventory</h1>
                        </div>
                    </header>

                    <main className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                        {/* Connection Status */}
                        {error && (
                            <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                                <div className="flex">
                                    <div className="flex-shrink-0">
                                        <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                                        </svg>
                                    </div>
                                    <div className="ml-3">
                                        <h3 className="text-sm font-medium text-red-800">Connection Error</h3>
                                        <div className="mt-2 text-sm text-red-700">
                                            <p>{error}</p>
                                            <p className="mt-2">
                                                Make sure Laravel server is running: <code>php artisan serve</code>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Search and Add Section */}
                        <div className="mb-6 bg-white p-4 rounded-lg shadow">
                            <div className="flex flex-col sm:flex-row sm:items-center space-y-4 sm:space-y-0 sm:space-x-4">
                                <div className="flex-grow">
                                    <input
                                        type="text"
                                        placeholder="Search by product name or SKU..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                                <button
                                    onClick={() => {
                                        setFormData({
                                            name: '',
                                            sku: '',
                                            price: '',
                                            quantity: '',
                                            description: ''
                                        });
                                        setEditingId(null);
                                        setShowForm(true);
                                    }}
                                    className="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded font-medium"
                                >
                                    Add New Product
                                </button>
                            </div>
                        </div>

                        {/* Product Form Modal */}
                        {showForm && (
                            <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
                                <div className="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                                    <h3 className="text-lg font-medium mb-4">
                                        {editingId ? 'Edit Product' : 'Add New Product'}
                                    </h3>
                                    
                                    <form onSubmit={handleSubmit}>
                                        <div className="space-y-4">
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">
                                                    Product Name *
                                                </label>
                                                <input
                                                    type="text"
                                                    name="name"
                                                    value={formData.name}
                                                    onChange={handleInputChange}
                                                    required
                                                    className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                                />
                                            </div>
                                            
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">
                                                    SKU *
                                                </label>
                                                <input
                                                    type="text"
                                                    name="sku"
                                                    value={formData.sku}
                                                    onChange={handleInputChange}
                                                    required
                                                    className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                                />
                                            </div>
                                            
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">
                                                    Price *
                                                </label>
                                                <input
                                                    type="number"
                                                    name="price"
                                                    step="0.01"
                                                    min="0"
                                                    value={formData.price}
                                                    onChange={handleInputChange}
                                                    required
                                                    className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                                />
                                            </div>
                                            
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">
                                                    Quantity *
                                                </label>
                                                <input
                                                    type="number"
                                                    name="quantity"
                                                    min="0"
                                                    value={formData.quantity}
                                                    onChange={handleInputChange}
                                                    required
                                                    className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                                />
                                            </div>
                                            
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">
                                                    Description
                                                </label>
                                                <textarea
                                                    name="description"
                                                    value={formData.description}
                                                    onChange={handleInputChange}
                                                    rows="3"
                                                    className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                                />
                                            </div>
                                        </div>
                                        
                                        <div className="mt-6 flex justify-end space-x-3">
                                            <button
                                                type="button"
                                                onClick={() => setShowForm(false)}
                                                className="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                                            >
                                                Cancel
                                            </button>
                                            <button
                                                type="submit"
                                                className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                            >
                                                {editingId ? 'Update' : 'Create'}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        )}

                        {/* Products Table */}
                        <div className="bg-white shadow overflow-hidden sm:rounded-lg">
                            {loading ? (
                                <div className="p-8 text-center">
                                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                                    <p className="mt-4 text-gray-600">Loading products...</p>
                                </div>
                            ) : filteredProducts.length === 0 ? (
                                <div className="p-8 text-center">
                                    <div className="text-gray-400 mb-4">
                                        <svg className="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                    </div>
                                    <h3 className="text-lg font-medium text-gray-900 mb-2">
                                        {searchTerm ? 'No matching products found' : 'No products in database'}
                                    </h3>
                                    <p className="text-gray-600 mb-4">
                                        {searchTerm 
                                            ? `Try a different search term` 
                                            : 'Click "Add New Product" to create your first product'}
                                    </p>
                                    {!searchTerm && (
                                        <button
                                            onClick={() => setShowForm(true)}
                                            className="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded font-medium"
                                        >
                                            Add Your First Product
                                        </button>
                                    )}
                                </div>
                            ) : (
                                <div className="overflow-x-auto">
                                    <table className="w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-100">
                                            <tr>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NAME</th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PRICE</th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">QUANTITY</th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DESCRIPTION</th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ACTIONS</th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {loading ? (
                                                <tr>
                                                    <td colSpan="7" className="text-center py-4">
                                                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                                                        <p className="mt-2 text-gray-600">Loading products...</p>
                                                    </td>
                                                </tr>
                                            ) : filteredProducts.length === 0 ? (
                                                <tr>
                                                    <td colSpan="7" className="text-center py-4">
                                                        <div className="text-gray-400 mb-4">
                                                            <svg className="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                                            </svg>
                                                        </div>
                                                        <h3 className="text-lg font-medium text-gray-900 mb-2">
                                                            {searchTerm ? 'No matching products found' : 'No products in database'}
                                                        </h3>
                                                        <p className="text-gray-600 mb-4">
                                                            {searchTerm 
                                                                ? 'Try a different search term' 
                                                                : 'Click "Add New Product" to create your first product'}
                                                        </p>
                                                        {!searchTerm && (
                                                            <button
                                                                onClick={() => setShowForm(true)}
                                                                className="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded font-medium"
                                                            >
                                                                Add Your First Product
                                                            </button>
                                                        )}
                                                    </td>
                                                </tr>
                                            ) : (
                                                filteredProducts.map((product, index) => (
                                                    <tr key={product.id} className="hover:bg-gray-50">
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold text-lg bg-blue-100">
                                                            ID: {product.id || 'N/A'}
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                            {product.name}
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            {product.sku}
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            {formatPrice(product.price)}
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            {product.quantity}
                                                        </td>
                                                        <td className="px-6 py-4 text-sm text-gray-600">
                                                            {product.description || '-'}
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                            <button
                                                                onClick={() => handleEdit(product)}
                                                                className="text-blue-600 hover:text-blue-900 mr-3"
                                                            >
                                                                Edit
                                                            </button>
                                                            <button
                                                                onClick={() => handleDelete(product.id)}
                                                                className="text-red-600 hover:text-red-900"
                                                            >
                                                                Delete
                                                            </button>
                                                        </td>
                                                    </tr>
                                                ))
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </div>
                    </main>

                    {/* Footer */}
                    <footer className="bg-white mt-8 py-4 border-t">
                        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                            <p className="text-center text-gray-500 text-sm">
                                © 2025 Product Inventory Manager. All rights reserved.
                            </p>
                        </div>
                    </footer>
                </div>
            );
        }

        // Render the app
        const root = ReactDOM.createRoot(document.getElementById('root'));
        root.render(<ProductInventoryApp />);
    </script>
</body>
</html>
