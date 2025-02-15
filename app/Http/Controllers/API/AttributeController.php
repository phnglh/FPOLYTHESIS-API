<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttributeRequest;
use App\Http\Requests\AttributeValueRequest;
use App\Services\AttributeService;

class AttributeController extends Controller {
    protected $attributeService;

    public function __construct(AttributeService $attributeService) {
        $this->attributeService = $attributeService;
    }

    // Lấy danh sách thuộc tính
    public function index() {
        return response()->json($this->attributeService->getAllAttributes());
    }

    // Tạo thuộc tính mới
    public function store(AttributeRequest $request) {
        return response()->json($this->attributeService->createAttribute($request->validated()), 201);
    }

    // Cập nhật thuộc tính
    public function update(AttributeRequest $request, $id) {
        return response()->json($this->attributeService->updateAttribute($id, $request->validated()));
    }

    // Xóa thuộc tính
    public function destroy($id) {
        $this->attributeService->deleteAttribute($id);
        return response()->json(['message' => 'Attribute deleted successfully']);
    }

    // -------------------------------
    // Giá trị thuộc tính (Attribute Values)
    // -------------------------------

    // Lấy danh sách giá trị của một thuộc tính
    public function getAttributeValues($attributeId) {
        return response()->json($this->attributeService->getAttributeValues($attributeId));
    }

    // Tạo giá trị thuộc tính mới
    public function storeAttributeValue(AttributeValueRequest $request, $attributeId) {
        return response()->json($this->attributeService->createAttributeValue($attributeId, $request->validated()), 201);
    }

    // Cập nhật giá trị thuộc tính
    public function updateAttributeValue(AttributeValueRequest $request, $id) {
        return response()->json($this->attributeService->updateAttributeValue($id, $request->validated()));
    }

    // Xóa giá trị thuộc tính
    public function destroyAttributeValue($id) {
        $this->attributeService->deleteAttributeValue($id);
        return response()->json(['message' => 'Attribute value deleted successfully']);
    }
}
