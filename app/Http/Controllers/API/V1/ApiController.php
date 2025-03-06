<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'API is working!'], 200);
    }
}
