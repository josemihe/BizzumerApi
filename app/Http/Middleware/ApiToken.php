<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class ApiToken
{
    public function handle($request, Closure $next)
    {
        $token = $request->header('Authorization');
        if (Str::startsWith($token, 'Bearer ')) {
            $token = substr($token, 7);
        }
        $token_parts = explode('|', $token);
        $tokenId = $token_parts[0];
        if (DB::table('personal_access_tokens')->where('id', $tokenId)->exists()) {
            $user_id = DB::table('personal_access_tokens')
                ->where('id', $tokenId)
                ->value('tokenable_id');
            $user = User::find($user_id);
            $request->merge(['user' => $user]);
            return $next($request);
        }
        return response()->json('Unauthorized', 401);
    }
}
