<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing products
        Product::query()->delete();
        
        // Add sample products
        $products = [
            [
                'name' => 'Laptop',
                'sku' => 'LP001',
                'price' => 9999.00,
                'quantity' => 15,
                'description' => 'Latest version laptop with high performance'
            ],
            [
                'name' => 'Keyboard',
                'sku' => 'KB001',
                'price' => 120.00,
                'quantity' => 20,
                'description' => 'Mechanical keyboard with RGB lighting'
            ],
            [
                'name' => 'Mouse',
                'sku' => 'MS001',
                'price' => 55.00,
                'quantity' => 50,
                'description' => 'Wireless optical mouse'
            ],
            [
                'name' => 'Monitor',
                'sku' => 'MN001',
                'price' => 300.00,
                'quantity' => 10,
                'description' => '27 inch 4K monitor'
            ],
            [
                'name' => 'Headphones',
                'sku' => 'HP001',
                'price' => 200.00,
                'quantity' => 25,
                'description' => 'Noise cancelling wireless headphones'
            ]
        ];
        
        foreach ($products as $product) {
            Product::create($product);
        }
        
        $this->command->info('Products seeded successfully!');
    }
}
