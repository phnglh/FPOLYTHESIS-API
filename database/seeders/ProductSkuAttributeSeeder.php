<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sku;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;

class ProductSkuAttributeSeeder extends Seeder
{
    public function run()
    {
        $products = Product::factory()->count(50)->create([
            'image_url' => "https://i.pinimg.com/736x/af/13/eb/af13eb5efbe36bb9abd64be1f32cc0cf.jpg"
        ]);

        $attributes = Attribute::factory()->count(2)->create();

        $attributeValues = [];
        foreach ($attributes as $attribute) {
            $attributeValues[$attribute->id] = AttributeValue::factory()
                ->count(3)
                ->create(['attribute_id' => $attribute->id]);
        }

        $products->each(function ($product) use ($attributes, $attributeValues) {
            $skus = Sku::factory()->count(3)->create(['product_id' => $product->id]);

            $skus->each(function ($sku) use ($attributes, $attributeValues) {
                $attributes->each(function ($attribute) use ($sku, $attributeValues) {
                    $attributeValue = $attributeValues[$attribute->id]->random();

                    $sku->attribute_values()->attach($attributeValue->id, [
                        'attribute_id' => $attribute->id,
                        'value' => $attributeValue->value,
                    ]);
                });
            });
        });
    }
}
