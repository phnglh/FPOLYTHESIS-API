<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\AttributeValue;

class AttributeService {
    // Lấy danh sách tất cả thuộc tính
    public function getAllAttributes() {
        return Attribute::with('values')->get();
    }

    // Tạo thuộc tính mới
    public function createAttribute(array $data) {
        return Attribute::create($data);
    }

    // Cập nhật thuộc tính
    public function updateAttribute($id, array $data) {
        $attribute = Attribute::findOrFail($id);
        $attribute->update($data);
        return $attribute;
    }

    // Xóa thuộc tính
    public function deleteAttribute($id) {
        $attribute = Attribute::findOrFail($id);
        $attribute->delete();
    }

    // -------------------------------
    // Giá trị thuộc tính (Attribute Values)
    // -------------------------------

    // Lấy danh sách giá trị của một thuộc tính
    public function getAttributeValues($attributeId) {
        return AttributeValue::where('attribute_id', $attributeId)->get();
    }

    // Tạo giá trị thuộc tính
    public function createAttributeValue($attributeId, array $data) {
        return AttributeValue::create([
            'attribute_id' => $attributeId,
            'value' => $data['value']
        ]);
    }

    // Cập nhật giá trị thuộc tính
    public function updateAttributeValue($id, array $data) {
        $value = AttributeValue::findOrFail($id);
        $value->update($data);
        return $value;
    }

    // Xóa giá trị thuộc tính
    public function deleteAttributeValue($id) {
        $value = AttributeValue::findOrFail($id);
        $value->delete();
    }
}
