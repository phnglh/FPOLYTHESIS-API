<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Http\Requests\UserAddressRequest;
use App\Services\UserAddressService;

class UserAddressController extends BaseController
{
    protected $userAddressService;

    public function __construct(UserAddressService $userAddressService)
    {
        $this->userAddressService = $userAddressService;
    }

    public function index()
    {
        $addresses = $this->userAddressService->getUserAddresses();
        return $this->successResponse($addresses, 'Danh sách địa chỉ');
    }

    public function store(UserAddressRequest $request)
    {
        $address = $this->userAddressService->createUserAddress($request->validated());
        return $this->successResponse($address, 'Địa chỉ đã được thêm');
    }

    public function update(UserAddressRequest $request, $id)
    {
        $address = $this->userAddressService->updateUserAddress($id, $request->validated());
        return $this->successResponse($address, 'Địa chỉ đã được cập nhật');
    }

    public function destroy($id)
    {
        $this->userAddressService->deleteUserAddress($id);
        return $this->successResponse(null, 'Địa chỉ đã được xóa');
    }
}
