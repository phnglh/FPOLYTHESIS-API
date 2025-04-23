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

        return $this->successResponse($attribute, 'GET_TO_ATTRIBUTES_SUCCESS');
    }

    // Tạo thuộc tính mới
    public function store(AttributeRequest $request)
    {
        $attribute = $this->attributeService->createAttribute($request->validated());

        return $this->successResponse($attribute, 'ADD_TO_ATTRIBUTES_SUCCESS');
    }

    // Cập nhật thuộc tính
    public function update(AttributeRequest $request, $id)
    {
        $attribute = $this->attributeService->updateAttribute($id, $request->validated());

        return $this->successResponse($attribute, 'UPDATE_TO_ATTRIBUTES_SUCCESS');
    }

    // Xóa thuộc tính
    public function destroy($id)
    {
        $this->attributeService->deleteAttribute($id);

        return $this->successResponse(null, 'DELETE_TO_ATTRIBUTES_SUCCESS');
    }

    // -------------------------------
    // Giá trị thuộc tính (Attribute Values)
    // -------------------------------

    // Lấy danh sách giá trị của một thuộc tính
    public function getAttributeValues($attributeId)
    {
        $attributeValues = $this->attributeService->getAttributeValues($attributeId);

        return $this->successResponse($attributeValues, 'GET_TO_ATTRIBUTES_SUCCESS');
    }

    // Tạo giá trị thuộc tính mới
    public function storeAttributeValue(AttributeValueRequest $request, $attribute_id)
    {
        $attributeValue = $this->attributeService->createAttributeValue($attribute_id, $request->validated());

        return $this->successResponse($attributeValue, 'ADD_TO_ATTRIBUTES_SUCCESS');
    }

    // Cập nhật giá trị thuộc tính
    public function updateAttributeValue(AttributeValueRequest $request, $id)
    {
        $attributeValue = $this->attributeService->updateAttributeValue($id, $request->validated());

        return $this->successResponse($attributeValue, 'UPDATE_TO_ATTRIBUTES_SUCCESS');
    }

    // Xóa giá trị thuộc tính
    public function destroyAttributeValue($id)
    {
        $this->attributeService->deleteAttributeValue($id);

        return $this->successResponse(null, 'DELETE_TO_ATTRIBUTES_SUCCESS');
    }
}
