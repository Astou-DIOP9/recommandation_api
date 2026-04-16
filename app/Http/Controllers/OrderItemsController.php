<?php

namespace App\Http\Controllers;

use App\Models\order_items;
use Illuminate\Http\Request;

class OrderItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = order_items::query()
            ->with(['order', 'product'])
            ->latest('id')
            ->paginate(20);

        return response()->json($items);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantite' => ['nullable', 'numeric', 'min:0.01'],
        ]);

        $item = order_items::create([
            'order_id' => $validated['order_id'],
            'product_id' => $validated['product_id'],
            'quantite' => $validated['quantite'] ?? 1,
        ]);

        return response()->json([
            'message' => 'Order item created',
            'data' => $item->load(['order', 'product']),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(order_items $order_items)
    {
        return response()->json($order_items->load(['order', 'product']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(order_items $order_items)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, order_items $order_items)
    {
        $validated = $request->validate([
            'order_id' => ['sometimes', 'integer', 'exists:orders,id'],
            'product_id' => ['sometimes', 'integer', 'exists:products,id'],
            'quantite' => ['sometimes', 'numeric', 'min:0.01'],
        ]);

        $order_items->update($validated);

        return response()->json([
            'message' => 'Order item updated',
            'data' => $order_items->load(['order', 'product']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(order_items $order_items)
    {
        $order_items->delete();

        return response()->json([
            'message' => 'Order item deleted',
        ]);
    }
}
