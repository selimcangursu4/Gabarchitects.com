<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(?BlogCategory $category = null): View
    {
        $posts = BlogPost::published()
            ->when($category, fn ($q) => $q->where('blog_category_id', $category->id))
            ->with(['cover', 'category', 'author'])
            ->orderByDesc('published_at')
            ->paginate(9);

        return view('pages.blog.index', [
            'posts' => $posts,
            'category' => $category,
            'categories' => BlogCategory::whereHas('posts', fn ($q) => $q->published())->orderBy('sort_order')->get(),
        ]);
    }

    public function show(BlogPost $post): View
    {
        abort_unless($post->isPubliclyVisible(), 404);

        $post->load(['cover', 'category', 'author']);

        return view('pages.blog.show', [
            'post' => $post,
            'related' => BlogPost::published()->whereKeyNot($post->id)
                ->when($post->blog_category_id, fn ($q) => $q->where('blog_category_id', $post->blog_category_id))
                ->orderByDesc('published_at')->with('cover')->limit(3)->get(),
        ]);
    }
}
