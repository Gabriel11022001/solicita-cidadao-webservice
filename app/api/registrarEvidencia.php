<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\DAOS\EvidenciaDAO;
use SistemaSolicitacaoServico\App\DAOS\NotificacaoDAO;
use SistemaSolicitacaoServico\App\DAOS\SolicitacaoServicoDAO;
use SistemaSolicitacaoServico\App\DAOS\TecnicoDAO;
use SistemaSolicitacaoServico\App\Entidades\Evidencia;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\GerenciadorEmail;
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
        'url_foto_evidencia' => array(
            'dado' => $evidencia->getUrlFotoEvidencia(),
            'tipo_dado' => PDO::PARAM_STR
        )
    );
    $evidenciaDAO = new EvidenciaDAO($conexaoBancoDados, 'tbl_evidencias');
    $notificacaoDAO = new NotificacaoDAO($conexaoBancoDados, 'tbl_notificacoes');
    $tecnicoDAO = new TecnicoDAO($conexaoBancoDados, 'tbl_tecnicos');
    $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');

    if (!$evidenciaDAO->salvar($dadosEvidenciaCadastrar)) {
        $conexaoBancoDados->rollBack();
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se registrar a evidência!', 200, null, false);
        exit;
    }

    // alterando o status da solicitação para concluído
    if (!SolicitacaoServicoDAO::concluirSolicitacao(
        $conexaoBancoDados,
        $evidencia->getSolicitacaoServicoId()
    )) {
        $conexaoBancoDados->rollBack();
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se registrar a evidência!', 200, null, false);
        exit;
    }

    $idEvidencia = $conexaoBancoDados->lastInsertId();
    $evidencia->setId($idEvidencia);
    $dadosCidadaoSolicitacao = $cidadaoDAO->buscarDadosCidadaoPeloIdSolicitacao($evidencia->getSolicitacaoServicoId());

    if (!$dadosCidadaoSolicitacao) {
        $conexaoBancoDados->rollBack();
        RespostaHttp::resposta('Não existe um cidadão cadastrado no banco de dados relacionado a solicitação em questão!', 200, null, false);
        exit;
    }

    $dataEnvioNoti = new DateTime('now');
    $dadosNotificacaoCadastrar = [
        'mensagem' => [
            'dado' => 'A solicitação de serviço de protocolo ' . $solicitacao['protocolo'] . ' foi concluída com sucesso!',
            'tipo_dado' => PDO::PARAM_STR
        ],
        'solicitacao_servico_id' => [
            'dado' => $evidencia->getSolicitacaoServicoId(),
            'tipo_dado' => PDO::PARAM_INT
        ],
        'usuario_id' => [
            'dado' => $dadosCidadaoSolicitacao['id'],
            'tipo_dado' => PDO::PARAM_INT
        ],
        'data_envio' => [
            'dado' => $dataEnvioNoti->format('Y-m-d H:i:s'),
            'tipo_dado' => PDO::PARAM_STR
        ]
    ];

    if (!$notificacaoDAO->salvar($dadosNotificacaoCadastrar)) {
        $conexaoBancoDados->rollBack();
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se registrar a evidência!');
        exit;
    }

    $mensagemEmail = 'A solicitação de serviço de protocolo ' . $solicitacao['protocolo'] . ' foi concluída com sucesso!<br>
    Dados da solicitação:<br>
    <ul>
        <li><strong>Protocolo:</strong> ' . $solicitacao['protocolo'] . '</li>
        <li><strong>Título da solicitação:</strong> ' . $solicitacao['titulo'] . '</li>
        <li><strong>Descrição da solicitação:</strong> ' . $solicitacao['descricao'] . '</li>
        <li><strong>Status da solicitação:</strong> Concluído</li>
        <li><strong>Prioridade da solicitação:</strong> ' . $solicitacao['prioridade'] . '</li>
        <li><strong>Cidadão:</strong> ' . $dadosCidadaoSolicitacao['nome'] . '</li>
        <li><strong>Cpf do cidadão: ' . $dadosCidadaoSolicitacao['cpf'] . '</strong></li>
        <li><strong>E-mail do cidadão: ' . $dadosCidadaoSolicitacao['email'] . '</strong></li>
        <li><strong>Telefone do cidadão: ' . $dadosCidadaoSolicitacao['telefone'] . '</strong></li>
    </ul><br>
    Dados da evidência:<br>
    <ul>
        <li><strong>Descrição detalhada do que foi realizado:</strong> ' . $evidencia->getDescricao() . '</li>
        <li><strong>Data de conclusão:</strong> ' . $evidencia->getDataRegistro()->format('d-m-Y H:i:s') . '</li>
    </ul>';
    $emailCidadaoSolicitacao = $dadosCidadaoSolicitacao['email'];
    $emailsTecnicos = TecnicoDAO::buscarEmailsTecnicosRelacionadosASolicitacaoServico($conexaoBancoDados, $evidencia->getSolicitacaoServicoId());

    // enviando e-mail para o cidadão
    if (!GerenciadorEmail::enviarEmail(
        $emailCidadaoSolicitacao,
        $mensagemEmail,
        'Conclusão da solicitação de serviço'
    )) {
        $conexaoBancoDados->rollBack();
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se registrar a evidência!', 200, null, false);
        exit;
    }

    if (count($emailsTecnicos) > 0) {
        $todosEmailsForamEnviadosComSucesso = true;

        // enviando e-mails para os técnicos da equipe relacionada a solicitação
        foreach ($emailsTecnicos as $emailTec) {
            $email = $emailTec['email'];
    
            if (!GerenciadorEmail::enviarEmail(
                $email,
                $mensagemEmail,
                'Conclusão da solicitação de serviço'
            )) {
                $todosEmailsForamEnviadosComSucesso = false;
            }
    
        }
    
        if (!$todosEmailsForamEnviadosComSucesso) {
            $conexaoBancoDados->rollBack();
            RespostaHttp::resposta('Ocorreu um erro ao tentar-se registrar a evidência!', 200, null, false);
            exit;
        }
    
    }

    $conexaoBancoDados->commit();
    RespostaHttp::resposta('A solicitação foi registrada com sucesso!', 200, [
        'id' => $idEvidencia,
        'descricao' => $evidencia->getDescricao(),
        'data_registro' => $evidencia->getDataRegistro()->format('d-m-Y H:i:s'),
        'solicitacao_servico_id' => $evidencia->getSolicitacaoServicoId()
    ], true);
} catch (AuthException $e) {
    $conexaoBancoDados->rollBack();
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), $e->getMessage());
} catch (Exception $e) {
    $conexaoBancoDados->rollBack();
    Log::registrarLog('Ocorreu um erro ao tentar-se registrar a evidência!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se registrar a evidência!' . $e->getMessage(), 200, null, false);
}