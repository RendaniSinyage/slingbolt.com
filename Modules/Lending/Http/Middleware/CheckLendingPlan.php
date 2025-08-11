<?php

namespace Modules\Lending\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Plan;

class CheckLendingPlan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if ($user && $user->type == 'company') {
            $plan = Plan::find($user->plan);
            if ($plan && $plan->lending == 1) {
                return $next($request);
            }
        }

        // Also allow super admin
        if ($user && $user->type == 'super admin') {
            return $next($request);
        }

        return redirect()->route('dashboard')->with('error', __('You do not have access to the Lending module.'));
    }
}
