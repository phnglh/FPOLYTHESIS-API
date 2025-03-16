<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\AttributeRequest;
use App\Http\Requests\AttributeValueRequest;
use App\Services\AttributeService;

class AttributeController extends BaseController
{
    protected $attributeService;

    public function __construct(AttributeService $attributeService)
    {
        $this->attributeService = $attributeService;
    }

    // Lấy danh sách thuộc tính
    public function index()
    {
        $attribute = $this->attributeService->getAllAttributes();

        return $this->successResponse($attribute, 'Attributes fetched successfully');
    }

    // Tạo thuộc tính mới
    public function store(AttributeRequest $request)
    {
        $attribute = $this->attributeService->createAttribute($request->validated());

        return $this->successResponse($attribute, 'Attribute created successfully');
    }

    // Cập nhật thuộc tính
    public function update(AttributeRequest $request, $id)
    {
        $attribute = $this->attributeService->updateAttribute($id, $request->validated());

        return $this->successResponse($attribute, 'Attribute updated successfully');
    }

    // Xóa thuộc tính
    public function destroy($id)
    {
        $this->attributeService->deleteAttribute($id);

        return $this->successResponse(null, 'Attribute deleted successfully');
    }

    // -------------------------------
    // Giá trị thuộc tính (Attribute Values)
    // -------------------------------

    // Lấy danh sách giá trị của một thuộc tính
    public function getAttributeValues($attributeId)
    {
        $attributeValues = $this->attributeService->getAttributeValues($attributeId);

        return $this->successResponse($attributeValues, 'Attribute values fetched successfully');
    }

    // Tạo giá trị thuộc tính mới
    public function storeAttributeValue(AttributeValueRequest $request, $attribute_id)
    {
        $attributeValue = $this->attributeService->createAttributeValue($attribute_id, $request->validated());

        return $this->successResponse($attributeValue, 'Attribute value created successfully');
    }

    // Cập nhật giá trị thuộc tính
    public function updateAttributeValue(AttributeValueRequest $request, $id)
    {
        $attributeValue = $this->attributeService->updateAttributeValue($id, $request->validated());

        return $this->successResponse($attributeValue, 'Attribute value updated successfully');
    }

    // Xóa giá trị thuộc tính
    public function destroyAttributeValue($id)
    {
        $this->attributeService->deleteAttributeValue($id);

        return $this->successResponse(null, 'Attribute value deleted successfully');
    }
}
