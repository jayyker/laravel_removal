import React from 'react';
import { createRoot } from 'react-dom/client';
import ProductInventory from './ProductInventory.jsx';

// Find the root element
const container = document.getElementById('app');

// Create a React root and render the app
if (container) {
    const root = createRoot(container);
    root.render(<ProductInventory />);
} else {
    console.error('Root element #app not found');
}
