<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\ServicoDAO;
use SistemaSolicitacaoServico\App\Entidades\TipoServico;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    $tipoServico = new TipoServico();
    $tipoServico->setNome(ParametroRequisicao::obterParametro('nome'));
    $tipoServico->setDescricao(ParametroRequisicao::obterParametro('descricao'));
    $errosCampos = [];

    if (empty($tipoServico->getNome())) {
        $errosCampos['nome'] = 'Informe o nome do tipo de serviço!';
    }

    if (empty($tipoServico->getDescricao())) {
        $errosCampos['descricao'] = 'Informe a descrição do tipo de serviço!';
    }

    if (count($errosCampos) > 0) {
        RespostaHttp::resposta('Preencha todos os campos obrigatórios!', 400, $errosCampos);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $tipoServicoDAO = new ServicoDAO($conexaoBancoDados, 'tbl_servicos');

    if ($tipoServicoDAO->buscarTipoServicoPeloNome($tipoServico->getNome()) != false) {
        RespostaHttp::resposta('Já existe um outro tipo de serviço cadastrado com esse nome!', 400, null);
        exit;
    }

    $dadosTipoServico = [
        'nome' => [
            'dado' => $tipoServico->getNome(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'descricao' => [
            'dado' => $tipoServico->getDescricao(),
            'tipo_dado' => PDO::PARAM_STR
        ]
    ];

    if ($tipoServicoDAO->salvar($dadosTipoServico)) {
        RespostaHttp::resposta('O tipo de serviço foi cadastrado com sucesso!', 201, [
            'id' => intval($conexaoBancoDados->lastInsertId()),
            'nome' => $tipoServico->getNome(),
            'descricao' => $tipoServico->getDescricao(),
            'status' => true
        ]);
    } else {
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar o tipo de serviço!', 400, null);
    }

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se cadastrar um tipo de serviço!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar o tipo de serviço!', 400, null);
}