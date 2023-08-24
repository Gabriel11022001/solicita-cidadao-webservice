<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\InstituicaoDAO;
use SistemaSolicitacaoServico\App\DAOS\PeritoDAO;
use SistemaSolicitacaoServico\App\DAOS\SolicitacaoServicoDAO;
use SistemaSolicitacaoServico\App\Entidades\SolicitacaoServico;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\GerenciadorEmail;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

$conexaoBancoDados = ConexaoBancoDados::obterConexao();
$conexaoBancoDados->beginTransaction();

try {
    Auth::validarToken();
    $solicitacaoId = intval(ParametroRequisicao::obterParametro('solicitacao_id'));
    $tipoReenchaminhamento = trim(ParametroRequisicao::obterParametro('tipo_reencaminhamento'));
    $idPerito = null;
    $idInstituicao = null;
    $errosDados = [];

    if (empty($solicitacaoId)) {
        $errosDados['solicitacao_id'] = 'Informe o id da solicitação de serviço!';
    }

    if (empty($tipoReenchaminhamento)) {
        $errosDados['tipo_reencaminhamento'] = 'Informe o tipo de reencaminhamento que você deseja realizar!';
    }

    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 200, $errosDados, false);
        exit;
    }

    if ($tipoReenchaminhamento != 'perito' && $tipoReenchaminhamento != 'instituicao') {
        RespostaHttp::resposta('Tipo de reencaminhamento inválido!', 200, null, false);
        exit;
    }

    if ($tipoReenchaminhamento === 'perito') {
        $idPerito = intval(ParametroRequisicao::obterParametro('perito_id'));
    } else {
        $idInstituicao = intval(ParametroRequisicao::obterParametro('instituicao_id'));
    }

    if ($tipoReenchaminhamento === 'perito' && empty($idPerito)) {
        $errosDados['perito_id'] = 'Informe o id do perito!';
    } elseif ($tipoReenchaminhamento === 'instituicao' && empty($idInstituicao)) {
        $errosDados['instituicao_id'] = 'Informe o id da instituição!';
    }

    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 200, $errosDados, false);
        exit;
    }

    $peritoDAO = new PeritoDAO($conexaoBancoDados, 'tbl_peritos');
    $instituicaoDAO = new InstituicaoDAO($conexaoBancoDados, 'tbl_instituicoes');
    $solicitacaoServicoDAO = new SolicitacaoServicoDAO($conexaoBancoDados, 'tbl_solicitacoes_servico');
    $instituicaoEncaminhar = null;
    $peritoEncaminhar = null;
    $solicitacao = $solicitacaoServicoDAO->buscarSolicitacaoPeloId($solicitacaoId);

    if ($tipoReenchaminhamento === 'perito') {
        // buscar o perito pelo id(o que vai receber a solicitação)
        $peritoEncaminhar = $peritoDAO->buscarPeloId($idPerito);

        if (!$peritoEncaminhar) {
            RespostaHttp::resposta('Não existe um perito cadastrado no banco de dados com esse id!', 200, null, false);
            exit;
        }

    } else {
        // buscar instituição que vai receber a solicitação
        $instituicaoEncaminhar = $instituicaoDAO->buscarPeloId($idInstituicao);

        if (!$instituicaoEncaminhar) {
            RespostaHttp::resposta('Não existe uma instituição cadastrada no banco de dados com esse id!', 200, null, false);
            exit;
        }

    }

    if (!$solicitacao) {
        RespostaHttp::resposta('Não existe uma solicitação de serviço cadastrada no banco de dados com esse id!', 200, null, false);
        exit;
    }

    if ($solicitacao['perito_id'] != null) {
        $idPeritoAnterior = $solicitacao['perito_id'];
        $peritoAnterior = $peritoDAO->buscarPeloId($idPeritoAnterior);
        
        if (!$peritoAnterior) {
            RespostaHttp::resposta('O perito anterior definido na solicitação não está cadastrado no banco de dados!', 200, null, false);
            exit;
        }

        $emailPeritoAnterior = $peritoAnterior['email'];

        if ($tipoReenchaminhamento === 'perito') {
            /**
             * - alterar o valor da coluna perito_id para o id do
             * novo perito que vai receber a solicitação.
             * - definir null para a coluna instituicao_id.
             * - enviar e-mail para o perito anterior informando
             * que a solicitação foi trasferida para outro perito.
             * - enviar e-mail para o perito que recebeu a solicitação.
             * - alterar o status para 'Aguardando análise do perito'.
             * - alterar o valor da coluna equipe_id para null.
             */

            if (!SolicitacaoServicoDAO::reencaminharSolicitacaoParaPerito(
                $conexaoBancoDados,
                $solicitacaoId,
                $idPerito
            )) {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Ocorreu um erro ao tentar-se reencaminhar a solicitação de serviço!', 200, null, false);
                exit;
            }

            if (!SolicitacaoServicoDAO::definirNuloParaColunaInstituicaoIdDaSolicitacao(
                $conexaoBancoDados, 
                $solicitacaoId
            )) {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Ocorreu um erro ao tentar-se reencaminhar a solicitação de serviço!', 200, null, false);
                exit;
            }

            // ===== a coluna equipe_id não está sendo definida como null! =====
            if (!SolicitacaoServicoDAO::definirNuloParaColunaEquipeIdDaSolicitacao(
                $conexaoBancoDados,
                $solicitacaoId
            )) {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Ocorreu um erro ao tentar-se reencaminhar a solicitação de serviço!', 200, null, false);
                exit;
            }

            if (!GerenciadorEmail::enviarEmail(
                $emailPeritoAnterior,
                'A solicitação com o protocolo ' . $solicitacao['protocolo'] . ' foi encaminhada para outro perito!',
                'Reencaminhamento de solicitação de serviço'
            )) {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Ocorreu um erro ao tentar-se reencaminhar a solicitação de serviço!', 200, null, false);
                exit;
            }

            if (!GerenciadorEmail::enviarEmail(
                $peritoEncaminhar['email'],
                'Foi encaminhado a você a solicitação de serviço de protocolo ' . $solicitacao['protocolo'] . '!',
                'Reencaminhamento de solicitação de serviço'
            )) {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Ocorreu um erro ao tentar-se reencaminhar a solicitação de serviço!', 200, null, false);
                exit;
            }

            $conexaoBancoDados->commit();
            RespostaHttp::resposta('A solicitação de serviço foi reencaminhada com sucesso!', 200, [
                'id_solicitacao' => $solicitacaoId,
                'novo_status_solicitacao' => 'Aguardando análise do perito',
                'id_perito_recebeu_solicitacao' => $idPerito
            ], true);
        } else {
            /**
             * - definir null para a coluna perito_id.
             * - definir o valor na coluna instituicao_id referente
             * a instituição que vai receber a solicitação.
             * - enviar e-mail para o perito anterior informando que a solicitação
             * foi transferida para uma instituição.
             * - enviar e-mail para os gestores de instituição da instituição
             * que recebeu a solicitação.
             * - alterar o status para 'Aguardando encaminhamento a equipe responsável'.
             */
        }

    }

    if ($solicitacao['instituicao_id'] != null) {
        $idInstituicaoAnterior = $solicitacao['instituicao_id'];
        $instituicaoAnterior = $instituicaoDAO->buscarPeloId($idInstituicaoAnterior);

        if (!$instituicaoAnterior) {
            RespostaHttp::resposta('A instituição anterior definida na solicitação não está cadastrada no banco de dados!', 200, null, false);
            exit;
        }

        $emailsGestoresInstituicao = [];
        $emailsTecnicos = [];
        // buscar os e-mails dos gestores de instituição relacionados a instituição
    }

} catch (AuthException $e) {

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se reencaminhar a solicitação de serviço!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se reencaminhar a solicitação de serviço!' . $e->getMessage(), 200, null, false);
}