<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Menu;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;

class MenuProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (Schema::hasTable('menus')) {

            $menuData = Menu::with([
            'children' => function ($query) {
                $query->with('children');
            }
            ])->where('parent_id', null)->get()->toArray();
            

            view()->share('menuData', $menuData);
        }

    }
}
