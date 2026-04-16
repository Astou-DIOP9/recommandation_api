<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Récupère ou crée le panier (order pending) de l'utilisateur connecté.
     */
    private function getOrCreateCart(Request $request): Order
    {
        $user = $request->user();

        return Order::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'pending'],
        );
    }

    /**
     * GET /api/cart
     * Retourne le panier avec ses articles et les détails produit.
     */
    public function index(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request);
        $cart->load(['items.product.category']);

        $total = $cart->items->sum(fn($item) => $item->quantite * $item->product->price);

        return response()->json([
            'cart' => $cart,
            'total' => $total,
            'total_fcfa' => number_format($total, 0, ',', ' ') . ' FCFA',
            'items_count' => $cart->items->sum('quantite'),
        ]);
    }

    /**
     * POST /api/cart
     * Ajoute ou incrémente un produit dans le panier.
     * Body: { product_id, quantite (optionnel, défaut 1) }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantite' => ['sometimes', 'integer', 'min:1'],
        ]);

        $cart = $this->getOrCreateCart($request);
        $quantite = $validated['quantite'] ?? 1;

        $item = $cart->items()->where('product_id', $validated['product_id'])->first();

        if ($item) {
            $item->increment('quantite', $quantite);
        } else {
            $cart->items()->create([
                'product_id' => $validated['product_id'],
                'quantite' => $quantite,
            ]);
        }

        $cart->load(['items.product.category']);
        $total = $cart->items->sum(fn($item) => $item->quantite * $item->product->price);

        return response()->json([
            'message' => 'Produit ajouté au panier',
            'cart' => $cart,
            'total' => $total,
            'total_fcfa' => number_format($total, 0, ',', ' ') . ' FCFA',
            'items_count' => $cart->items->sum('quantite'),
        ], 201);
    }

    /**
     * PATCH /api/cart/{item}
     * Met à jour la quantité d'un article du panier.
     * Body: { quantite }
     */
    public function update(Request $request, OrderItem $item): JsonResponse
    {
        $this->authorizeCartItem($request, $item);

        $validated = $request->validate([
            'quantite' => ['required', 'integer', 'min:1'],
        ]);

        $item->update(['quantite' => $validated['quantite']]);

        $cart = $item->order->load(['items.product.category']);
        $total = $cart->items->sum(fn($i) => $i->quantite * $i->product->price);

        return response()->json([
            'message' => 'Quantité mise à jour',
            'cart' => $cart,
            'total' => $total,
            'total_fcfa' => number_format($total, 0, ',', ' ') . ' FCFA',
            'items_count' => $cart->items->sum('quantite'),
        ]);
    }

    /**
     * DELETE /api/cart/{item}
     * Supprime un article du panier.
     */
    public function destroy(Request $request, OrderItem $item): JsonResponse
    {
        $this->authorizeCartItem($request, $item);

        $order = $item->order;
        $item->delete();

        $order->load(['items.product.category']);
        $total = $order->items->sum(fn($i) => $i->quantite * $i->product->price);

        return response()->json([
            'message' => 'Article supprimé du panier',
            'cart' => $order,
            'total' => $total,
            'total_fcfa' => number_format($total, 0, ',', ' ') . ' FCFA',
            'items_count' => $order->items->sum('quantite'),
        ]);
    }

    /**
     * POST /api/cart/checkout
     * Valide le panier → passe la commande en statut "confirmed".
     */
    public function checkout(Request $request): JsonResponse
    {
        $cart = Order::where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->with('items.product')
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Votre panier est vide.'], 422);
        }

        $cart->update(['status' => 'confirmed']);

        return response()->json([
            'message' => 'Commande confirmée avec succès',
            'order' => $cart,
        ]);
    }

    /**
     * DELETE /api/cart
     * Vide entièrement le panier.
     */
    public function clear(Request $request): JsonResponse
    {
        $cart = Order::where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->first();

        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json(['message' => 'Panier vidé']);
    }

    private function authorizeCartItem(Request $request, OrderItem $item): void
    {
        $order = $item->order;

        abort_if(
            $order->user_id !== $request->user()->id || $order->status !== 'pending',
            403,
            'Action non autorisée.'
        );
    }
}
