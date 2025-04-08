<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttributeSeeder extends Seeder
{
    public function run()
    {
        $attributes = [
            ['name' => 'Color'],
            ['name' => 'Size'],
            ['name' => 'Material']
        ];

        foreach ($attributes as $attribute) {
            $attributeId = DB::table('attributes')->insertGetId($attribute);

            // Thêm giá trị cho mỗi thuộc tính
            $values = match ($attribute['name']) {
                'Color' => ['Red', 'Blue', 'Green'],
                'Size' => ['S', 'M', 'L', 'XL'],
                'Material' => ['Cotton', 'Polyester', 'Silk'],
                default => [],
            };

            foreach ($values as $value) {
                DB::table('attribute_values')->insert([
                    'attribute_id' => $attributeId,
                    'value' => $value
                ]);
            }
        }
    }
}
