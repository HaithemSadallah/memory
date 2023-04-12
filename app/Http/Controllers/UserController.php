<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function search_user($name_user)
    {
        return User::where("username","like","%".$name_user."%")->get();
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function get_user()
    {
        $users = User::get();
        return response([
            'user' => $users
        ], 200);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */

    public function ban(Request $request)

    {
        /*
        $ban = $user->ban([
   'expired_at' => null,
]);

$ban->isPermanent();
        */

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


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */

    public function revoke($id)
    {
        if(!empty($id))
        {
            $user = User::find($id);
            if($user){
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
        $userPosts = Post::where('user_id', $id_user)->get();
        $user=User::find($id_user);
        if($user){

            foreach ($userPosts as $post) {

                // Delete the post's photo from storage
                Storage::delete('public/posts/'.$post->images);

                // Delete the post from the database
                //$post->delete();
            }



            if($user->profile_img){
                Storage::delete('public/images/' . $user->profile_img);


            }




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
