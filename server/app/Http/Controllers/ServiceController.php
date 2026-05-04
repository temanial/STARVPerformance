<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Filial;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $filialId = $request->query('filial_id');

        if ($filialId) {
            $services = Service::whereHas('filials', function($query) use ($filialId) {
                $query->where('filial_service.filial_id', $filialId)
                      ->where('filial_service.is_active', true);
            })->get();

            return response()->json($services);
        }

        return response()->json(Service::with('filials')->get());
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'required|numeric',
            'description' => 'nullable|string',
        ]);

        $service = Service::create($data);
        
        $allFilialIds = Filial::pluck('id')->toArray();

        $attachData = [];
        foreach ($allFilialIds as $id) {
            $attachData[$id] = ['is_active' => true];
        }

        $service->filials()->attach($attachData);

        return response()->json([
            'message' => 'Услуга создана и активирована во всех филиалах', 
            'service' => $service->load('filials')
        ], 201);
    }

    public function toggleActive(Request $request, $serviceId)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'manager'])) {
            return response()->json(['message' => 'Недостаточно прав'], 403);
        }

        $request->validate([
            'filial_id' => 'required|exists:filials,id',
            'is_active' => 'required|boolean'
        ]);

        $service = Service::findOrFail($serviceId);
        
        $service->filials()->updateExistingPivot($request->filial_id, [
            'is_active' => $request->is_active
        ]);

        return response()->json([
            'message' => 'Статус услуги для филиала изменен',
            'is_active' => (bool)$request->is_active
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $service = Service::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'base_price' => 'sometimes|numeric',
            'description' => 'nullable|string',
        ]);

        $service->update($data);

        return response()->json([
            'message' => 'Данные услуги обновлены', 
            'service' => $service->load('filials')
        ]);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $service = Service::findOrFail($id);
        $service->delete();

        return response()->json(['message' => 'Услуга полностью удалена из системы']);
    }
}