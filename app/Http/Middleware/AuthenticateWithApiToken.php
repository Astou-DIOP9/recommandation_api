<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWithApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainToken = $request->bearerToken();

        if (! $plainToken) {
            return response()->json([
                'message' => 'Token manquant.',
            ], 401);
        }

        $user = User::where('api_token', hash('sha256', $plainToken))->first();

        if (! $user) {
            return response()->json([
                'message' => 'Token invalide.',
            ], 401);
        }

        $request->setUserResolver(fn() => $user);

        return $next($request);
    }
}
