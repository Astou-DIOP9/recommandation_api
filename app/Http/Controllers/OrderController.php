<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $authUser = $request->user();

        if (! $authUser instanceof User) {
            return response()->json(['message' => 'Authentification requise.'], 401);
        }

        $query = Order::query()->with(['items.product']);

        if (! $this->isAdmin($authUser)) {
            $query->where('user_id', $authUser->getKey());
        }

        $orders = $query->latest('id')->get()->map(function (Order $order) {
            return $this->toHistoryPayload($order);
        });

        return response()->json([
            'data' => $orders,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $authUser = $request->user();

        if (! $authUser instanceof User) {
            return response()->json(['message' => 'Authentification requise.'], 401);
        }

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $order = Order::create([
            'user_id' => $authUser->getKey(),
            'status' => 'confirmed',
        ]);

        foreach ($validated['items'] as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantite' => $item['quantity'],
            ]);
        }

        $order->load(['items.product']);

        return response()->json([
            'data' => $this->toHistoryPayload($order),
        ], 201);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $authUser = $request->user();

        if (! $authUser instanceof User) {
            return response()->json(['message' => 'Authentification requise.'], 401);
        }

        if (! $this->isAdmin($authUser) && $order->getAttribute('user_id') !== $authUser->getKey()) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        $order->load(['items.product']);

        return response()->json([
            'data' => $this->toHistoryPayload($order),
        ]);
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $authUser = $request->user();

        if (! $authUser instanceof User) {
            return response()->json(['message' => 'Authentification requise.'], 401);
        }

        if (! $this->isAdmin($authUser) && $order->getAttribute('user_id') !== $authUser->getKey()) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,confirmed,cancelled'],
        ]);

        $order->update([
            'status' => $validated['status'],
        ]);

        $order->load(['items.product']);

        return response()->json([
            'data' => $this->toHistoryPayload($order),
        ]);
    }

    public function destroy(Request $request, Order $order): JsonResponse
    {
        $authUser = $request->user();

        if (! $authUser instanceof User) {
            return response()->json(['message' => 'Authentification requise.'], 401);
        }

        if (! $this->isAdmin($authUser) && $order->getAttribute('user_id') !== $authUser->getKey()) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        $order->delete();

        return response()->json([
            'message' => 'Commande supprimee.',
        ]);
    }

    private function isAdmin(User $user): bool
    {
        return (bool) $user->getAttribute('is_admin') || $user->getAttribute('role') === 'admin';
    }

    private function toHistoryPayload(Order $order): array
    {
        $orderItems = $order->items()->with('product')->get();
        $buyer = $order->user()->first();

        $items = $orderItems->map(function ($item) {
            return [
                'product_id' => $item->getAttribute('product_id'),
                'quantity' => (int) $item->getAttribute('quantite'),
                'product' => $item->product,
            ];
        })->values();

        $itemsCount = $items->sum('quantity');
        $total = $orderItems->sum(function ($item) {
            $price = $item->product?->getAttribute('discount_price') ?? $item->product?->getAttribute('price') ?? 0;
            return ((int) $item->getAttribute('quantite')) * (float) $price;
        });

        return [
            'id' => $order->getKey(),
            'status' => (string) $order->getAttribute('status'),
            'created_at' => optional($order->getAttribute('created_at'))->toISOString() ?? now()->toISOString(),
            'items_count' => $itemsCount,
            'total' => $total,
            'items' => $items,
            'user_id' => $order->getAttribute('user_id'),
            'user' => $buyer
                ? [
                    'id' => $buyer->getKey(),
                    'name' => (string) $buyer->getAttribute('name'),
                    'email' => (string) $buyer->getAttribute('email'),
                ]
                : null,
        ];
    }
}
