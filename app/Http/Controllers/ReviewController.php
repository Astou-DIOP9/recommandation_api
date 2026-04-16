<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function indexByProduct(Request $request, Product $product): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min($perPage, 50));

        $sort = (string) $request->query('sort', 'newest');

        $query = Review::query()
            ->where('product_id', $product->id)
            ->with('user:id,name');

        if ($sort === 'oldest') {
            $query->orderBy('created_at');
        } else {
            $query->orderByDesc('created_at');
        }

        $reviews = $query->paginate($perPage);

        $reviews->getCollection()->transform(function (Review $review) {
            return [
                'id' => $review->id,
                'user_id' => $review->user_id,
                'product_id' => $review->product_id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'user_name' => $review->user?->name ?? 'Utilisateur',
                'user_avatar' => null,
                'created_at' => optional($review->created_at)->toISOString(),
            ];
        });

        return response()->json($reviews);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:2000'],
        ]);

        $user = $request->user();

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        $review->load('user:id,name');

        return response()->json([
            'data' => [
                'id' => $review->id,
                'user_id' => $review->user_id,
                'product_id' => $review->product_id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'user_name' => $review->user?->name ?? 'Utilisateur',
                'user_avatar' => null,
                'created_at' => optional($review->created_at)->toISOString(),
            ],
        ], 201);
    }

    public function destroy(Request $request, Review $review): JsonResponse
    {
        $user = $request->user();

        $isOwner = $review->user_id === $user->id;
        $isAdmin = $user->is_admin === true || $user->is_admin === 1;

        if (! $isOwner && ! $isAdmin) {
            return response()->json([
                'message' => 'Action non autorisee.',
            ], 403);
        }

        $review->delete();

        return response()->json([
            'message' => 'Avis supprime.',
        ]);
    }
}
