<?php

namespace App\Services;

use App\Models\Notificacao;

class NotificacaoService
{
    /**
     * Cria uma notificação direcionada a um usuário.
     *
     * @param string      $userId    Quem recebe
     * @param string      $tipo      Ex: 'contrato_aceito'
     * @param string      $titulo    Título curto
     * @param string      $mensagem  Descrição
     * @param array|null  $dados     Navegação: ['rota' => 'contrato', 'id' => $uuid]
     */
    public static function criar(
        string $userId,
        string $tipo,
        string $titulo,
        string $mensagem,
        ?array $dados = null
    ): Notificacao {
        return Notificacao::create([
            'user_id'  => $userId,
            'tipo'     => $tipo,
            'titulo'   => $titulo,
            'mensagem' => $mensagem,
            'dados'    => $dados,
            'lida'     => false,
        ]);
    }

    /**
     * Atalhos semânticos para os eventos de contrato.
     * Centralizam o texto — se quiser mudar a redação, muda só aqui.
     */
    public static function contratoAceito(string $consultorId, $contrato): Notificacao
    {
        return self::criar(
            $consultorId,
            'contrato_aceito',
            'Contrato aceito',
            "O contrato da fazenda \"{$contrato->fazenda->name}\" foi aceito.",
            ['rota' => 'contrato', 'id' => $contrato->id]
        );
    }

    public static function contratoRecusado(string $consultorId, $contrato): Notificacao
    {
        return self::criar(
            $consultorId,
            'contrato_recusado',
            'Contrato recusado',
            "O contrato da fazenda \"{$contrato->fazenda->name}\" foi recusado.",
            ['rota' => 'contrato', 'id' => $contrato->id]
        );
    }

    public static function contratoEncerrado(string $destinatarioId, $contrato): Notificacao
    {
        return self::criar(
            $destinatarioId,
            'contrato_encerrado',
            'Contrato encerrado',
            "O contrato da fazenda \"{$contrato->fazenda->name}\" foi encerrado.",
            ['rota' => 'contrato', 'id' => $contrato->id]
        );
    }
}
