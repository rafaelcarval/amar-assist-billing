<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        /*
         * Rotas da API nunca devem redirecionar para HTML.
         * Usuários não autenticados recebem HTTP 401.
         */
        if ($request->is('api/*')) {
            return null;
        }

        /*
         * Para páginas web, redirecionamos para
         * a tela de login da SPA Vue.
         */
        if (! $request->expectsJson()) {
            return url('/app/login');
        }

        return null;
    }
}