<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index()
    {
        $slug = 'data:list';
        $cached = Redis::get('posts:'.$slug);

        if(isset($cached)) {
            $posts = json_decode($cached, FALSE);

            // return response()->json([
            //     'status_code' => 200,
            //     'message' => 'data dari redis',
            //     'data' => $posts
            // ]);
            return new PostResource(true, 'Data REDIS', $posts);
        } else {
            $posts = Post::orderBy("id", "asc")->get();
            Redis::set('posts:data:list', $posts, 'EX', 120);

            // return response()->json([
            //     'status_code' => 200,
            //     'message' => 'data dari db',
            //     'data' => $posts
            // ]);
            return new PostResource(true, 'Data DB', $posts);
        }


    }

    public function store (Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required',
            'content' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $image_path = $request->file('image')->store('posts', 'public');


        $post = Post::create([
            'image' => $image_path,
            'title' => $request->title,
            'content' => $request->content
        ]);

        return new PostResource(true, 'Data Post Berhasil Ditambahkan', $post);
    }

    public function show (Post $post)
    {
        return new PostResource(true, 'Data Post Ditemukan', $post);
    }

    public function update (Request $request, Post $post)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'title'     => 'required',
            'content'   => 'required',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->hasFile('image')) {
            $image_path = $request->file('image')->store('posts', 'public');

            //delete old image
            Storage::delete('public/'.$post->image);

            $post->update([
                'image'     => $image_path,
                'title'     => $request->title,
                'content'   => $request->content,
            ]);
        } else {
            $post->update([
                'title'     => $request->title,
                'content'   => $request->content,
            ]);
        }
        return new PostResource(true, 'Data Post Berhasil Diubah!', $post);
    }

    public function destroy(Post $post)
    {
        Storage::delete('public/'.$post->image);

        $post->delete();

        return new PostResource(true, 'Data Post Berhasil Dihapus!', null);
    }
}
