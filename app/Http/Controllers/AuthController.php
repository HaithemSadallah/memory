<?php

namespace App\Http\Controllers;

use Session;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;



class AuthController extends Controller
{
 public function register(RegisterRequest $request)
 {
        $request->validated();
       // $file_name = $request->file('profile_img')->store('public/images');
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number'=>$request->phone_number,
                'wilaya'=>$request->wilaya,
                'type_job'=>$request->type_job,
                'name_service'=>$request->name_service ?? 'nothing',
            ]);

    if ($request->hasFile('profile_img')) {
        $user->profile_img = $request->file('profile_img')->store('public/images');
        $user->save();
    }

        return response([
            'user' => $user,
        ], 200);

 }
 public function login(LoginRequest $request)
 {
   $request->validated();
   $user = User::whereEmail($request->email)->first();
   if (!$user || !Hash::check($request->password, $user->password)) {
       return response([
           'message' => 'Invalid credentials'
       ], 400);
   }
        if($user  && $user->isBanned())
        {
            $ban = $user->bans()->first();
            Session::flush();

            return response([
            'message'=>'This account is blocked.',
            'comment'=>$ban->comment
             ],500);
        }
   $token = $user->createToken('memory')->plainTextToken;

        return response([
            'token' => $token,
        ],200);
 }

 public function userDetails()
 {
    $pick=url("/".auth()->user()->profile_img);
    $user=auth()->user();
    $user->profile_img=$pick;
    return response([
            'user' => $user,
        ],200);
 }


  public function logout_user(Request $request)
  {


    $request->user()->currentAccessToken()->delete();

    return response([
            'Message' => "Logout Success."
     ], 200);

  }



  public function update_user(Request $request,$id)

    {
        $user=auth()->user();

        if($user->id==$id){
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
                ],400);
            }

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
            if($request->username){
                $user->update(['username'=>$request->username ]);
            }
            if($request->email){
                $user->update(['email'=>$request->email]);
            }
            if($request->phone_number){
                $user->update(['phone_number'=>$request->phone_number]);
            }

            if($request->wilaya){
                $user->update(['wilaya'=>$request->wilaya]);
            }

            if($request->profile_img){
                $user->update(['profile_img'=>$file_name]);
            }

            return response([
            'message'=> 'user update success' ,
            'data'=>  $user
            ],200);
        }else {
            return response([   'message' => 'Unauthorized'  ],202);
        }


    }


    public function update_password(Request $request,$id)
    {
        $user=auth()->user();
     if($user->id==$id){
        $validator = Validator::make($request->all(),
        [
        'old_password'=>'nullable',
        'password'=>'required|min:6',
        'confirm_password'=>'required|same:password',
        ]);

        if ($validator->fails())
        {
           return response([
          'message'=> 'Validation error' ,
          'error'=>  $validator->errors()
            ],400);
        }
        $user= $request->user();
        if(Hash::check($request->old_password, $user->password))
        {
            $user->update([
                'password'=>Hash::make($request->password),
            ]);
            return response([
                'message'=> 'password changed successfuly' ,
            ],200);

        }else {
            return response([
                'message'=> 'password old wrong' ,
            ],201);
        }

      }else{
            return response([   'message' => 'Unauthorized'  ],202);
        }



    }

}


?>
