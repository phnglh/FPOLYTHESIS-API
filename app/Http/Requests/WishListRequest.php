<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;

class WishListRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }
}
