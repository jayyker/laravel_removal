<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Inventory</title>
    
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <div id="root"></div>
    
    <script>
        // Pass data from Laravel to React
        window.laravelData = {
            products: @json($products),
            csrfToken: '{{ csrf_token() }}',
            apiRoutes: {
                store: '{{ route("products.store") }}',
                update: '{{ route("products.update", ":id") }}',
                destroy: '{{ route("products.destroy", ":id") }}'
            }
        };
        
        console.log('Laravel data:', window.laravelData);
    </script>
    
    <!-- React -->
    <script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    
    <!-- React Application -->
    <script type="text/babel">
        const { useState, useEffect } = React;
        
        function ProductInventoryApp() {
            const [products, setProducts] = useState([]);
            const [filteredProducts, setFilteredProducts] = useState([]);
            const [searchTerm, setSearchTerm] = useState('');
            const [showForm, setShowForm] = useState(false);
            const [editingProduct, setEditingProduct] = useState(null);
            const [formData, setFormData] = useState({
                name: '',
                sku: '',
                price: '',
                quantity: '',
                description: ''
            });
            const [loading, setLoading] = useState(false);
            const [message, setMessage] = useState({ type: '', text: '' });
            
            // Initialize products from Laravel
            useEffect(() => {
                const laravelData = window.laravelData;
                console.log('Initializing with:', laravelData);
                
                if (laravelData && laravelData.products) {
                    // Convert Laravel collection to array
                    let productArray = [];
                    
                    if (Array.isArray(laravelData.products)) {
                        productArray = laravelData.products;
                    } else if (laravelData.products.data) {
                        productArray = laravelData.products.data;
                    } else {
                        // Try to convert object to array
                        productArray = Object.values(laravelData.products);
                    }
                    
                    console.log('Loaded products:', productArray);
                    setProducts(productArray);
                    setFilteredProducts(productArray);
                }
            }, []);
            
            // Handle search
            const handleSearch = () => {
                if (!searchTerm.trim()) {
                    setFilteredProducts(products);
                } else {
                    const filtered = products.filter(product =>
                        product.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                        product.sku.toLowerCase().includes(searchTerm.toLowerCase())
                    );
                    setFilteredProducts(filtered);
                }
            };
            
            // Handle form input changes
            const handleInputChange = (e) => {
                const { name, value } = e.target;
                setFormData(prev => ({
                    ...prev,
                    [name]: value
                }));
            };
            
            // Handle edit
            const handleEdit = (product) => {
                setEditingProduct(product);
                setFormData({
                    name: product.name,
                    sku: product.sku,
                    price: product.price,
                    quantity: product.quantity,
                    description: product.description || ''
                });
                setShowForm(true);
            };
            
            // Handle delete
            const handleDelete = async (productId) => {
                if (!confirm('Are you sure you want to delete this product?')) return;
                
                try {
                    setLoading(true);
                    
                    const response = await fetch(`/products/${productId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': window.laravelData.csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Update local state
                        const updatedProducts = products.filter(p => p.id !== productId);
                        setProducts(updatedProducts);
                        setFilteredProducts(updatedProducts);
                        
                        setMessage({ type: 'success', text: result.message });
                        setTimeout(() => setMessage({ type: '', text: '' }), 3000);
                    }
                } catch (error) {
                    setMessage({ type: 'error', text: 'Error deleting product' });
                } finally {
                    setLoading(false);
                }
            };
            
            // Handle form submit (create or update)
            const handleSubmit = async (e) => {
                e.preventDefault();
                setLoading(true);
                
                try {
                    const url = editingProduct 
                        ? `/products/${editingProduct.id}` 
                        : '/products';
                    
                    const method = editingProduct ? 'PUT' : 'POST';
                    
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'X-CSRF-TOKEN': window.laravelData.csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Refresh products (in a real app, you might update the specific product)
                        // For simplicity, we'll reload the page
                        window.location.reload();
                    } else {
                        setMessage({ type: 'error', text: result.message || 'Error saving product' });
                    }
                } catch (error) {
                    setMessage({ type: 'error', text: 'Network error' });
                } finally {
                    setLoading(false);
                }
            };
            
            // Reset form
            const resetForm = () => {
                setFormData({
                    name: '',
                    sku: '',
                    price: '',
                    quantity: '',
                    description: ''
                });
                setEditingProduct(null);
                setShowForm(false);
            };
            
            // Format price
            const formatPrice = (price) => {
                const num = parseFloat(price);
                if (isNaN(num)) return '$0.00';
                return num.toLocaleString('en-US', {
                    style: 'currency',
                    currency: 'USD',
                    minimumFractionDigits: 2
                });
            };
            
            return (
                <div className="min-h-screen bg-gray-100">
                    {/* Header */}
                    <header className="bg-white shadow">
                        <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            <h1 className="text-3xl font-bold text-gray-900">Product Inventory</h1>
                        </div>
                    </header>
                    
                    <main className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                        {/* Messages */}
                        {message.text && (
                            <div className={`mb-4 p-4 rounded-md ${message.type === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200'}`}>
                                {message.text}
                            </div>
                        )}
                        
                        {/* Search Bar */}
                        <div className="mb-6 bg-white p-4 rounded-lg shadow">
                            <div className="flex items-center">
                                <div className="relative flex-grow">
                                    <input
                                        type="text"
                                        placeholder="Search by product name or SKU..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        onKeyPress={(e) => e.key === 'Enter' && handleSearch()}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-l focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                                <button
                                    onClick={handleSearch}
                                    className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-r font-medium"
                                >
                                    <i className="fas fa-search mr-2"></i>Search
                                </button>
                                <button
                                    onClick={() => setShowForm(true)}
                                    className="ml-4 bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded font-medium"
                                >
                                    <i className="fas fa-plus mr-2"></i>Add New Product
                                </button>
                            </div>
                        </div>
                        
                        {/* Product Form Modal */}
                        {showForm && (
                            <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                                <div className="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                                    <div className="flex justify-between items-center mb-4">
                                        <h3 className="text-lg font-medium">
                                            {editingProduct ? 'Edit Product' : 'Add New Product'}
                                        </h3>
                                        <button onClick={resetForm} className="text-gray-400 hover:text-gray-600">
                                            <i className="fas fa-times"></i>
                                        </button>
                                    </div>
                                    
                                    <form onSubmit={handleSubmit}>
                                        <div className="space-y-4">
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700">Product Name *</label>
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
                                                <label className="block text-sm font-medium text-gray-700">SKU *</label>
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
                                                <label className="block text-sm font-medium text-gray-700">Price *</label>
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
                                                <label className="block text-sm font-medium text-gray-700">Quantity *</label>
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
                                                <label className="block text-sm font-medium text-gray-700">Description</label>
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
                                                onClick={resetForm}
                                                className="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                                                disabled={loading}
                                            >
                                                Cancel
                                            </button>
                                            <button
                                                type="submit"
                                                disabled={loading}
                                                className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                                            >
                                                {loading ? 'Saving...' : (editingProduct ? 'Update' : 'Create')}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        )}
                        
                        {/* Products Table */}
                        <div className="bg-white shadow overflow-hidden sm:rounded-lg">
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NAME</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PRICE</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">QUANTITY</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DESCRIPTION</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ACTIONS</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {filteredProducts.length === 0 ? (
                                            <tr>
                                                <td colSpan="7" className="px-6 py-4 text-center text-gray-500">
                                                    No products found. {searchTerm && 'Try a different search.'}
                                                </td>
                                            </tr>
                                        ) : (
                                            filteredProducts.map((product) => (
                                                <tr key={product.id} className="hover:bg-gray-50">
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{product.id}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{product.name}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{product.sku}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{formatPrice(product.price)}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{product.quantity}</td>
                                                    <td className="px-6 py-4 text-sm text-gray-600 max-w-xs">{product.description}</td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <button
                                                            onClick={() => handleEdit(product)}
                                                            className="text-blue-600 hover:text-blue-900 mr-3"
                                                        >
                                                            <i className="fas fa-edit mr-1"></i>Edit
                                                        </button>
                                                        <button
                                                            onClick={() => handleDelete(product.id)}
                                                            className="text-red-600 hover:text-red-900"
                                                        >
                                                            <i className="fas fa-trash mr-1"></i>Delete
                                                        </button>
                                                    </td>
                                                </tr>
                                            ))
                                        )}
                                    </tbody>
                                </table>
                            </div>
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
        
        // Render the React app
        const root = ReactDOM.createRoot(document.getElementById('root'));
        root.render(<ProductInventoryApp />);
    </script>
</body>
</html>
