<?php

namespace App\Http\Requests;

class WishListRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }
}
