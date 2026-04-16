<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $authUser = $request->user();

        if (! $authUser instanceof User || ! $this->isAdmin($authUser)) {
            return response()->json([
                'message' => 'Acces refuse. Seul un administrateur peut lister les utilisateurs.',
            ], 403);
        }

        $users = User::query()
            ->select(['id', 'name', 'email', 'is_admin', 'role'])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $users,
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $authUser = $request->user();

        if (! $authUser instanceof User || ! $this->isAdmin($authUser)) {
            return response()->json([
                'message' => 'Acces refuse. Seul un administrateur peut modifier les utilisateurs.',
            ], 403);
        }

        $validated = $request->validate([
            'is_admin' => ['sometimes', 'boolean'],
            'role' => ['sometimes', 'string', 'in:admin,user'],
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255'],
        ]);

        if (array_key_exists('role', $validated) && ! array_key_exists('is_admin', $validated)) {
            $validated['is_admin'] = $validated['role'] === 'admin';
        }

        if (array_key_exists('is_admin', $validated) && ! array_key_exists('role', $validated)) {
            $validated['role'] = $validated['is_admin'] ? 'admin' : 'user';
        }

        // Prevent removing admin rights from the last administrator.
        if (
            $user->getKey() === $authUser->getKey()
            && array_key_exists('is_admin', $validated)
            && $validated['is_admin'] === false
            && User::query()->where('is_admin', true)->count() <= 1
        ) {
            return response()->json([
                'message' => 'Impossible de retirer le dernier administrateur.',
            ], 422);
        }

        $user->update($validated);

        return response()->json([
            'data' => $user->only(['id', 'name', 'email', 'is_admin', 'role']),
        ]);
    }

    private function isAdmin(User $user): bool
    {
        return (bool) $user->getAttribute('is_admin') || $user->getAttribute('role') === 'admin';
    }
}
