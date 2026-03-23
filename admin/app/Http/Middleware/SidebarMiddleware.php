<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Menu;

class SidebarMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $sidebarMenuData = Menu::with([
            'children' => function ($query) {
                $query->with('children');
            }
        ])->where('parent_id', null)->get()->toArray();

        if(auth()->user()->role_id != 1){

            if (!empty($sidebarMenuData)) {

                foreach ($sidebarMenuData as $key => &$menu) {

                    if (!empty($menu['children'])) {

                        foreach ($menu['children'] as $key1 => &$child) {

                            if (!empty($child['children'])) {

                                foreach ($child['children'] as $key2 => $subChild) {

                                    $hasPermission = Permission::where('role_id', auth()->user()->role_id)
                                        ->where('slug', $subChild['slug'])
                                        ->where('action', 'read')
                                        ->exists();
    
                                    if (!$hasPermission) {
                                        unset($child['children'][$key2]);
                                    }
                                }
    
                                // Remove child if all sub-children are gone
                                if (empty($child['children'])) {
                                    unset($menu['children'][$key1]);
                                }

                            } else {

                                $hasPermission = Permission::where('role_id', auth()->user()->role_id)
                                    ->where('slug', $child['slug'])
                                    ->where('action', 'read')
                                    ->exists();
    
                                if (!$hasPermission) {
                                    unset($menu['children'][$key1]);
                                }
                            }
                        }
    
                        // Remove menu if all children are gone
                        if (empty($menu['children'])) {
                            unset($sidebarMenuData[$key]);
                        }

                    } else {

                        $hasPermission = Permission::where('role_id', auth()->user()->role_id)
                            ->where('slug', $menu['slug'])
                            ->where('action', 'read')
                            ->exists();
    
                        if (!$hasPermission) {
                            unset($sidebarMenuData[$key]);
                        }
                    }
                }

                unset($menu, $child); // break references
            }
        }

        view()->share('sidebarMenuData', $sidebarMenuData);

        return $next($request);
    }
}
