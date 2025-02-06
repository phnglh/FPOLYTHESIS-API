<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{


    

    // phân quyền 
    public function updateRole(Request $request, $id)
    {
        if($request->user()->role != 'admin'){
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện thao tác này!',
                'data' => null,
                'errors' => null   
            ], 403);
        }

        $user = User::findOrFail($id);

        if($user->id === $request->user()->id){
            return response()->json([
                'success' => false,
                'message' => 'Bạn không thể tự thay đổi quyền của chính mình',
                'data' => null,
                'errors' => null  
            ], 400);
        }

        $newRole = $user->role === 'admin' ? 'customer' : 'admin';
        $user->update(['role'=> $newRole]);

        return response()->json([
                'success' => true,
                'message' => 'Nhân sự đã được cập nhật quyền!',
                'data' => $user,
                'errors' => null  
        ], 200);

    }
}
