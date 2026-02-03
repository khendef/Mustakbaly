<?php

namespace Modules\UserManagementModule\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GetRequestedOrganization
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Check if the URL has an {organization} parameter
        if ($request->route('organization')) {
            
            // 2. Fetch the organization (Laravel automatically resolves slugs)
            $org = $request->route('organization');

            // 3. Store the Org ID in the 'config' or a Singleton so the Scope can see it
            config(['app.current_organization_id' => $org]);

            // 4. Remove 'organization' from the route parameters
            // This prevents it from being passed to the controller method
            $request->route()->forgetParameter('organization');
        }
        return $next($request);
    }
}
