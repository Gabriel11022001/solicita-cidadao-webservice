<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\ServicoDAO;
use SistemaSolicitacaoServico\App\Entidades\TipoServico;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCamposObrigatorios;

try {
    $tipoServico = new TipoServico();
    $tipoServico->setId(ParametroRequisicao::obterParametro('id'));
    $tipoServico->setNome(ParametroRequisicao::obterParametro('nome'));
    $tipoServico->setDescricao(ParametroRequisicao::obterParametro('descricao'));
    $tipoServico->setStatus(ParametroRequisicao::obterParametro('status'));
    $errosFormularioEditarTipoServico = ValidaCamposObrigatorios::validarFormularioEditarTipoServico($tipoServico);

    if (count($errosFormularioEditarTipoServico) > 0) {
        RespostaHttp::resposta('Preencha todos os campos obrigatórios!', 400, $errosFormularioEditarTipoServico);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $tipoServicoDAO = new ServicoDAO($conexaoBancoDados, 'tbl_servicos');
    // validando se existe um tipo de serviço cadastrado com o id informado
    $tipoServicoComIdInformado = $tipoServicoDAO->buscarPeloId($tipoServico->getId());

    if (!$tipoServicoComIdInformado) {
        RespostaHttp::resposta('Não existe um tipo de serviço cadastrado com o id informado!', 400, null);
        exit;
    }

    // validando se já existe algum outro tipo de serviço com o nome informado
    $tipoServicoComNomeInformado = $tipoServicoDAO->buscarTipoServicoPeloNome($tipoServico->getNome());

    if ($tipoServicoComNomeInformado != false) {
        // existe um tipo de serviço com o nome informado

        if ($tipoServicoComNomeInformado['id'] != $tipoServico->getId()) {
            // é outro tipo de serviço, não pode editar!
            RespostaHttp::resposta('Já existe outro tipo de serviço cadastrado com o nome informado!', 400, null);
            exit;
        }

    }

    if ($tipoServicoDAO->editarTipoServico($tipoServico)) {
        RespostaHttp::resposta('Tipo de serviço alterado com sucesso!', 200, [
            'id' => $tipoServico->getId(),
            'nome' => $tipoServico->getNome(),
            'descricao' => $tipoServico->getDescricao(),
            'status' => $tipoServico->getStatus()
        ]);
    } else {
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se alterar os dados do tipo de serviço!', 400, null);
    }

} catch (Exception $e) {

}