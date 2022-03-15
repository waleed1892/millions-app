<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Post::withCount('likes')
            ->with(['recentlyLikedBy', 'author'])
            ->orderBy('created_at', 'desc')->paginate();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $file_path = '';
        if($request->hasFile('image')){
            $image = $request->file('image');
            $file_path = $image->storePublicly('posts');
        }
        $input = $request->except('image');
        $input['slug'] = Str::slug($request->title);
        $input['image'] = env('APP_URL')."/storage/" .$file_path;
        $input['author_id'] = $request->user()->id;
        $input['id'] = Str::uuid();
        $post = Post::create($input);
        return $post;
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Post $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        return $post->loadCount('likes')->load(['author', 'recentlyLikedBy']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Post $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        $post->update($request->all());
        return $post;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Post $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        if($post->image){
            Storage::delete(str_replace(env('APP_URL')."/storage/", "", $post->image));
        }
        $post->delete();
        return $post;
    }

    public function toggleLike(Request $request)
    {
        $post_id = $request->post_id;
        $post = Post::findOrFail($post_id);
        $post->likes()->toggle($request->user()->id, ['created_at' => now()]);
        return $post->load('likes');
    }
}
