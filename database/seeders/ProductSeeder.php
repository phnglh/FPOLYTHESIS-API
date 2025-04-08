<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sku;
use App\Models\Attribute;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $attributes = Attribute::with('values')->get();

        $products = Product::factory()->count(50)->create([
            'image_url' => "https://i.pinimg.com/736x/af/13/eb/af13eb5efbe36bb9abd64be1f32cc0cf.jpg"
        ]);

        $products->each(function ($product) use ($attributes) {
            $skus = Sku::factory()->count(3)->create(['product_id' => $product->id,  'image_url' => "https://i.pinimg.com/736x/af/13/eb/af13eb5efbe36bb9abd64be1f32cc0cf.jpg"]);

            $skus->each(function ($sku) use ($attributes) {
                $attributes->each(function ($attribute) use ($sku) {
                    $attributeValue = $attribute->values->random();

                    $sku->attribute_values()->attach($attributeValue->id, [
                        'attribute_id' => $attribute->id,
                        'value' => $attributeValue->value,
                    ]);
                });
            });
        });
    }
}
