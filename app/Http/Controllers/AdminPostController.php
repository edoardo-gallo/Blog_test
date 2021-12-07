<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminPostController extends Controller
{
    public function index() {
        return view('admin.posts.index', [
            'posts' => Post::paginate(50)
        ]);
    }
    public function create()
    {
        
        return view('admin.posts.create');
    }

    public function store() {
       
       $attributes = $this->validatePost(new Post()); 

       // to parse the user_id combined with the post created
       $attributes['user_id'] = auth()->id();
       $attributes['thumbnail'] = request()->file('thumbnail')->store('thumbnails');

       Post::create($attributes);

       return redirect('/');
    }

    public function edit(Post $post) {
        return view('admin.posts.edit', ['post' => $post]);
    }

    public function update(Post $post) 
    {
        $attributes = $this->validatePost(new Post());

        if(isset($attributes['thumbnail'])) {
            $attributes['thumbnail'] = request()->file('thumbnail')->store('thumbnails');
        }
        $post->update($attributes);

        return back()->with('succes', 'Post Updated');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return back()->with('success', 'Post Deleted');
    }


    protected function validatePost(?Post $post = null) // user not obligated to pass a post
    {
        $post ??= new Post(); // if you have a post, we're gonna use that

        return request()->validate([
            'title' => 'required',
            'thumbnail' => $post->exists ? ['image'] : ['required', 'image'],
            'slug' => ['required', Rule::unique('posts', 'slug')->ignore($post)],
            'excerpt' => 'required',
            'body' => 'required',
            'category_id' => ['required' , Rule::exists('categories', 'id')] // same as the above
        ]); 
    }
}
