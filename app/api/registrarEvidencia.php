<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\SolicitacaoServicoDAO;
use SistemaSolicitacaoServico\App\Entidades\Evidencia;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

$conexaoBancoDados = ConexaoBancoDados::obterConexao();
$conexaoBancoDados->beginTransaction();

try {
    Auth::validarToken();
    $evidencia = new Evidencia();
    $evidencia->setDescricao(trim(ParametroRequisicao::obterParametro('descricao')));
    $evidencia->setUrlFotoEvidencia(trim(ParametroRequisicao::obterParametro('url_foto_evidencia')));
    $evidencia->setSolicitacaoServicoId(intval(ParametroRequisicao::obterParametro('solicitacao_servico_id')));
    $dataRegistro = new DateTime('now');
    $evidencia->setDataRegistro($dataRegistro);
    $errosDados = [];

    if (empty($evidencia->getDescricao())) {
        $errosDados['descricao'] = 'Informe a descrição da evidência!';
    }

    if (empty($evidencia->getSolicitacaoServicoId())) {
        $errosDados['solicitacao_servico_id'] = 'Informe o id da solicitação de serviço!';
    }
    
    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 200, $errosDados, false);
        exit;
    }
    
    if ($evidencia->getSolicitacaoServicoId() <= 0) {
        RespostaHttp::resposta('O id da solicitação de serviço não pode ser menor ou igual a 0!', 200, null, false);
        exit;
    }

    $evidenciaDAO = null;
    $solicitacaoServicoDAO = new SolicitacaoServicoDAO($conexaoBancoDados, 'tbl_solicitacoes_servico');
    $solicitacao = $solicitacaoServicoDAO->buscarPeloId($evidencia->getSolicitacaoServicoId());

    // validando se existe uma solicitação cadastrada no banco de dados com o id informado
    if (!$solicitacao) {
        RespostaHttp::resposta('Não existe uma solicitação de serviço cadastrada no banco de dados com esse id!', 200, null, false);
        exit;
    }

    if ($solicitacao['status'] === 'Concluído'
    || $solicitacao['status'] === 'Cancelado'
    || $solicitacao['status'] === 'Reprovado pelo perito') {
        RespostaHttp::resposta('Não é possível registrar uma evidência para essa solicitação pois seu status está definido como ' . $solicitacao['status'] . '!', 200, null, false);
        exit;
    }
    
    $dadosEvidenciaCadastrar = array(
        'descricao' => array(
            'dado' => $evidencia->getDescricao(),
            'tipo_dado' => PDO::PARAM_STR
        ),
        'solicitacao_servico_id' => array(
            'dado' => $evidencia->getSolicitacaoServicoId(),
            'tipo_dado' => PDO::PARAM_INT
        ),
        'data_registro' => array(
            'dado' => $evidencia->getDataRegistro()->format('Y-m-d H:i:s'),
            'tipo_dado' => PDO::PARAM_STR
        ),
        'url_foto_laudo' => array(
            'dado' => $evidencia->getUrlFotoEvidencia(),
            'tipo_dado' => PDO::PARAM_STR
        )
    );
} catch (AuthException $e) {
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), $e->getMessage());
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se registrar a evidência!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se registrar a evidência!', 200, null, false);
}