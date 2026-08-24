<?php

use App\Http\Controllers\Api\ChargeController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Todas as rotas abaixo são protegidas pelo Laravel Sanctum.
| Apenas usuários autenticados poderão acessar clientes,
| contratos, cobranças e informações do usuário logado.
|
*/

Route::middleware('auth:sanctum')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Usuário autenticado
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/user',
            [AuthController::class, 'me']
        );

        /*
        |--------------------------------------------------------------------------
        | Clientes
        |--------------------------------------------------------------------------
        */

        Route::prefix('customers')
            ->group(function () {

                Route::get(
                    '/',
                    [CustomerController::class, 'index']
                );

                Route::post(
                    '/',
                    [CustomerController::class, 'store']
                );

                Route::get(
                    '/{customer}',
                    [CustomerController::class, 'show']
                );

                Route::put(
                    '/{customer}',
                    [CustomerController::class, 'update']
                );

                Route::patch(
                    '/{customer}/status',
                    [CustomerController::class, 'changeStatus']
                );

                /*
                |--------------------------------------------------------------------------
                | Contratos do cliente
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/{customer}/contracts',
                    [ContractController::class, 'index']
                );

                Route::post(
                    '/{customer}/contracts',
                    [ContractController::class, 'store']
                );
            });

        /*
        |--------------------------------------------------------------------------
        | Cobranças
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/charges',
            [ChargeController::class, 'index']
        );

        Route::post(
            '/contracts/{contract}/charges',
            [ChargeController::class, 'store']
        );

        Route::patch(
            '/charges/{charge}/pay',
            [ChargeController::class, 'markAsPaid']
        );
    });