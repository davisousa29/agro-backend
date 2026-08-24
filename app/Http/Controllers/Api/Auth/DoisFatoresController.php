<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Configuracao2fa;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;

class DoisFatoresController extends Controller
{
    // ── 1. Inicia a ativação: gera segredo, QR code e chave manual ────────────
    public function gerar(Request $request)
    {
        $user = auth()->user();

        // Se já tem 2FA ativo e confirmado, não deixa gerar de novo
        $config = $user->configuracao2fa;
        if ($config && $config->ativo && $config->confirmado_em) {
            return response()->json([
                'message' => 'A autenticação de dois fatores já está ativa.',
            ], 422);
        }

        $google2fa = new Google2FA();

        // Gera o segredo único
        $secret = $google2fa->generateSecretKey();

        // Cria ou atualiza a configuração (ainda NÃO ativa — só após confirmar)
        $config = Configuracao2fa::updateOrCreate(
            ['user_id' => $user->id],
            [
                'secret'        => $secret,
                'ativo'         => false,
                'confirmado_em' => null,
            ]
        );

        // Monta a URL do QR code (otpauth://)
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            'Colchete',           // nome do app (aparece no autenticador)
            $user->email,         // identifica a conta no autenticador
            $secret
        );

        return response()->json([
            'message'      => 'Escaneie o QR code ou insira a chave manualmente.',
            'qr_code_url'  => $qrCodeUrl,
            'chave_manual' => $secret,
        ]);
    }

    // ── 2. Confirma a ativação validando um código do autenticador ────────────
    public function confirmar(Request $request)
    {
        $user = auth()->user();

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'codigo' => 'required|string|size:6',
        ], [
            'codigo.size' => 'O código deve ter 6 dígitos.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Código inválido.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $config = $user->configuracao2fa;

        if (!$config || !$config->secret) {
            return response()->json([
                'message' => 'Gere o QR code antes de confirmar.',
            ], 422);
        }

        if ($config->ativo && $config->confirmado_em) {
            return response()->json([
                'message' => 'A autenticação de dois fatores já está ativa.',
            ], 422);
        }

        // Valida o código contra o segredo
        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $valido = $google2fa->verifyKey($config->secret, $request->codigo);

        if (!$valido) {
            return response()->json([
                'message' => 'Código incorreto. Verifique o autenticador e tente novamente.',
            ], 422);
        }

        // Gera os códigos de recuperação/backup (8 códigos de uso único)
        $codigosRecuperacao = $this->gerarCodigosRecuperacao();

        // Ativa o 2FA
        $config->update([
            'ativo'               => true,
            'metodo'              => 'authenticator',
            'confirmado_em'       => now(),
            'codigos_recuperacao' => $codigosRecuperacao,
        ]);

        return response()->json([
            'message'              => 'Autenticação de dois fatores ativada com sucesso.',
            'codigos_recuperacao'  => $codigosRecuperacao,
        ]);
    }

    // ── Gera 8 códigos de recuperação únicos ──────────────────────────────────
    private function gerarCodigosRecuperacao(): array
    {
        $codigos = [];
        for ($i = 0; $i < 8; $i++) {
            // Formato: XXXX-XXXX (8 caracteres alfanuméricos)
            $codigos[] = strtoupper(
                substr(bin2hex(random_bytes(4)), 0, 4) . '-' .
                substr(bin2hex(random_bytes(4)), 0, 4)
            );
        }
        return $codigos;
    }

    // ── 3. Desativa o 2FA (exige senha por segurança) ─────────────────────────
    public function desativar(Request $request)
    {
        $user = auth()->user();

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Informe sua senha para desativar.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Confirma a senha antes de desativar (proteção contra sessão sequestrada)
        if (!$user->password || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Senha incorreta.',
            ], 422);
        }

        $config = $user->configuracao2fa;

        if (!$config || !$config->ativo) {
            return response()->json([
                'message' => 'A autenticação de dois fatores não está ativa.',
            ], 422);
        }

        // Remove a configuração por completo
        $config->delete();

        return response()->json([
            'message' => 'Autenticação de dois fatores desativada.',
        ]);
    }

    // ── LOGIN 2FA - Passo A: enviar código por email (método email) ───────────
    public function enviarCodigoEmail(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Email inválido.'], 422);
        }

        $email = strtolower(trim($request->email));
        $user  = \App\Models\User::where('email', $email)->where('active', true)->first();

        // Resposta genérica mesmo se não achar (não revela se existe)
        if (!$user || !$user->configuracao2fa || !$user->configuracao2fa->ativo) {
            return response()->json([
                'message' => 'Se aplicável, enviamos um código para o seu email.',
            ]);
        }

        // Reaproveita a tabela de códigos de recuperação de senha para o 2FA por email
        $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        \App\Models\CodigoRecuperacao::where('email', $email)
            ->where('usado', false)
            ->update(['usado' => true]);

        \App\Models\CodigoRecuperacao::create([
            'email'      => $email,
            'codigo'     => \Illuminate\Support\Facades\Hash::make($codigo),
            'expira_em'  => now()->addMinutes(10),
            'usado'      => false,
            'tentativas' => 0,
        ]);

        \Illuminate\Support\Facades\Mail::to($email)
            ->send(new \App\Mail\CodigoRecuperacaoMail($codigo));

        return response()->json([
            'message' => 'Se aplicável, enviamos um código para o seu email.',
        ]);
    }

    // ── LOGIN 2FA - Passo B: verificar código e finalizar o login ─────────────
    public function verificarLogin(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'email'  => 'required|string|email',
            'codigo' => 'required|string',
            'metodo' => 'required|in:authenticator,email,backup',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim($request->email));
        $user  = \App\Models\User::where('email', $email)->where('active', true)->first();

        if (!$user || !$user->configuracao2fa || !$user->configuracao2fa->ativo) {
            return response()->json([
                'message' => 'Não foi possível validar o código.',
            ], 401);
        }

        $config = $user->configuracao2fa;
        $codigo = trim($request->codigo);
        $valido = false;

        // ── Valida conforme o método ──────────────────────────────────────────
        if ($request->metodo === 'authenticator') {
            $google2fa = new \PragmaRX\Google2FA\Google2FA();
            $valido = $google2fa->verifyKey($config->secret, $codigo);

        } elseif ($request->metodo === 'email') {
            $registro = \App\Models\CodigoRecuperacao::where('email', $email)
                ->where('usado', false)
                ->latest()
                ->first();

            if ($registro && now()->lessThan($registro->expira_em)
                && \Illuminate\Support\Facades\Hash::check($codigo, $registro->codigo)) {
                $valido = true;
                $registro->update(['usado' => true]);
            }

        } elseif ($request->metodo === 'backup') {
            $codigos = $config->codigos_recuperacao ?? [];
            $codigoUpper = strtoupper($codigo);

            if (in_array($codigoUpper, $codigos, true)) {
                $valido = true;
                // Remove o código de backup usado (uso único)
                $config->update([
                    'codigos_recuperacao' => array_values(array_diff($codigos, [$codigoUpper])),
                ]);
            }
        }

        if (!$valido) {
            return response()->json([
                'message' => 'Código incorreto ou expirado.',
            ], 401);
        }

        // ── Código válido — gera o token e finaliza o login ───────────────────
        $token = auth()->login($user);

        return response()->json([
            'message' => 'Login realizado com sucesso.',
            'user'    => $user,
            'token'   => $token,
        ]);
    }
}
