import React, { useState, useEffect } from 'react';
import './ProductInventory.scss';

const ProductInventory = () => {
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
            const response = await fetch('/api/products');
            
            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Products from database:', data);
            
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
            setError('Cannot connect to server. Make sure Laravel API is running.');
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

    // Handle form submit
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
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                await fetchProducts();
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
            const response = await fetch(`/api/products/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                await fetchProducts();
                alert('Product deleted successfully!');
            } else {
                alert('Error: ' + (data.message || 'Delete failed'));
            }
        } catch (error) {
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
        <div className="product-inventory">
            <header className="product-inventory__header">
                <h1 className="product-inventory__title">Product Inventory</h1>
                <p className="product-inventory__subtitle">Manage your products efficiently</p>
            </header>

            <main className="product-inventory__main">
                {/* Search and Add */}
                <section className="product-inventory__controls">
                    <div className="search-bar">
                        <input
                            type="text"
                            placeholder="Search by product name or SKU..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            className="search-bar__input"
                        />
                        <button className="search-bar__button">Search</button>
                    </div>
                    <button
                        onClick={() => {
                            setFormData({ name: '', sku: '', price: '', quantity: '', description: '' });
                            setEditingId(null);
                            setShowForm(true);
                        }}
                        className="btn btn--primary"
                    >
                        Add New Product
                    </button>
                </section>

                {/* Products Table */}
                <section className="product-inventory__table">
                    {loading ? (
                        <div className="loading">
                            <div className="loading__spinner"></div>
                            <p>Loading products...</p>
                        </div>
                    ) : filteredProducts.length === 0 ? (
                        <div className="empty-state">
                            <div className="empty-state__icon">📦</div>
                            <h3>No products found</h3>
                            <p>{searchTerm ? 'Try a different search term' : 'Click "Add New Product" to create your first product'}</p>
                            {!searchTerm && (
                                <button
                                    onClick={() => setShowForm(true)}
                                    className="btn btn--primary"
                                >
                                    Add Your First Product
                                </button>
                            )}
                        </div>
                    ) : (
                        <table className="products-table">
                            <thead className="products-table__head">
                                <tr>
                                    <th>ID</th>
                                    <th>NAME</th>
                                    <th>SKU</th>
                                    <th>PRICE</th>
                                    <th>QUANTITY</th>
                                    <th>DESCRIPTION</th>
                                    <th>ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody className="products-table__body">
                                {filteredProducts.map((product, index) => (
                                    <tr key={product.id} className="products-table__row">
                                        <td className="products-table__cell products-table__cell--id">
                                            {product.id}
                                        </td>
                                        <td className="products-table__cell products-table__cell--name">
                                            {product.name}
                                        </td>
                                        <td className="products-table__cell products-table__cell--sku">
                                            {product.sku}
                                        </td>
                                        <td className="products-table__cell products-table__cell--price">
                                            {formatPrice(product.price)}
                                        </td>
                                        <td className="products-table__cell products-table__cell--quantity">
                                            {product.quantity}
                                        </td>
                                        <td className="products-table__cell products-table__cell--description">
                                            {product.description || '-'}
                                        </td>
                                        <td className="products-table__cell products-table__cell--actions">
                                            <button
                                                onClick={() => handleEdit(product)}
                                                className="btn btn--secondary btn--small"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                onClick={() => handleDelete(product.id)}
                                                className="btn btn--danger btn--small"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )}
                </section>
            </main>

            {/* Add/Edit Form Modal */}
            {showForm && (
                <div className="modal">
                    <div className="modal__content">
                        <div className="modal__header">
                            <h2 className="modal__title">
                                {editingId ? 'Edit Product' : 'Add New Product'}
                            </h2>
                            <button
                                onClick={() => setShowForm(false)}
                                className="modal__close"
                            >
                                ×
                            </button>
                        </div>
                        <form onSubmit={handleSubmit} className="modal__form">
                            <div className="form-group">
                                <label className="form-group__label">Product Name *</label>
                                <input
                                    type="text"
                                    name="name"
                                    value={formData.name}
                                    onChange={handleInputChange}
                                    required
                                    className="form-group__input"
                                />
                            </div>
                            <div className="form-group">
                                <label className="form-group__label">SKU *</label>
                                <input
                                    type="text"
                                    name="sku"
                                    value={formData.sku}
                                    onChange={handleInputChange}
                                    required
                                    className="form-group__input"
                                />
                            </div>
                            <div className="form-group">
                                <label className="form-group__label">Price *</label>
                                <input
                                    type="number"
                                    name="price"
                                    step="0.01"
                                    value={formData.price}
                                    onChange={handleInputChange}
                                    required
                                    className="form-group__input"
                                />
                            </div>
                            <div className="form-group">
                                <label className="form-group__label">Quantity *</label>
                                <input
                                    type="number"
                                    name="quantity"
                                    value={formData.quantity}
                                    onChange={handleInputChange}
                                    required
                                    className="form-group__input"
                                />
                            </div>
                            <div className="form-group">
                                <label className="form-group__label">Description</label>
                                <textarea
                                    name="description"
                                    value={formData.description}
                                    onChange={handleInputChange}
                                    className="form-group__textarea"
                                    rows="3"
                                />
                            </div>
                            <div className="modal__actions">
                                <button
                                    type="button"
                                    onClick={() => setShowForm(false)}
                                    className="btn btn--secondary"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    className="btn btn--primary"
                                >
                                    {editingId ? 'Update' : 'Create'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            <footer className="product-inventory__footer">
                <p>&copy; 2025 Product Inventory Manager. All rights reserved.</p>
            </footer>
        </div>
    );
};

export default ProductInventory;
