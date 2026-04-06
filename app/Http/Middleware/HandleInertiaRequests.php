<?php

namespace App\Http\Middleware;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        app()->setLocale(session('locale', 'en'));

        $locale = app()->getLocale();
        $translations = [];
        $path = base_path("lang/{$locale}.json");
        if (file_exists($path)) {
            $translations = json_decode(file_get_contents($path), true) ?? [];
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id'        => $request->user()->id,
                    'name'      => $request->user()->name,
                    'email'     => $request->user()->email,
                    'role'      => $request->user()->role,
                    'is_active' => $request->user()->is_active,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
            'lowStockCount' => fn () => $request->user() && $request->user()->role === 'admin'
                ? Ingredient::lowStock()->count()
                : 0,
            'locale' => $locale,
            'translations' => $translations,
        ];
    }
}
