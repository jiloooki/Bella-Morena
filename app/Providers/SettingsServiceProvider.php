<?php

namespace App\Providers;

use App\Models\Page;
use App\Models\Settings;
use App\Models\Menu;
use Illuminate\Contracts\Cache\Factory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Factory $cache, Settings $settings)
    {
        View::share('sharedMenus', []);
        View::share('sharedPages', []);

        if (env('APP_ENV') != 'install' AND Schema::hasTable('settings')) {
            $settings = $cache->rememberForever('settings', function () use ($settings) {
                return $settings->pluck('val', 'name')->all();
            });
            config()->set('settings', $settings);

            $menus = Cache::rememberForever('menus', function () {
                return Menu::where('status', 'active')
                    ->orderBy('sortable', 'asc')
                    ->limit(16)
                    ->get(['title', 'layout', 'route', 'url', 'icon', 'sortable'])
                    ->map(fn (Menu $menu) => $menu->only(['title', 'layout', 'route', 'url', 'icon', 'sortable']))
                    ->all();
            });
            View::share('sharedMenus', $menus);

            $pages = Cache::rememberForever('pages', function () {
                return Page::where('featured', 'active')
                    ->where('status', 'publish')
                    ->orderBy('id', 'desc')
                    ->limit(6)
                    ->get(['title', 'slug'])
                    ->map(fn (Page $page) => $page->only(['title', 'slug']))
                    ->all();
            });
            View::share('sharedPages', $pages);
        }
    }
}
