<?php

namespace App\Http\Controllers;
use App\Models\Post;
use App\Models\User;
use App\Models\Image;
use Illuminate\Http\Request;
use App\Http\Requests\PostRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Notifications\PostNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function search_post($name_post)
    {
        return Post::where("title","like","%".$name_post."%")->paginate(12);

    }

    public function get_Post()
    {
        $post = Post::with('user')->latest()->paginate(12);
        return response([
            'post' => $post
        ], 200);

    }


    public function markAsRead(Request $request, $id)
    {
        $user = Auth::user();

        $notification = $user->notifications()->find($id);

        $notification->markAsRead();

        return response([
            'message' => 'Notification marked as read.'
        ],200);
    }




    public function store(PostRequest $request)
    {
       $request->validated();
        $post = auth()->user()->posts()->create([
            'title' => $request->title,
            'description' => $request->description,
        ]);
        if ($request->hasFile('images_post'))
        {
            foreach ($request->file('images_post') as $images) {
                $path = $images->store('public/posts');
                $post->images()->create([
                    'user_id'=>auth()->user()->id,
                    'images_post'=>$path
                ]);
            }
        }


        $post->user->notify(new PostNotification($post));
        $post->load('user','images');
        return response([
            'message'=>'post created successfuly',
            'data'=>$post,
        ], 200);
    }

    public function update_post(Request $request ,$id)
    {
      $post = Post::find($id);


        if($post)
        {



            if ( $post->user_id == $request->user()->id)
            {

                $validator = Validator::make($request->all(), [
                    'title' => 'nullable|string',
                    'description' => 'nullable',
                    'images_post.*' => 'nullable|image|mimes:png,jpg'
                ]);

                if ($validator->fails())
                {
                    return response([
                    'message'=> 'Validation error' ,
                    'error'=>  $validator->errors()
                    ],400);
                }

                    $post->update([
                    'title' => $request->title,
                    'description' => $request->description,
                    ]);


                    foreach ($post->images as $image)
                    {
                     Storage::delete($image->images_post);
                     $image->delete();
                    }

                    if ($request->hasFile('images_post'))
                    {
                            foreach ($request->file('images_post') as $image)
                            {
                                $path = $image->store('public/posts');
                                $image = new Image([
                                    'images_post' => $path,
                                    'user_id' => auth()->id(),
                                ]);
                                $post->images()->save($image);

                            }
                    }
                    $post->load('images');
                    return response([
                    'message'=>'post updated successfuly',
                    'data'=>$post,
                    ], 200);
            }else{
            return response([   'message' => 'Unauthorized'  ],202);
            }



        }

      return response([
      'message'=>'id post not found',
    ], 201);

    }



    public function delete_post(Request $request,$id)
    {
         $post=Post::find($id);
             if ($post)
            {
                if($post->user_id == $request->user()->id)
                {

                    foreach ($post->images as $image) {
                        Storage::delete($image->images_post);

                    }
                     $post->delete();
                     return response([
                     'message'=>'post deleted successfuly',
                     'post'=>$post
                        ], 200);
                } else{
                    return response([
                        'message' => 'Unauthorized'
                     ], 202);
                }
            }

        return response([
             'message'=>'id post not found'
            ],201);


    }


}


?>
