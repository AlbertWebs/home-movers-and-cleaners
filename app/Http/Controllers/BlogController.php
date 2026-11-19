<?php

namespace App\Http\Controllers;

use App\Models\BlogArticle;
use App\Support\ImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function show(string $slug): View
    {
        $article = BlogArticle::query()->where('slug', $slug)->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now())->firstOrFail();
        $articleSchema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->title,
            'description' => $article->excerpt,
            'datePublished' => $article->published_at->toIso8601String(),
            'dateModified' => $article->updated_at->toIso8601String(),
            'mainEntityOfPage' => $article->canonical_url ?: route('blog.show', $article->slug),
            'image' => $article->featured_image ? asset($article->featured_image) : null,
            'publisher' => ['@type' => 'Organization', 'name' => 'Home Movers and Cleaners'],
        ], static fn (mixed $value): bool => $value !== null);

        return view('pages.blog-article', compact('article', 'articleSchema'));
    }

    public function adminIndex(Request $request): View
    {
        $articles = BlogArticle::query()->orderByDesc('updated_at')->paginate(20);
        $selected = $request->integer('edit') ? BlogArticle::query()->findOrFail($request->integer('edit')) : new BlogArticle;

        return view('admin.blog', compact('articles', 'selected'));
    }

    public function store(Request $request, ImageOptimizer $optimizer): RedirectResponse
    {
        return $this->save($request, new BlogArticle, $optimizer);
    }

    public function update(Request $request, BlogArticle $article, ImageOptimizer $optimizer): RedirectResponse
    {
        return $this->save($request, $article, $optimizer);
    }

    public function destroy(BlogArticle $article): RedirectResponse
    {
        $article->update(['status' => 'draft', 'published_at' => null]);

        return back()->with('success', 'Article unpublished and saved as a draft.');
    }

    private function save(Request $request, BlogArticle $article, ImageOptimizer $optimizer): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('blog_articles', 'slug')->ignore($article->id)],
            'category' => ['required', 'string', 'max:80'],
            'excerpt' => ['required', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:50000'],
            'featured_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'image_alt' => ['required_with:featured_image_file', 'nullable', 'string', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:150'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'canonical_url' => ['nullable', 'url', 'max:2048'],
            'seo_robots' => ['nullable', Rule::in(['index, follow', 'noindex, follow', 'noindex, nofollow'])],
            'og_title' => ['nullable', 'string', 'max:150'],
            'og_description' => ['nullable', 'string', 'max:320'],
            'og_image' => ['nullable', 'url', 'max:2048'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
        ]);

        if ($article->exists && $article->slug !== $data['slug']) {
            return back()->withErrors(['slug' => 'An existing article URL cannot be changed here.'])->withInput();
        }
        if ($request->hasFile('featured_image_file')) {
            $data['featured_image'] = 'storage/'.$optimizer->store($request->file('featured_image_file'), 'site');
        }
        unset($data['featured_image_file']);
        if ($data['status'] === 'published') {
            $data['published_at'] = $data['published_at'] ?? $article->published_at ?? now();
        } else {
            $data['published_at'] = null;
        }
        $article->fill($data)->save();

        return redirect()->route('admin.blog.index', ['edit' => $article->id])->with('success', 'Article saved.');
    }
}
