<?php

namespace App\Services;

use App\Models\WishList;
use Illuminate\Support\Facades\Auth;

class WishListService
{
    // lấy danh sách sản phẩm yêu thích
    public function getAllWishLists()
    {
        $userId = Auth::id();
        return WishList::where('userId',$userId)->with('sku')->get();
    }

    // thêm sản phẩm vào danh sách sản phẩm yêu thích
    public function addWishList($skuId)
    {
        $userId = Auth::id();
        $exists = WishList::where('userId',$userId)->where('sku_id',$skuId)->exists();
        if($exists)
        {
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
        $listExists = WishList::where('userId',$userId)->where('sku_id',$skuId)->first();
        if($listExists)
        {
            return['message'=>'sản phẩm không tồn tại trong danh sách yêu thích' , 'status'=>400];
        }
        $listExists->delete();
        return['message'=>'đã xoá thành công sản phẩm khỏi danh sách yêu thích' , 'status'=>400];
    }

}
