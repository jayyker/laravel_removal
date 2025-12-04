<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // Get all products
    public function index()
    {
        $products = Product::all();
        return response()->json([
            'success' => true,
            'data' => $products,
            'count' => $products->count()
        ]);
    }

    // Get single product
    public function show($id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    // Create product
    public function store(Request $request)
    {
        // Debug: Log incoming data
        \Log::info('Store request data:', $request->all());
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::create($request->all());
        \Log::info('Product created:', $product->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }

    // Update product
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $id,
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $product->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }

    // Delete product and renumber ALL database IDs
    public function destroy($id)
    {
        \DB::beginTransaction();
        try {
            // 1. Delete the product
            $product = Product::findOrFail($id);
            $product->delete();
            
            // 2. Get all remaining products
            $remainingProducts = Product::orderBy('id')->get();
            
            // 3. Truncate and reinsert with sequential IDs
            \DB::statement('SET FOREIGN_KEY_CHECKS=0');
            Product::truncate();
            
            $newId = 1;
            foreach ($remainingProducts as $product) {
                Product::create([
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'quantity' => $product->quantity,
                    'description' => $product->description,
                    'created_at' => $product->created_at,
                    'updated_at' => now(),
                ]);
                $newId++;
            }
            
            // 4. Reset auto-increment
            \DB::statement("ALTER TABLE products AUTO_INCREMENT = $newId");
            \DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            \DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Product deleted. All IDs are now sequential.'
            ]);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
