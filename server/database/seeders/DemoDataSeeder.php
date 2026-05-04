<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Service;
use App\Models\Filial;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Создаем филиал[cite: 7, 19]
        $filial = Filial::create([
            'city' => 'Москва',
            'address' => 'ул. Тюнинговая, 1',
            'phone' => '+7 (999) 000-00-00',
            'is_active' => true,
        ]);

        // 2. Создаем услуги (одну активную, одну нет)[cite: 2, 11, 18, 19]
        $activeService = Service::create([
            'name' => 'Чип-тюнинг Stage 1',
            'base_price' => 25000.00,
            'description' => 'Увеличение мощности программным методом.',
        ]);

        $inactiveService = Service::create([
            'name' => 'Установка обвеса',
            'base_price' => 15000.00,
            'description' => 'Профессиональный монтаж элементов экстерьера.',
        ]);

        // Привязываем их к филиалу с разными статусами[cite: 11, 19]
        $filial->services()->attach($activeService->id, ['is_active' => true]);
        $filial->services()->attach($inactiveService->id, ['is_active' => false]);

        // 3. Создаем категорию и товар[cite: 4, 10, 14, 20]
        $exterior = Category::create(['name' => 'Внешний тюнинг']);
        $product = Product::create([
            'category_id' => $exterior->id,
            'name' => 'Карбоновый диффузор BMW X5',
            'brand' => 'Starv Performance',
            'price' => 32000.00,
            'stock_quantity' => 10,
        ]);
        
        $filial->products()->attach($product->id, ['quantity' => 5]); //[cite: 12, 19, 20]

        // 4. Создаем демонстрационный заказ[cite: 5, 16]
        $order = Order::create([
            'filial_id' => $filial->id,
            'status' => 'pending',
            'client_name' => 'Александр Иванов',
            'client_phone' => '+7 (900) 111-22-33',
            'vin_code' => 'WBA00000000000000',
            'total_price' => 57000.00, // (25000 услуга + 32000 товар)
            'scheduled_date' => now()->addDays(3),
            'admin_comment' => 'Клиент хочет установить диффузор сразу после прошивки.',
        ]);

        // 5. Добавляем позиции в заказ (снапшоты данных)
        OrderItem::create([
            'order_id' => $order->id,
            'item_type' => 'service',
            'item_id' => $activeService->id,
            'title_at_time' => $activeService->name,
            'price_at_time' => 25000.00,
            'quantity' => 1,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'item_type' => 'product',
            'item_id' => $product->id,
            'title_at_time' => $product->name,
            'price_at_time' => 32000.00,
            'quantity' => 1,
        ]);
    }
}