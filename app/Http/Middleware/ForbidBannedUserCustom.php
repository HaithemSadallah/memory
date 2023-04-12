<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Auth\Guard;

class ForbidBannedUserCustom
{
    protected $auth;

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->auth->user();
        if($user && $user->isBanned()){
            \Session::flush();

         /*   return redirect('login')->withInput()->withErrors([
                'email' => 'This account is blocked.',
            ]);*/
            return response([
                'message'=>'This account is blocked.'
            ]);
        }
        return $next($request);
    }
}
