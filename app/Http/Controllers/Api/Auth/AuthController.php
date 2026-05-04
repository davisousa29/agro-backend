<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ── Registro ──────────────────────────────────────────────────────────────

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|min:3|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'phone'    => 'nullable|string|min:11|max:11',
            'username' => 'required|string|min:3|max:30|unique:users|alpha_dash',
            'whatsapp' => 'nullable|string|min:11|max:11',
            'role'     => 'required|in:consultor,fazendeiro',
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols(),
            ],
        ], [

            // ── Campos obrigatórios ─────────────────────────────
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O email é obrigatório.',
            'username.required' => 'O nome de usuário é obrigatório.',
            'password.required' => 'A senha é obrigatória.',
            'role.required' => 'O tipo de usuário é obrigatório.',

            // ── Formatos inválidos ──────────────────────────────
            'name.min' => 'O nome deve ter no mínimo 3 letras.',
            'email.email' => 'Informe um email válido.',
            'phone.min' => 'O número de telefone deve ter no mínimo 11 caracteres.',
            'phone.max' => 'O número de telefone deve ter no máximo 11 caracteres.',
            'whatsapp.min' => 'O número de whatsapp deve ter no mínimo 11 caracteres.',
            'whatsapp.max' => 'O número de whatsapp deve ter no máximo 11 caracteres.',
            'username.min' => 'O nome de usuário deve ter no mínimo 3 caracteres.',
            'username.alpha_dash' => 'O nome de usuário só pode conter letras, números, traços e underlines.',

            // ── Unicidade ───────────────────────────────────────
            'email.unique' => 'Não é possível utilizar este email.',
            'username.unique' => 'Não é possível utilizar este nome.',

            // ── Senha ───────────────────────────────────────────
            'password.confirmed' => 'As senhas não coincidem.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'password.mixed' => 'A senha deve conter letras maiúsculas e minúsculas.',
            'password.letters' => 'A senha deve conter pelo menos uma letra.',
            'password.numbers' => 'A senha deve conter pelo menos um número.',
            'password.symbols' => 'A senha deve conter pelo menos um caractere especial.',

            // ── Outros ──────────────────────────────────────────
            'role.in' => 'O tipo de usuário selecionado é inválido.',
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
            'password' => Hash::make($request->password),
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
