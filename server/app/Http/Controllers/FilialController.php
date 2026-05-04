<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use Illuminate\Http\Request;

class FilialController extends Controller
{
    public function index()
    {
        return response()->json(Filial::where('is_active', true)->get());
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'У вас нет прав для создания филиала'], 403);
        }

        $data = $request->validate([
            'address' => 'required|string',
            'city' => 'required|string',
            'phone' => 'required|string',
        ]);

        $filial = Filial::create($data);

        return response()->json([
            'message' => 'Филиал Starv Performance успешно добавлен',
            'filial' => $filial
        ], 201);
    }

    public function destroy($id)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Удаление доступно только администратору'], 403);
        }

        $filial = Filial::find($id);
        
        if (!$filial) {
            return response()->json(['message' => 'Филиал не найден'], 404);
        }

        $filial->delete();

        return response()->json(['message' => 'Филиал удален']);
    }
}