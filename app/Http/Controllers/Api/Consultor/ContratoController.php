<?php

namespace App\Http\Controllers\Api\Consultor;

use App\Http\Controllers\Controller;
use App\Models\Contrato;
use App\Models\User;
use App\Models\Fazenda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ContratoController extends Controller
{
    // ── Lista contratos do usuário autenticado ────────────────────────────────

    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'consultor') {
            $contratos = Contrato::where('consultor_id', $user->id)
                ->with(['fazendeiro', 'fazenda'])
                ->get();
        } else {
            $contratos = Contrato::where('fazendeiro_id', $user->id)
                ->with(['consultor', 'fazenda'])
                ->get();
        }

        return response()->json([
            'contratos' => $contratos,
        ]);
    }

    // ── Exibe um contrato específico ──────────────────────────────────────────

    public function show($id)
    {
        $user = auth()->user();

        $contrato = Contrato::where(function ($query) use ($user) {
            $query->where('consultor_id', $user->id)
                ->orWhere('fazendeiro_id', $user->id);
        })
            ->with(['consultor', 'fazendeiro', 'fazenda'])
            ->findOrFail($id);

        return response()->json([
            'contrato' => $contrato,
        ]);
    }

    // ── Consultor propõe um contrato ──────────────────────────────────────────

    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'consultor') {
            return response()->json([
                'message' => 'Apenas consultores podem propor contratos.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'fazendeiro_username' => 'required|string|exists:users,username',
            'fazenda_id'          => 'required|uuid|exists:fazendas,id',
            'start_date'          => 'nullable|date',
            'end_date'            => 'nullable|date|after_or_equal:start_date',
            'value'               => 'nullable|numeric|min:0',
            'scope_description'   => 'nullable|string',
            'file'                => 'nullable|file|mimes:pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $fazendeiro = User::where('username', $request->fazendeiro_username)
            ->where('role', 'fazendeiro')
            ->first();

        if (!$fazendeiro) {
            return response()->json([
                'message' => 'Fazendeiro não encontrado.',
            ], 404);
        }

        $fazenda = Fazenda::where('id', $request->fazenda_id)
            ->where('fazendeiro_id', $fazendeiro->id)
            ->first();

        if (!$fazenda) {
            return response()->json([
                'message' => 'Essa fazenda não pertence ao fazendeiro informado.',
            ], 422);
        }

        $file_url = null;
        if ($request->hasFile('file')) {
            $file_url = $request->file('file')->store('contratos', 'public');
        }

        $contrato = Contrato::create([
            'consultor_id'     => $user->id,
            'fazendeiro_id'    => $fazendeiro->id,
            'fazenda_id'       => $request->fazenda_id,
            'status'           => 'pendente',
            'start_date'       => $request->start_date,
            'end_date'         => $request->end_date,
            'value'            => $request->value,
            'scope_description'=> $request->scope_description,
            'file_url'         => $file_url,
        ]);

        return response()->json([
            'message'  => 'Proposta de contrato enviada com sucesso.',
            'contrato' => $contrato->load(['consultor', 'fazendeiro', 'fazenda']),
        ], 201);
    }

    // ── Fazendeiro confirma ou recusa o contrato ──────────────────────────────

    public function responder(Request $request, $id)
    {
        $user = auth()->user();

        if ($user->role !== 'fazendeiro') {
            return response()->json([
                'message' => 'Apenas fazendeiros podem responder contratos.',
            ], 403);
        }

        $contrato = Contrato::where('fazendeiro_id', $user->id)
            ->where('status', 'pendente')
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'acao' => 'required|in:aceitar,recusar',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ação inválida.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $contrato->status = $request->acao === 'aceitar' ? 'ativo' : 'cancelado';
        $contrato->save();

        $mensagem = $request->acao === 'aceitar'
            ? 'Contrato aceito com sucesso.'
            : 'Contrato recusado.';

        return response()->json([
            'message'  => $mensagem,
            'contrato' => $contrato->load(['consultor', 'fazendeiro', 'fazenda']),
        ]);
    }

    // ── Encerra um contrato ativo ─────────────────────────────────────────────

    public function encerrar($id)
    {
        $user = auth()->user();

        $contrato = Contrato::where(function ($query) use ($user) {
            $query->where('consultor_id', $user->id)
                ->orWhere('fazendeiro_id', $user->id);
        })
            ->where('status', 'ativo')
            ->findOrFail($id);

        $contrato->status = 'encerrado';
        $contrato->save();

        return response()->json([
            'message'  => 'Contrato encerrado com sucesso.',
            'contrato' => $contrato,
        ]);
    }
}
