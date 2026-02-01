<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para prevenir acceso a rutas centrales desde dominios de tenant.
 * 
 * Este middleware asegura que las rutas centrales (registro de tenants, usuarios centrales, etc.)
 * solo sean accesibles desde el dominio central y no desde los dominios de tenant.
 */
class PreventAccessFromTenantDomains
{
    public function handle(Request $request, Closure $next): Response
    {
        $currentHost = $request->getHost();
        $centralDomains = config('tenancy.central_domains', []);

        // Si el host actual NO está en la lista de dominios centrales, es un tenant
        if (! in_array($currentHost, $centralDomains, true)) {
            abort(404, 'Central routes are not accessible from tenant domains.');
        }

        return $next($request);
    }
}
