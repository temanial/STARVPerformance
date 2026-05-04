<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();

        if (!$currentUser || $currentUser->role !== 'admin') {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $managers = User::where('role', 'manager')
            ->with('filial')
            ->get();

        return response()->json($managers);
    }

    public function register(Request $request)
    {
        $currentUser = auth()->user();

        if (!$currentUser || $currentUser->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Только главный администратор может регистрировать сотрудников!'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'filial_id' => 'required|exists:filials,id' 
        ]);

        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'manager', 
            'filial_id' => $request->filial_id, 
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Менеджер успешно зарегистрирован и привязан к филиалу',
            'user' => $newUser->load('filial')
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $token = Auth::attempt($credentials);

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Неверный логин или пароль',
            ], 401);
        }

        $user = Auth::user();
        $user->load('filial');

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function me()
    {
        return response()->json(Auth::user()->load('filial'));
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Сессия успешно завершена',
        ]);
    }
}