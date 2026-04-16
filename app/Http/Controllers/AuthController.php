<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->merge([
            'name' => $request->input('name')
                ?? $request->input('full_name')
                ?? $request->input('fullName')
                ?? $request->input('nom')
                ?? $request->input('nom_complet')
                ?? $request->input('nomComplet'),
            'password_confirmation' => $request->input('password_confirmation')
                ?? $request->input('confirm_password')
                ?? $request->input('confirmPassword')
                ?? $request->input('passwordConfirm')
                ?? $request->input('passwordConfirmation'),
        ]);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Les donnees fournies sont invalides.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $plainToken = Str::random(64);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'api_token' => hash('sha256', $plainToken),
        ]);

        return response()->json([
            'message' => 'Inscription reussie',
            'token' => $plainToken,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->merge([
            'email' => $request->input('email') ?? $request->input('username'),
            'password' => $request->input('password') ?? $request->input('mot_de_passe') ?? $request->input('motDePasse'),
        ]);

        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Les donnees fournies sont invalides.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Identifiants invalides.',
                'errors' => [
                    'email' => ['Identifiants invalides.'],
                ],
            ], 401);
        }

        $plainToken = Str::random(64);
        $user->forceFill([
            'api_token' => hash('sha256', $plainToken),
        ])->save();

        return response()->json([
            'message' => 'Connexion reussie',
            'token' => $plainToken,
            'user' => $user,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            $user->forceFill([
                'api_token' => null,
            ])->save();
        }

        return response()->json([
            'message' => 'Deconnexion reussie',
        ]);
    }
}
