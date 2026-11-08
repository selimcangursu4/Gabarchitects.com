<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Panelden kapatılabilen modüller: ->middleware('module:real_estate')
 * Ayar: site_settings → modules.{modül}
 */
class EnsureModuleEnabled
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        abort_unless((bool) settings("modules.{$module}", false), 404);

        return $next($request);
    }
}
