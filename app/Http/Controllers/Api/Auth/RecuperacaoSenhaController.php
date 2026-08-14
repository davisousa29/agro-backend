<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CodigoRecuperacao;
use App\Mail\CodigoRecuperacaoMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class RecuperacaoSenhaController extends Controller
{
    // ── 1. Solicitar código de recuperação ────────────────────────────────────
    public function solicitar(Request $request)
    {
        $request->merge(['email' => strtolower(trim($request->email ?? ''))]);

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Informe um email válido.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $email = $request->email;
        $user  = User::where('email', $email)->where('active', true)->first();

        // Só gera e envia se o usuário existir — mas a resposta é sempre a mesma
        if ($user) {
            // Cooldown: impede novo código antes de 3 minutos desde o último
            $ultimoCodigo = CodigoRecuperacao::where('email', $email)
                ->latest()
                ->first();

            if ($ultimoCodigo && $ultimoCodigo->created_at->diffInSeconds(now()) < 180) {
                $segundosRestantes = 180 - $ultimoCodigo->created_at->diffInSeconds(now());

                return response()->json([
                    'message'            => 'Aguarde antes de solicitar um novo código.',
                    'segundos_restantes' => $segundosRestantes,
                ], 429);
            }

            // Invalida códigos anteriores não usados deste email
            CodigoRecuperacao::where('email', $email)
                ->where('usado', false)
                ->update(['usado' => true]);

            // Gera código de 6 dígitos
            $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            CodigoRecuperacao::create([
                'email'      => $email,
                'codigo'     => Hash::make($codigo),
                'expira_em'  => now()->addMinutes(15),
                'usado'      => false,
                'tentativas' => 0,
            ]);

            Mail::to($email)->send(new CodigoRecuperacaoMail($codigo));
        }

        // Resposta genérica — não revela se o email existe
        return response()->json([
            'message' => 'Se este email estiver cadastrado, enviaremos um código de recuperação.',
        ]);
    }

    // ── 2. Validar código e redefinir senha ───────────────────────────────────
    public function redefinir(Request $request)
    {
        $request->merge(['email' => strtolower(trim($request->email ?? ''))]);

        $validator = Validator::make($request->all(), [
            'email'    => 'required|string|email',
            'codigo'   => 'required|string|size:6',
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)->mixedCase()->letters()->numbers()->symbols(),
            ],
        ], [
            'codigo.size'        => 'O código deve ter 6 dígitos.',
            'password.confirmed' => 'As senhas não coincidem.',
            'password.min'       => 'A senha deve ter no mínimo 8 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $email = $request->email;

        // Busca o código mais recente não usado deste email
        $registro = CodigoRecuperacao::where('email', $email)
            ->where('usado', false)
            ->latest()
            ->first();

        if (!$registro) {
            return response()->json([
                'message' => 'Código inválido ou expirado. Solicite um novo.',
            ], 422);
        }

        // Expirou?
        if (now()->greaterThan($registro->expira_em)) {
            return response()->json([
                'message' => 'Código expirado. Solicite um novo.',
            ], 422);
        }

        // Muitas tentativas erradas?
        if ($registro->tentativas >= 5) {
            $registro->update(['usado' => true]);
            return response()->json([
                'message' => 'Muitas tentativas. Solicite um novo código.',
            ], 429);
        }

        // Código confere?
        if (!Hash::check($request->codigo, $registro->codigo)) {
            $registro->increment('tentativas');
            return response()->json([
                'message' => 'Código incorreto.',
            ], 422);
        }

        // Tudo certo — redefine a senha
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Não foi possível redefinir a senha.',
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Marca o código como usado
        $registro->update(['usado' => true]);

        return response()->json([
            'message' => 'Senha redefinida com sucesso.',
        ]);
    }
}
