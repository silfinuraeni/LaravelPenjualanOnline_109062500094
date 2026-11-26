<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingService;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:1000'],
            'shipping_service_id' => [
                'required',
                'integer',
                'exists:shipping_services,id',
            ],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                'exists:products,id',
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi checkout gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $order = DB::transaction(function () use ($request) {
                $user = $request->user();

                $shippingService = ShippingService::query()
                    ->whereKey($request->shipping_service_id)
                    ->where('is_active', true)
                    ->first();

                if (!$shippingService) {
                    throw ValidationException::withMessages([
                        'shipping_service_id' =>
                            'Layanan pengiriman tidak tersedia atau tidak aktif.',
                    ]);
                }

                $subtotal = 0;
                $orderItems = [];
                $products = [];

                foreach ($request->items as $item) {
                    $product = Product::query()
                        ->whereKey($item['product_id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$product || !$product->is_active) {
                        throw ValidationException::withMessages([
                            'items' =>
                                'Produk tidak ditemukan atau tidak aktif.',
                        ]);
                    }

                    $quantity = (int) $item['quantity'];

                    if ($product->stock < $quantity) {
                        throw ValidationException::withMessages([
                            'items' =>
                                "Stok produk {$product->name} tidak mencukupi.",
                        ]);
                    }

                    $price = (float) $product->price;
                    $lineTotal = $price * $quantity;
                    $subtotal += $lineTotal;

                    $products[] = [
                        'product' => $product,
                        'quantity' => $quantity,
                    ];

                    $orderItems[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'sku' => $product->sku,
                        'price' => $price,
                        'quantity' => $quantity,
                        'subtotal' => $lineTotal,
                    ];
                }

                $shippingCost = (float) $shippingService->cost;

                $order = Order::create([
                    'code' => 'ORD-' . now()->format('YmdHis')
                        . '-' . Str::upper(Str::random(5)),
                    'user_id' => $user->id,
                    'shipping_service_id' => $shippingService->id,
                    'recipient_name' => $request->recipient_name,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'shipping_courier' => $shippingService->courier,
                    'shipping_service' => $shippingService->service,
                    'shipping_eta' => $shippingService->eta,
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shippingCost,
                    'grand_total' => $subtotal + $shippingCost,
                    'status' => 'pending',
                    'ordered_at' => now(),
                ]);

                foreach ($products as $item) {
                    $product = $item['product'];
                    $quantity = $item['quantity'];

                    $orderItemData = collect($orderItems)->firstWhere(
                        'product_id',
                        $product->id
                    );

                    $orderItemData['order_id'] = $order->id;

                    OrderItem::create($orderItemData);

                    $product->decrement('stock', $quantity);

                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => $user->id,
                        'type' => 'OUT',
                        'quantity' => $quantity,
                        'unit_cost' => $product->price,
                        'reference' => $order->code,
                        'note' => 'Checkout API - ' . $order->code,
                        'movement_at' => now(),
                    ]);
                }

                return $order;
            });

            $order->load('items');

            return response()->json([
                'message' => 'Checkout API berhasil.',
                'data' => $order,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Checkout gagal.',
                'errors' => $e->errors(),
            ], 422);
        }
    }
}