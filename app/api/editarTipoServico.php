<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\ServicoDAO;
use SistemaSolicitacaoServico\App\Entidades\TipoServico;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCamposObrigatorios;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

try {
    $tipoServico = new TipoServico();
    $tipoServico->setId(intval(ParametroRequisicao::obterParametro('id')));
    $tipoServico->setNome(mb_strtoupper(trim(ParametroRequisicao::obterParametro('nome'))));
    $tipoServico->setDescricao(trim(ParametroRequisicao::obterParametro('descricao')));
    $tipoServico->setStatus(ParametroRequisicao::obterParametro('status'));
    $errosFormularioEditarTipoServico = ValidaCamposObrigatorios::validarFormularioEditarTipoServico($tipoServico);

    if (count($errosFormularioEditarTipoServico) > 0) {
        RespostaHttp::resposta('Preencha todos os campos obrigatórios!', 200, $errosFormularioEditarTipoServico, false);
        exit;
    }

    // validando se o usuário informou um id maior que 0 para o tipo de serviço
    if ($tipoServico->getId() < 0) {
        $errosFormularioEditarTipoServico['id'] = 'O id do tipo de serviço deve ser um valor maior que 0!';
    }

    // validando se o nome do tipo de serviço possui pelo menos 3 caracteres
    if (strlen($tipoServico->getNome()) < 3) {
        $errosFormularioEditarTipoServico['nome'] = 'O nome do tipo de serviço deve possuir no mínimo 3 caracteres!';
    }

    // validando se a descrição do tipo de serviço possui no mínimo 3 caracteres
    if (strlen($tipoServico->getDescricao()) < 3) {
        $errosFormularioEditarTipoServico['descricao'] = 'A descrição do tipo de serviço deve possuir no mínimo 3 caracteres!';
    }
    
    if (count($errosFormularioEditarTipoServico) > 0) {
        RespostaHttp::resposta('Ocorreram erros de validação de campos!', 200, $errosFormularioEditarTipoServico, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $tipoServicoDAO = new ServicoDAO($conexaoBancoDados, 'tbl_servicos');
    // validando se existe um tipo de serviço cadastrado com o id informado
    $tipoServicoComIdInformado = $tipoServicoDAO->buscarPeloId($tipoServico->getId());

    if (!$tipoServicoComIdInformado) {
        RespostaHttp::resposta('Não existe um tipo de serviço cadastrado com o id informado!', 200, null, false);
        exit;
    }

    // validando se já existe algum outro tipo de serviço com o nome informado
    $tipoServicoComNomeInformado = $tipoServicoDAO->buscarTipoServicoPeloNome($tipoServico->getNome());

    if ($tipoServicoComNomeInformado != false) {
        // existe um tipo de serviço com o nome informado

        if ($tipoServicoComNomeInformado['id'] != $tipoServico->getId()) {
            // é outro tipo de serviço, não pode editar!
            RespostaHttp::resposta('Já existe outro tipo de serviço cadastrado com o nome informado!', 200, null, false);
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
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se alterar os dados do tipo de serviço!', 200, null, false);
    }

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se alterar os dados do tipo de serviço!', $e->getMessage());
}