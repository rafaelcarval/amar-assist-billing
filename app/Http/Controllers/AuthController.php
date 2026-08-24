<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(
        LoginRequest $request
    ): JsonResponse {
        $credentials = $request->safe()->only([
            'email',
            'password',
        ]);

        $authenticated = Auth::guard('web')->attempt(
            $credentials,
            $request->boolean('remember')
        );

        if (! $authenticated) {
            throw ValidationException::withMessages([
                'email' => [
                    'E-mail ou senha inválidos.',
                ],
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();

        return response()->json([
            'message' => 'Autenticado com sucesso.',

            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function logout(
        Request $request
    ): JsonResponse {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Sessão encerrada com sucesso.',
        ]);
    }

    public function me(
        Request $request
    ): JsonResponse {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}