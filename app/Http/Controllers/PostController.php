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
        return Post::where("name_service","like","%".$name_post."%")->get();
        /*$posts = Post::where("name_service", "like", "%".$name_post."%")->paginate(10);
    return response([
        'posts' => $posts
    ], 200);*/


    }
    public function markAsRead(Request $request, $id)
    {
        $user = Auth::user();

        $notification = $user->notifications()->find($id);

        $notification->markAsRead();

        return response([
            'message' => 'Notification marked as read.'
        ]);
    }


    public function index()
    {
        $post = Post::with('user')->latest()->get();
        return response([
            'post' => $post
        ], 200);
       /* $posts = Post::with('user')->latest()->paginate(10);
        return response([
            'posts' => $posts
        ], 200);*/

    }


    public function store(PostRequest $request)
    {
       $request->validated();
        $post = auth()->user()->posts()->create([
            'name_service' => $request->name_service,
            'description' => $request->description,
        ]);
        if ($request->hasFile('images_post')){
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
            'message'=>'post successfuly',
            'data'=>$post,
        ], 201);
    }

    public function update_post(Request $request ,$id)
    {
      $post = Post::find($id);


        if($post)
        {



            if ( $post->user_id == $request->user()->id)
            {

                $validator = Validator::make($request->all(), [
                    'name_service' => 'nullable|string',
                    'description' => 'nullable',
                    'images_post.*' => 'nullable|image|mimes:png,jpg'
                ]);

                if ($validator->fails())
                {
                    return response([
                    'message'=> 'Validation error' ,
                    'error'=>  $validator->errors()
                    ]);
                }

                    $post->update([
                    'name_service' => $request->name_service,
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

                                $post->images()->update([
                                'user_id'=>auth()->user()->id,
                                'images_post'=>$path
                                ]);
                            }
                    }

                    return response([
                    'message'=>'post updated successfuly',
                    'data'=>$post,
                    ], 202);
            }else{
            return response([   'message' => 'Unauthorized'  ]);
            }



        }

                return response([
                    'message'=>'id post not found',
                ], 203);
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
                        ], 203);
                } else{
                    return response([
                        'message' => 'Unauthorized'
                     ], 203);
                }
            }

        return response([
             'message'=>'id post not found'
            ]);


    }


}


?>
