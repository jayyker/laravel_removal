import React from 'react';
import ReactDOM from 'react-dom/client';

function Example({ initialData }) {
    const products = initialData && Array.isArray(initialData.data) ? initialData.data : [];
    const offset = initialData && typeof initialData.from === 'number' ? initialData.from - 1 : 0;

    if (!products.length) {
        return (
            <div className="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                No products found.
            </div>
        );
    }

    return (
        <div className="overflow-x-auto mt-6">
            <table className="min-w-full border-collapse border border-gray-300">
                <thead className="bg-gray-200">
                    <tr>
                        <th className="border border-gray-300 px-4 py-2 text-left">ID</th>
                        <th className="border border-gray-300 px-4 py-2 text-left">Name</th>
                        <th className="border border-gray-300 px-4 py-2 text-left">SKU</th>
                        <th className="border border-gray-300 px-4 py-2 text-left">Price</th>
                        <th className="border border-gray-300 px-4 py-2 text-left">Quantity</th>
                        <th className="border border-gray-300 px-4 py-2 text-left">Description</th>
                    </tr>
                </thead>
                <tbody>
                    {products.map((product, index) => (
                        <tr key={product.id} className="hover:bg-gray-100">
                            <td className="border border-gray-300 px-4 py-2">{offset + index + 1}</td>
                            <td className="border border-gray-300 px-4 py-2">{product.name}</td>
                            <td className="border border-gray-300 px-4 py-2">{product.sku}</td>
                            <td className="border border-gray-300 px-4 py-2">{product.price}</td>
                            <td className="border border-gray-300 px-4 py-2">{product.quantity}</td>
                            <td className="border border-gray-300 px-4 py-2">{product.description}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

export default Example;

const exampleElement = document.getElementById('example');

if (exampleElement) {
    let initialData = null;

    if (window.__PRODUCTS__) {
        initialData = window.__PRODUCTS__;
    } else {
        const productsAttribute = exampleElement.getAttribute('data-products');

        if (productsAttribute) {
            try {
                initialData = JSON.parse(productsAttribute);
            } catch (error) {
                initialData = null;
            }
        }
    }

    const Index = ReactDOM.createRoot(exampleElement);

    Index.render(
        <React.StrictMode>
            <Example initialData={initialData} />
        </React.StrictMode>
    );
}
