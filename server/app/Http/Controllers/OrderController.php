<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'manager'])) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $query = Order::with(['filial', 'items'])->orderBy('created_at', 'desc');

        if ($user->role === 'manager') {
            $query->where('filial_id', $user->filial_id);
        }

        $orders = $query->get();
        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'filial_id'      => 'required|exists:filials,id',
            'client_name'    => 'required|string',
            'client_phone'   => 'required|string',
            'vin_code'       => 'required|string|size:17',
            'scheduled_date' => 'required|date',
            'items'          => 'required|array',
        ]);

        try {
            $order = DB::transaction(function () use ($data) {
                
                $order = Order::create([
                    'filial_id'      => $data['filial_id'],
                    'status'         => 'new', 
                    'client_name'    => $data['client_name'],
                    'client_phone'   => $data['client_phone'],
                    'vin_code'       => strtoupper($data['vin_code']),
                    'scheduled_date' => $data['scheduled_date'],
                    'total_price'    => 0,
                ]);

                $total = 0;

                foreach ($data['items'] as $item) {
                    $price = 0;
                    $title = '';
                    $qty = $item['quantity'] ?? 1;

                    if ($item['type'] === 'product') {
                        $product = Product::findOrFail($item['id']);
                        
                        $filialStock = DB::table('filial_product')
                            ->where('product_id', $product->id)
                            ->where('filial_id', $data['filial_id'])
                            ->first();

                        if (!$filialStock || $filialStock->quantity < $qty) {
                            $currentStock = $filialStock->quantity ?? 0;
                            throw new Exception("Недостаточно товара '{$product->name}' в выбранном филиале (в наличии: {$currentStock}).");
                        }

                        DB::table('filial_product')
                            ->where('product_id', $product->id)
                            ->where('filial_id', $data['filial_id'])
                            ->decrement('quantity', $qty);

                        $price = $product->price;
                        $title = $product->name;
                        
                    } else {
                        $service = Service::whereHas('filials', function($query) use ($data) {
                            $query->where('filial_service.filial_id', $data['filial_id'])
                                  ->where('filial_service.is_active', true);
                        })->find($item['id']);

                        if (!$service) {
                            throw new Exception("Услуга с ID {$item['id']} недоступна или отключена в выбранном филиале.");
                        }

                        $price = $service->base_price;
                        $title = $service->name;
                    }

                    $total += $price * $qty;

                    OrderItem::create([
                        'order_id'      => $order->id,
                        'item_type'     => $item['type'],
                        'item_id'       => $item['id'],
                        'title_at_time' => $title,
                        'price_at_time' => $price,
                        'quantity'      => $qty
                    ]);
                }

                $order->update(['total_price' => $total]);

                return $order;
            });

            return response()->json([
                'message' => 'Заявка принята! Менеджер Starv Performance свяжется с вами.',
                'order_id' => $order->id
            ], 201);

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'manager'])) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $order = Order::find($id);
        if (!$order) return response()->json(['message' => 'Заказ не найден'], 404);

        $data = $request->validate([
            'status' => 'required|string', 
            'admin_comment' => 'nullable|string'
        ]);

        $newStatus = $data['status'];
        $currentStatus = $order->status;

        $allowedTransitions = [
            'new'             => ['waiting_parts', 'waiting_vehicle', 'in_progress', 'cancelled'], // Новая заявка
            'waiting_parts'   => ['waiting_vehicle', 'in_progress', 'cancelled'],                  // Ожидание комплектующих
            'waiting_vehicle' => ['in_progress', 'cancelled'],                                     // Ожидание ТС
            'in_progress'     => ['ready', 'cancelled'],                                           // Принято в работу
            'ready'           => ['completed'],                                                    // Ожидает выдачи (Отмена запрещена!)
            'completed'       => [],                                                               // Завершен (Тупик)
            'cancelled'       => []                                                                // Отменен (Тупик)
        ];

        if (!array_key_exists($currentStatus, $allowedTransitions)) {
            return response()->json(['message' => 'Текущий статус заказа неизвестен системе.'], 500);
        }

        if (!in_array($newStatus, $allowedTransitions[$currentStatus])) {
            return response()->json([
                'message' => "Недопустимый переход статуса.",
                'error' => "Нельзя перевести заказ из '{$currentStatus}' в '{$newStatus}'."
            ], 422);
        }

        $order->update([
            'status' => $newStatus,
            'admin_comment' => $data['admin_comment'] ?? $order->admin_comment
        ]);

        return response()->json([
            'message' => 'Статус заказа успешно обновлен', 
            'order' => $order
        ]);
    }
}