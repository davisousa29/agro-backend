<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Google\Client as GoogleClient;

class GoogleAuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'access_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Token do Google ausente.',
            ], 422);
        }

        // ── Busca os dados do usuário no Google usando o access token ──────────
        $googleUser = $this->buscarUsuarioGoogle($request->access_token);

        if (!$googleUser || empty($googleUser['email'])) {
            return response()->json([
                'message' => 'Não foi possível validar o login com o Google.',
            ], 401);
        }

        $email    = strtolower(trim($googleUser['email']));
        $googleId = $googleUser['id'] ?? $googleUser['sub'] ?? null;
        $nome     = $googleUser['name'] ?? '';
        $avatar   = $googleUser['picture'] ?? null;

        // ── 1. Já existe usuário com esse google_id? (login normal) ────────────
        $user = User::where('google_id', $googleId)->first();

        // ── 2. Não achou por google_id — tenta por email (vínculo automático) ──
        if (!$user) {
            $user = User::where('email', $email)->first();

            if ($user) {
                // Vincula a conta existente ao Google
                $user->google_id     = $googleId;
                $user->auth_provider = $user->auth_provider === 'email' ? 'email' : 'google';
                if (!$user->avatar_url && $avatar) {
                    $user->avatar_url = $avatar;
                }
                $user->save();
            }
        }

        // ── 3. Não achou de jeito nenhum — cria cadastro prévio ────────────────
        if (!$user) {
            $user = User::create([
                'name'          => $nome,
                'email'         => $email,
                'username'      => null,
                'role'          => 'consultor',
                'password'      => null,
                'google_id'     => $googleId,
                'auth_provider' => 'google',
                'avatar_url'    => $avatar,
                'active'        => true,
            ]);
        }

        // ── Gera o token JWT do nosso sistema ──────────────────────────────────
        $token = auth()->login($user);

        return response()->json([
            'message'          => 'Login com Google realizado com sucesso.',
            'user'             => $user,
            'token'            => $token,
            'cadastro_completo'=> !empty($user->username),
        ]);
    }

    // ── Consulta a API do Google para obter os dados do usuário ────────────────
    private function buscarUsuarioGoogle(string $accessToken): ?array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::withToken($accessToken)
                ->get('https://www.googleapis.com/oauth2/v2/userinfo');

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Throwable $e) {
            // silencioso — retorna null
        }

        return null;
    }
}
