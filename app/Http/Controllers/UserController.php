<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    public function search_user($name_user)
    {
        return User::where("username","like","%".$name_user."%")->paginate(4);
    }

    public function get_user()
    {
        return   User::paginate(4);

    }




    public function rateUser(Request $request, $ratedUserId)
    {
        // Get the authenticated user
        $user = auth()->user();

        // Get the rated user
        $ratedUser = User::find($ratedUserId);

        // Validate the $ratedUser object
        if (!$ratedUser) {
            return response()->json([
                'error' => 'The rated user does not exist.',
            ], 404);
        }

        // Validate the rating input
        $request->validate([
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        // Check if the user can rate the rated user
        if ($ratedUser->type_job == 'craftsman' ) {
            // Allow the rating to be created

            $rating = $user->ratings()->updateOrCreate(
                ['rated_user_id' => $ratedUserId],
                ['rating' => $request->rating]
            );

            $avgRating = DB::table('ratings')->select(DB::raw('AVG(rating) AS avg_rating'))->where('rated_user_id', '=', $ratedUserId)->get()->first();
            $ratedUser->rating = $avgRating->avg_rating;
            $ratedUser->save();


            return response([
                'message' => 'User rated successfully.',
                'data' => $ratedUser,
            ],200);
        } else {
            return response()->json([
                'error' => 'You are not allowed to rate this user.',
            ], 403);
        }
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
                 ],300);
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
            ],201);
           }




        }
          return response([
            'message'=>'id user is empty'
          ],301);
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
                     ],300);
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
                 ], 200);
        }else{
            return response([
            'message'=>'id user not found',
            ], 201);
        }
    }

    public function delete_post(Request $request,$id)
    {
         $post=Post::find($id);
             if ($post)
            {


                    foreach ($post->images as $image) {
                        Storage::delete($image->images_post);

                    }
                     $post->delete();
                     return response([
                     'message'=>'post user deleted successfuly',
                     'post'=>$post
                        ], 200);

            }

        return response([
             'message'=>'id post not found'
            ],201);


    }


}
