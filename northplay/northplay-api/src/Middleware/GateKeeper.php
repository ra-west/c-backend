<?php
namespace Northplay\NorthplayApi\Middleware;

use Closure;
use Illuminate\Http\Request;

class GateKeeper
{
     // Blocked IP addresses
     public function restrictedRoutes(){

        return [


        ];
     }

     public function restrictedEnabled() {
        return true;
     }

     /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
       /*     if (request()->is('*admin*')) {
               if(auth()->guest()) {
                  return response()->errorApi('access restricted', 403);
               }
            }
            if (request()->is('/')) {
                  return redirect('https://front.northplay.online/');
            }
*/

            return $next($request);
    }
}