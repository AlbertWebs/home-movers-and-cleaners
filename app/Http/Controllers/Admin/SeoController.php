<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogArticle;
use App\Models\ContentPage;
use App\Models\Service;
use App\Models\UrlRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SeoController extends Controller
{
    public function index(): View
    {
        $services = Service::query()->where('is_active', true)->get(['seo_title', 'seo_description', 'seo_robots']);
        $pages = ContentPage::query()->where('is_published', true)->get(['seo_title', 'seo_description', 'seo_robots']);
        $articles = BlogArticle::query()->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now())->get(['seo_title', 'seo_description', 'seo_robots']);
        $indexed = $services->concat($pages)->concat($articles)->filter(fn ($item): bool => ! str_contains($item->seo_robots ?? '', 'noindex'));
        $redirects = UrlRedirect::query()->latest()->paginate(20);

        return view('admin.seo', [
            'indexedCount' => $indexed->count(),
            'missingTitles' => $indexed->filter(fn ($item): bool => blank($item->seo_title))->count(),
            'missingDescriptions' => $indexed->filter(fn ($item): bool => blank($item->seo_description))->count(),
            'publishedArticles' => $articles->count(),
            'redirects' => $redirects,
        ]);
    }

    public function storeRedirect(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'from_path' => ['required', 'string', 'max:500', 'regex:/^\/(?!\/)[^?#]*$/', 'unique:url_redirects,from_path'],
            'destination' => ['required', 'string', 'max:2048', function (string $attribute, mixed $value, \Closure $fail): void {
                if (is_string($value) && ((Str::startsWith($value, '/') && ! Str::startsWith($value, '//')) || (filter_var($value, FILTER_VALIDATE_URL) && in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true)))) {
                    return;
                }
                $fail('Use a local path beginning with one slash or a complete HTTP/HTTPS URL.');
            }],
            'status_code' => ['required', Rule::in([301, 302])],
        ]);
        $this->validateRedirect($data['from_path'], $data['destination']);
        $data['history'] = [['destination' => $data['destination'], 'status_code' => (int) $data['status_code'], 'changed_at' => now()->toIso8601String()]];
        UrlRedirect::query()->create($data);

        return back()->with('success', 'Redirect saved.');
    }

    public function updateRedirect(Request $request, UrlRedirect $redirect): RedirectResponse
    {
        $data = $request->validate([
            'from_path' => ['required', 'string', 'max:500', 'regex:/^\/(?!\/)[^?#]*$/', Rule::unique('url_redirects', 'from_path')->ignore($redirect->id)],
            'destination' => ['required', 'string', 'max:2048', function (string $attribute, mixed $value, \Closure $fail): void {
                if (is_string($value) && ((Str::startsWith($value, '/') && ! Str::startsWith($value, '//')) || (filter_var($value, FILTER_VALIDATE_URL) && in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true)))) {
                    return;
                }
                $fail('Use a local path beginning with one slash or a complete HTTP/HTTPS URL.');
            }],
            'status_code' => ['required', Rule::in([301, 302])],
            'is_active' => ['required', 'boolean'],
        ]);
        $this->validateRedirect($data['from_path'], $data['destination'], $redirect);
        $history = $redirect->history ?? [];
        if ($redirect->destination !== $data['destination'] || $redirect->from_path !== $data['from_path'] || $redirect->status_code !== (int) $data['status_code']) {
            $history[] = ['from_path' => $redirect->from_path, 'destination' => $redirect->destination, 'status_code' => $redirect->status_code, 'changed_at' => now()->toIso8601String()];
            $history[] = ['from_path' => $data['from_path'], 'destination' => $data['destination'], 'status_code' => (int) $data['status_code'], 'changed_at' => now()->toIso8601String()];
        }
        $redirect->update($data + ['history' => $history]);

        return back()->with('success', 'Redirect updated.');
    }

    public function redirectLegacy(Request $request): RedirectResponse
    {
        $path = '/'.ltrim($request->path(), '/');
        $redirect = UrlRedirect::query()->where('from_path', $path)->where('is_active', true)->first();
        abort_unless($redirect, 404);

        return redirect()->to($redirect->destination, $redirect->status_code);
    }

    private function validateRedirect(string $fromPath, string $destination, ?UrlRedirect $current = null): void
    {
        $normalizedFromPath = '/'.trim((string) preg_replace('#/+#', '/', $fromPath), '/');
        if ($fromPath !== $normalizedFromPath) {
            throw ValidationException::withMessages(['from_path' => 'Use a normalized path with one leading slash and no trailing slash.']);
        }
        if ($fromPath === '/') {
            throw ValidationException::withMessages(['from_path' => 'The homepage cannot be used as a redirect source.']);
        }
        try {
            $matchedRoute = app('router')->getRoutes()->match(Request::create($fromPath));
            if ($matchedRoute->getActionName() !== self::class.'@redirectLegacy') {
                throw ValidationException::withMessages(['from_path' => 'This address is already handled by a live website page.']);
            }
        } catch (NotFoundHttpException) {
            // An unmatched path is eligible to use the redirect fallback.
        }
        if (Str::startsWith($destination, '/')) {
            $destinationPath = (string) parse_url($destination, PHP_URL_PATH);
            $nextPath = '/'.trim((string) preg_replace('#/+#', '/', $destinationPath), '/');
            if ($destinationPath !== $nextPath) {
                throw ValidationException::withMessages(['destination' => 'Use a normalized local path with one leading slash and no trailing slash.']);
            }
            if ($nextPath === $fromPath) {
                throw ValidationException::withMessages(['destination' => 'A redirect cannot point to itself.']);
            }
            $nextRedirect = UrlRedirect::query()->where('from_path', $nextPath)->where('is_active', true)->when($current, fn ($query) => $query->where('id', '!=', $current->id))->first();
            if ($nextRedirect && $nextRedirect->destination === $fromPath) {
                throw ValidationException::withMessages(['destination' => 'This would create a redirect loop. Remove or update the conflicting redirect first.']);
            }
            if ($nextRedirect) {
                throw ValidationException::withMessages(['destination' => 'This destination already redirects elsewhere. Use its final destination to avoid a redirect chain.']);
            }
        }
    }
}
