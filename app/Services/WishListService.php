<?php

namespace App\Services;

use App\Models\WishList;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\ApiException; // them moi

class WishListService
{
    // lấy danh sách sản phẩm yêu thích
    public function getAllWishLists()
    {
        $userId = Auth::id();
        if (!$userId) {
            throw new ApiException(
                'Không lấy được dữ liệu',
                404
            );
        }
        return WishList::where('userId',$userId)->with('sku')->get();
    }

    // thêm sản phẩm vào danh sách sản phẩm yêu thích
    public function addWishList($skuId)
    {
        $userId = Auth::id();
        if (!$userId) {
            throw new ApiException(
                'Không lấy được dữ liệu',
                404
            );
        }
        $exists = WishList::where('userId',$userId)->where('sku_id',$skuId)->exists();
        if (!$exists) {
            throw new ApiException(
                'Không lấy được dữ liệu',
                404
            );
        }
        else{
            return['message'=>'sản phẩm đã có trong danh sách yêu thích' , 'status'=>400];
        }
        WishList::create([
            'userId' => $userId,
            'sku_id' => $skuId
        ]);
        return['message'=>'đã thêm thành công vào danh sách yêu thích' , 'status'=>200];
    }

    // xoá sản phẩm khỏi danh sách sản phẩm yêu thích
    public function deleteWishList($skuId)
    {
        $userId = Auth::id();
        if (!$userId) {
            throw new ApiException(
                'Không lấy được dữ liệu',
                404
            );
        }
        $listExists = WishList::where('userId',$userId)->where('sku_id',$skuId)->first();
        if($listExists)
        {
            return['message'=>'sản phẩm không tồn tại trong danh sách yêu thích' , 'status'=>400];
        }
        $listExists->delete();
        return['message'=>'đã xoá thành công sản phẩm khỏi danh sách yêu thích' , 'status'=>400];
    }

}
