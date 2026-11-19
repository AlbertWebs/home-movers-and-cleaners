<?php

namespace App\Http\Controllers;

use App\Models\ContentPage;
use App\Models\Service;
use App\Support\ImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContentPageController extends Controller
{
    public function show(string $slug): View
    {
        $page = ContentPage::query()->where('slug', $slug)->where('is_published', true)->first();
        $legalDraft = false;
        if (! $page && array_key_exists($slug, config('site_content.pages', [])) && $slug !== 'about') {
            $page = new ContentPage(config('site_content.pages.'.$slug) + ['slug' => $slug]);
            $legalDraft = true;
        }

        if (! $page && $slug === 'about') {
            $page = new ContentPage(config('site_content.pages.about') + ['slug' => 'about', 'image' => 'images/service-moving.webp', 'image_alt' => 'Two movers carrying a wrapped sofa']);
        }
        abort_unless($page, 404);

        $viewData = ['page' => $page, 'preview' => false, 'legalDraft' => $legalDraft];
        if ($slug === 'about') {
            $viewData['services'] = Service::query()->where('is_active', true)->orderBy('sort_order')->get();
        }

        return view('pages.content', $viewData);
    }

    public function index(Request $request): View
    {
        $data = $request->validate(['edit' => ['nullable', 'alpha_dash:ascii', 'max:100']]);
        $pages = ContentPage::query()->orderBy('title')->get()->keyBy('slug');
        foreach (config('site_content.pages') as $slug => $defaults) {
            if (! $pages->has($slug)) {
                $pages->put($slug, new ContentPage($defaults + ['slug' => $slug]));
            }
        }
        $selected = $pages->get($data['edit'] ?? '') ?? new ContentPage;

        return view('admin.pages', compact('pages', 'selected'));
    }

    public function save(Request $request, ImageOptimizer $optimizer): RedirectResponse
    {
        $data = $request->validate([
            'page_id' => ['nullable', 'integer', 'exists:content_pages,id'],
            'slug' => ['required', 'string', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'max:100'],
            'title' => ['required', 'string', 'max:150'],
            'summary' => ['required', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:30000'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'image_alt' => ['required_with:image_file', 'nullable', 'string', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:150'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'canonical_url' => ['nullable', 'url', 'max:2048'],
            'seo_robots' => ['nullable', Rule::in(['index, follow', 'noindex, follow', 'noindex, nofollow'])],
            'og_title' => ['nullable', 'string', 'max:150'],
            'og_description' => ['nullable', 'string', 'max:320'],
            'og_image' => ['nullable', 'url', 'max:2048'],
            'is_published' => ['required', 'boolean'],
        ]);

        $page = isset($data['page_id'])
            ? ContentPage::query()->findOrFail($data['page_id'])
            : ContentPage::query()->firstOrNew(['slug' => $data['slug']]);
        $request->validate(['slug' => [Rule::unique('content_pages')->ignore($page->id)]]);
        if ($page->exists && array_key_exists($page->slug, config('site_content.pages')) && $page->slug !== $data['slug']) {
            return back()->withErrors(['slug' => 'The address of a built-in page cannot be changed.'])->withInput();
        }

        if ($request->hasFile('image_file')) {
            $data['image'] = 'storage/'.$optimizer->store($request->file('image_file'), 'site');
        }

        $page->fill($data)->save();

        return redirect()->route('admin.pages', ['edit' => $page->slug])->with('success', 'Page saved.');
    }

    public function preview(ContentPage $page): View
    {
        $viewData = ['page' => $page, 'preview' => true];
        if ($page->slug === 'about') {
            $viewData['services'] = Service::query()->where('is_active', true)->orderBy('sort_order')->get();
        }

        return view('pages.content', $viewData);
    }
}
