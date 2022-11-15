<?php

namespace App\Http\Middleware;

use App\Models\Player;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlayerRefresh
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guest()) {
            return $next($request);
        }

        $player = Player::query()->find(Auth::id());
        if (!$player) {
            return $next($request);
        }
        $player->hp += time() - $player->refreshed_at;
        if ($player->hp > $player->hp_max) {
            $player->hp = $player->hp_max;
        }
        $player->refreshed_at = time();
        $player->save();

        return $next($request);
    }
}
