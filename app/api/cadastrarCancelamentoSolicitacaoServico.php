<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CancelamentoSolicitacaoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\DAOS\SolicitacaoServicoDAO;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;

$conexaoBancoDados = ConexaoBancoDados::obterConexao();
// iniciando a transação
$conexaoBancoDados->beginTransaction();

try {
    Auth::validarToken();
    $motivo = trim(ParametroRequisicao::obterParametro('motivo'));
    $idSolicitacao = intval(ParametroRequisicao::obterParametro('id_solicitacao'));
    $dataCancelamento = new DateTime('now');
    $errosDados = [];

    if (empty($motivo)) {
        $errosDados['motivo'] = 'Informe o motivo para cancelamento!';
    }

    if (empty($idSolicitacao)) {
        $errosDados['id_solicitacao'] = 'Informe o id da solicitação!';
    }

    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 200, $errosDados, false);
        exit;
    }

    if (mb_strlen($motivo) < 6) {
        $errosDados['motivo'] = 'Informe pelo menos 6 caracteres para o motivo!';
    }

    if ($idSolicitacao <= 0) {
        $errosDados['id_solicitacao'] = 'O id da solicitação deve ser maior ou igual a 1!';
    }

    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Ocorreram erros de validação de dados!', 200, $errosDados, false);
        exit;
    }

    $cancelamentoDAO = new CancelamentoSolicitacaoDAO($conexaoBancoDados, 'tbl_cancelamentos_solicitacoes');
    $solicitacaoServicoDAO = new SolicitacaoServicoDAO($conexaoBancoDados, 'tbl_solicitacoes_servico');

    // validando se existe uma solicitação cadastrada com o id informado
    if (!$solicitacaoServicoDAO->buscarSolicitacaoPeloId($idSolicitacao)) {
        RespostaHttp::resposta('Não existe uma solicitação de serviço cadastrada no banco de dados com esse id!', 200, null, false);
    } else {
        // existe a solicitação cadastrada com o id informado
        $dadosCancelamentoCadastrar = [
            'motivo' => [ 'dado' => $motivo, 'tipo_dado' => PDO::PARAM_STR ],
            'solicitacao_servico_id' => [ 'dado' => $idSolicitacao, 'tipo_dado' => PDO::PARAM_INT ],
            'data_cancelamento' => [ 'dado' => $dataCancelamento->format('Y-m-d H:i:s'), 'tipo_dado' => PDO::PARAM_STR ]
        ];

        if ($cancelamentoDAO->salvar($dadosCancelamentoCadastrar)) {
            $idCancelamento = intval($conexaoBancoDados->lastInsertId());
            // alterando o status da solicitação de serviço para "Cancelado"

            if ($solicitacaoServicoDAO->cancelarSolicitacao($idSolicitacao)) {
                $dadosRetorno = [
                    'id' => $idCancelamento,
                    'motivo' => $motivo,
                    'data_cancelamento' => $dataCancelamento->format('d-m-Y H:i:s'),
                    'solicitacao_servico_id' => $idSolicitacao
                ];
                // comitando o processo
                $conexaoBancoDados->commit();
                RespostaHttp::resposta('Solicitação cancelada com sucesso!', 201, $dadosRetorno, true);
            } else {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar o cancelamento da solicitação de serviço!', 200, null, false);
            }

        } else {
            $conexaoBancoDados->rollBack();
            RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar o cancelamento da solicitação de serviço!', 200, null, false);
        }

    }

} catch (AuthException $e) {
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    // realizando o rollback
    $conexaoBancoDados->rollBack();
    Log::registrarLog('Ocorreu um erro ao tentar-se cadastrar o cancelamento de solicitação!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar o cancelamento de solicitação!' . $e->getMessage(), 200, null, false);
}