<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\ServicoDAO;
use SistemaSolicitacaoServico\App\Entidades\TipoServico;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    $tipoServico = new TipoServico();
    $tipoServico->setNome(mb_strtoupper(trim(ParametroRequisicao::obterParametro('nome'))));
    $tipoServico->setDescricao(trim(ParametroRequisicao::obterParametro('descricao')));
    $errosCampos = [];

    if (empty($tipoServico->getNome())) {
        $errosCampos['nome'] = 'Informe o nome do tipo de serviço!';
    }

    if (empty($tipoServico->getDescricao())) {
        $errosCampos['descricao'] = 'Informe a descrição do tipo de serviço!';
    }

    if (count($errosCampos) > 0) {
        RespostaHttp::resposta('Preencha todos os campos obrigatórios!', 200, $errosCampos, false);
        exit;
    }

    // validando se o nome do tipo de serviço possui pelo menos 3 caracteres
    if (strlen($tipoServico->getNome()) < 3) {
        $errosCampos['nome'] = 'O nome do tipo de serviço deve possuir no mínimo 3 caracteres!';
    }

    // validando se a descrição do tipo de serviço possui no mínimo 3 caracteres
    if (strlen($tipoServico->getDescricao()) < 3) {
        $errosCampos['descricao'] = 'A descrição do tipo de serviço deve possuir no mínimo 3 caracteres!';
    }

    if (count($errosCampos) > 0) {
        RespostaHttp::resposta('Ocorreram erros de validação de campos!', 200, $errosCampos, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $tipoServicoDAO = new ServicoDAO($conexaoBancoDados, 'tbl_servicos');

    if ($tipoServicoDAO->buscarTipoServicoPeloNome($tipoServico->getNome()) != false) {
        RespostaHttp::resposta('Já existe um outro tipo de serviço cadastrado com esse nome!', 200, null, false);
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
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar o tipo de serviço!', 200, null, false);
    }

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se cadastrar um tipo de serviço!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar o tipo de serviço!', 200, null, false);
}