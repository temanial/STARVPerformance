<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    public function index()
    {
        return response()->json(Category::all());
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $request->validate([
            'name' => 'required|string|unique:categories,name'
        ]);

        $category = Category::create([
            'name' => $request->name
        ]);

        return response()->json([
            'message' => 'Категория успешно создана',
            'category' => $category
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Категория не найдена'], 404);
        }

        $request->validate([
            'name' => 'required|string|unique:categories,name,' . $id
        ]);

        $category->update([
            'name' => $request->name
        ]);

        return response()->json([
            'message' => 'Категория обновлена',
            'category' => $category
        ]);
    }

    public function destroy($id)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Категория не найдена'], 404);
        }

        $category->delete();

        return response()->json([
            'message' => 'Категория успешно удалена'
        ]);
    }
}