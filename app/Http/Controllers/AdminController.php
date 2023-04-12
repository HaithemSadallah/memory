<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Requests\AdminRequest;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function login(AdminRequest $request)
    {
        $request->validated();

        $admin = Admin::whereEmail($request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response([
                'message' => 'Invalid Admin Credentials'
            ], 400);
        }


        $token = $admin->createToken('adminLogin')->plainTextToken;

        return response([
            'message' => 'success',
            'admin' => $admin,
            'token' => $token,
        ]);
    }

    public function logout_admin(Request $request)
    {

       $request->user()->currentAccessToken()->delete();

        return response([
            'Message' => "Logout Success."
        ], 200);
    }
}
