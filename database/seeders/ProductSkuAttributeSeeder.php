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
        $products = Product::factory()->count(5)->create();

        $products->each(function ($product) {
            $skus = Sku::factory()->count(3)->create(['product_id' => $product->id]);

            $attributes = Attribute::factory()->count(2)->create();

            $skus->each(function ($sku) use ($attributes) {
                $attributes->each(function ($attribute) use ($sku) {
                    $attributeValue = AttributeValue::factory()->create([
                        'attribute_id' => $attribute->id
                    ]);

                    $sku->attribute_values()->attach($attributeValue->id, [
                        'attribute_id' => $attribute->id,
                        'value' => $attributeValue->value,
                    ]);
                });
            });
        });
    }
}
