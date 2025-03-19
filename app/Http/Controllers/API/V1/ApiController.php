<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Api\BaseController;

class ApiController extends BaseController
{
    public function index()
    {
        return $this->successResponse(null, 'API is working!', 200);
    }
}
