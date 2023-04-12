<?php

namespace App\Http\Controllers;

use Session;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
 public function register(RegisterRequest $request)
 {
        $request->validated();
        $file_name = $request->file('profile_img')->store('public/images');
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number'=>$request->phone_number,
            'wilaya'=>$request->wilaya,
            'profile_img'=>$file_name,
        ]);
        $token = $user->createToken('memory')->plainTextToken;

        return response([
            'user' => $user,
            'token' => $token
        ], 201);

 }
 public function login(LoginRequest $request)
 {
   $request->validated();
   $user = User::whereEmail($request->email)->first();
   if (!$user || !Hash::check($request->password, $user->password)) {
       return response([
           'message' => 'Invalid credentials'
       ], 422);
   }


        if($user  && $user->isBanned())
        {
            Session::flush();

            return response([
            'message'=>'This account is blocked.'
             ]);
        }


   $token = $user->createToken('memory')->plainTextToken;

        return response([
            'user' => $user,
            'token' => $token,

        ],200);
 }
  public function logout(Request $request)
  {


    $request->user()->currentAccessToken()->delete();

    return response([
            'Message' => "Logout Success."
     ], 200);

  }



  public function update_user(Request $request)

    {


        $validator = Validator::make($request->all(), [
            'username'=>'nullable|string',
            'email'=>'nullable|email|unique:users',
            'phone_number'=>'nullable|min:10|max:10|unique:users',
            'wilaya'=>'nullable|string',
            'profile_img'=>'nullable|image|mimes:png,jpg',

        ]);

        if ($validator->fails())
        {
            return response([
            'message'=> 'Validation error' ,
            'error'=>  $validator->errors()
            ]);
        }
        $user=$request->user();
        $oldImages = $user->profile_img;
        if($request->hasFile('profile_img') )
        {
            if($oldImages)
            {
                Storage::delete($oldImages);
            }
            $file_name = $request->profile_img->store('public/images');
        }else
        {
            $file_name = $user->profile_img;
        }
        $user->update([
        'username'=>$request->username,
        'email'=>$request->email,
        'phone_number'=>$request->phone_number,
        'wilaya'=>$request->wilaya,
        'profile_img'=>$file_name,
         ]);

        return response([
        'message'=> 'user update success' ,
        'data'=>  $user
        ]);
    }


    public function update_password(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
        'old_password'=>'nullable',
        'password'=>'nullable|min:6',
        'confirm_password'=>'nullable|same:password',
        ]);

        if ($validator->fails())
        {
           return response([
          'message'=> 'Validation error' ,
          'error'=>  $validator->errors()
            ]);
        }
        $user= $request->user();
        if(Hash::check($request->old_password, $user->password))
        {
            $user->update([
                'password'=>Hash::make($request->password),
            ]);
        }
        return response([
            'message'=> 'password change successfuly' ,
        ]);




    }

}


?>
