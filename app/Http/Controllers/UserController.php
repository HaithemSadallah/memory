<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function search_user($name_user)
    {
        return User::where("username","like","%".$name_user."%")->get();
    }




    public function get_user()
    {
        $users = User::get();
        return response([
            'user' => $users
        ], 200);
    }



    public function ban(Request $request)
    {
        $input = $request->all();

        if(!empty($input['id']))
        {

            $user = User::find($input['id']);

            $validator = Validator::make($request->all(), [
            'comment' => 'nullable',
            ]);


          if($user)
          {
            if($user->isBanned())
            {
                return response([
                'message'=>'This account is baned before .'
                 ]);
            }
            $ban=  $user->bans()->create([
                'expired_at' => '+1 month',
                'comment'=>$request->comment
            ]);

            return response([
                'message' => 'ban succsusfuly',
                'data'=>$ban
            ], 200);

          }else{
            return response([
                'message'=>'id user not found'
            ]);
           }




        }
          return response([
            'message'=>'id user is empty'
          ]);
    }


    public function unban($id)
    {
        if(!empty($id))
        {
            $user = User::find($id);
            if($user)
            {
                if($user->isNotBanned())
                {
                    return response([
                    'message'=>'This account is unbaned before .'
                     ]);
                }
                $user->unban();

                return response([
                    'message' => 'unban succsusfuly'
                ], 200);
            }


        return response([
        'message' => 'id user not found'
        ], 200);

        }


    }

    public function delete_user($id_user)
    {
        $userPosts = Image::where('user_id', $id_user)->get();
        $user=User::find($id_user);
        if($user)
        {
            foreach ($userPosts as $image)
            {

                // Delete the post's photo from storage
                Storage::delete($image->images_post);

            }

            if($user->profile_img)
            {

                //Delete the profile image from storage
                Storage::delete($user->profile_img);


            }

             // Delete the user from the database

            $user->delete();
            return response([
                'message'=>'user deleted successfuly',
                'user'=>$user
                 ], 203);
        }else{
            return response([
            'message'=>'id user not found',
            ], 203);
        }
    }


}
