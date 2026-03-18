<?php

namespace App\Http\Middleware;

use App\Models\Organisation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveOrganisationContext
{
    /**
     * Handle an incoming request involving specific organisations.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // For routes like /api/organisations/{organisation}/*
        $organisation = $request->route('organisation');

        if (! $organisation || ! auth()->check()) {
            return $next($request);
        }

        if (is_string($organisation) || is_int($organisation)) {
            $organisation = Organisation::find($organisation);
        }

        if (! $organisation) {
            return $next($request);
        }

        // 1. Is the Organisation itself active?
        if ($organisation->status !== 'active') {
            return response()->json([
                'success' => false,
                'error' => 'This organisation has been suspended by administration.',
                'code' => 'ORGANISATION_SUSPENDED',
            ], 403);
        }

        // 2. Is this specific User's membership in the Organisation active?
        $user = auth()->user();
        if ($user->role !== 'admin') {
            $membership = $organisation->members()->where('user_id', $user->id)->first();

            if ($membership && $membership->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'error' => 'Your membership to this organisation has been suspended.',
                    'code' => 'ORGANISATION_MEMBER_SUSPENDED',
                ], 403);
            }
        }

        return $next($request);
    }
}
