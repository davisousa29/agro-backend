<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // ── Registro ──────────────────────────────────────────────────────────────

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'phone'    => 'nullable|string|max:20',
            'username' => 'required|string|max:30|unique:users|alpha_dash',
            'whatsapp' => 'nullable|string|max:20',
            'role'     => 'required|in:consultor,fazendeiro',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'username' => $request->username,
            'whatsapp' => $request->whatsapp,
            'role'     => $request->role,
            'password' => $request->password,
        ]);

        $token = auth()->login($user);

        return response()->json([
            'message' => 'Usuário criado com sucesso.',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }

    // ── Login ─────────────────────────────────────────────────────────────────

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $token = auth()->attempt([
            'email'    => $request->email,
            'password' => $request->password,
            'active'   => true,
        ]);

        if (!$token) {
            return response()->json([
                'message' => 'Email ou senha incorretos.',
            ], 401);
        }

        return response()->json([
            'message' => 'Login realizado com sucesso.',
            'user'    => auth()->user(),
            'token'   => $token,
        ]);
    }

    // ── Usuário autenticado ───────────────────────────────────────────────────

    public function me()
    {
        return response()->json([
            'user' => auth()->user(),
        ]);
    }

    // ── Logout ────────────────────────────────────────────────────────────────

    public function logout()
    {
        auth()->logout();

        return response()->json([
            'message' => 'Logout realizado com sucesso.',
        ]);
    }
}
