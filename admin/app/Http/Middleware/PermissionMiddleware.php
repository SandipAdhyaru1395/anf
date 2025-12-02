<?php

namespace App\Http\Middleware;

use App\Models\Permission;

use Brian2694\Toastr\Facades\Toastr;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,$permission=null): Response
    {
        $default_permission=[
            'dashboard.read',
            'profile-user.read'
        ];

        if (auth()->user()->role_id != 1) {

            if (auth()->user()->role_id == null) {

                if (!in_array($permission, $default_permission)) {
                    Toastr::error('Permission denied!');
                    return redirect()->back();
                }

            } else {

                if (!in_array($permission, $default_permission)) {
                    
                    $hasPermission = Permission::where('role_id', auth()->user()->role_id)
                        ->where('route', $permission)->exists();

                    if (!$hasPermission) {

                        Toastr::error('Permission denied!');
                        return redirect()->back();
                    }
                }
            }
        }

        
        return $next($request);
    }
}
