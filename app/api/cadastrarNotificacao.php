<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\NotificacaoDAO;
use SistemaSolicitacaoServico\App\DAOS\SolicitacaoServicoDAO;
use SistemaSolicitacaoServico\App\DAOS\UsuarioDAO;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

try {
    $mensagem = trim(ParametroRequisicao::obterParametro('mensagem'));
    $idUsuario = intval(ParametroRequisicao::obterParametro('usuario_id'));
    $idSolicitacaoServico = intval(ParametroRequisicao::obterParametro('solicitacao_servico_id'));
    $errosDados = [];
    
    if (empty($mensagem)) {
        $errosDados['mensagem'] = 'Informe a mensagem da notificação!';
    }

    if (empty($idUsuario)) {
        $errosDados['usuario_id'] = 'Informe o id do usuário!';
    }

    if (empty($idSolicitacaoServico)) {
        $errosDados['solicitacao_servico_id'] = 'Informe o id da solicitação de serviço!';
    }

    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Preencha todos os campos obrigatórios!');
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $notificacaoDAO = new NotificacaoDAO($conexaoBancoDados, 'tbl_notificacoes');
    $usuarioDAO = new UsuarioDAO($conexaoBancoDados, 'tbl_usuarios');
    $solicitacaoServicoDAO = new SolicitacaoServicoDAO($conexaoBancoDados, 'tbl_solicitacoes_servico');

    if (!$usuarioDAO->buscarPeloId($idUsuario)) {
        RespostaHttp::resposta('Não existe um usuário cadastrado no banco de dados com esse id!', 200, null, false);
        exit;
    }

    if (!$solicitacaoServicoDAO->buscarPeloId($idSolicitacaoServico)) {
        RespostaHttp::resposta('Não existe uma solicitação de serviço cadastrada com esse id!', 200, null, false);
        exit;
    }

    $dataEnvioNotificacao = new DateTime('now');
    $dataEnvioNotificacao = $dataEnvioNotificacao->format('Y-m-d H:i:s');
    $dadosCadastro = [
        'mensagem' => [
            'dado' => $mensagem,
            'tipo_dado' => PDO::PARAM_STR
        ],
        'data_envio' => [
            'dado' => $dataEnvioNotificacao,
            'tipo_dado' => PDO::PARAM_STR
        ],
        'usuario_id' => [
            'dado' => $idUsuario,
            'tipo_dado' => PDO::PARAM_INT
        ],
        'solicitacao_servico_id' => [
            'dado' => $idSolicitacaoServico,
            'tipo_dado' => PDO::PARAM_INT
        ]
    ];
    
    if ($notificacaoDAO->salvar($dadosCadastro)) {        
        RespostaHttp::resposta('Notificação cadastrada com sucesso!', 201, [
            'id' => intval($conexaoBancoDados->lastInsertId()),
            'mensagem' => $mensagem,
            'usuario_id' => $idUsuario,
            'solicitacao_servico_id' => $idSolicitacaoServico,
            'data_envio' => $dataEnvioNotificacao,
            'status' => 'Aguardando visualização'
        ]);
    } else {
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar a notificação!', 200, null, false);
    }

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se cadastrar a notificação!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar a notificação! - ' . $e->getMessage(), 200, null, false);
}