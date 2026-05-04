<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json(Product::with(['category', 'filials'])->get());
    }

    public function show($id)
    {
        $product = Product::with(['category', 'filials'])->find($id);
        if (!$product) return response()->json(['message' => 'Товар не найден'], 404);
        return response()->json($product);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
        ]);

        $product = Product::create($data);
        return response()->json(['message' => 'Товар добавлен', 'product' => $product], 201);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $product = Product::find($id);
        if (!$product) return response()->json(['message' => 'Товар не найден'], 404);

        $product->delete();
        return response()->json(['message' => 'Товар удален']);
    }

    public function updateStock(Request $request, $id)
    {
        $user = auth()->user();
        
        $request->validate([
            'quantity' => 'required|integer|min:0'
        ]);

        $product = Product::findOrFail($id);
        $filialId = null;

        if ($user->role === 'admin') {
            $request->validate(['filial_id' => 'required|exists:filials,id']);
            $filialId = $request->filial_id;
        } elseif ($user->role === 'manager') {
            $filialId = $user->filial_id;
        }

        if (!$filialId) {
            return response()->json(['message' => 'Нет привязки к филиалу'], 403);
        }

        $product->filials()->syncWithoutDetaching([
            $filialId => ['quantity' => $request->quantity]
        ]);

        return response()->json(['message' => 'Остатки успешно обновлены']);
    }
}