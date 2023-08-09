<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\EquipeDAO;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCamposObrigatorios;
use SistemaSolicitacaoServico\App\Entidades\Equipe;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;

try {
    Auth::validarToken();
    $equipeEditar = new Equipe();
    $equipeEditar->setId(intval(ParametroRequisicao::obterParametro('id')));
    $equipeEditar->setNome(trim(ParametroRequisicao::obterParametro('nome')));
    $equipeEditar->setDescricao(trim(ParametroRequisicao::obterParametro('descricao')));
    $equipeEditar->setStatus(ParametroRequisicao::obterParametro('status'));
    $errosDados = [];
    $errosDados = ValidaCamposObrigatorios::validarFormularioEditarEquipe($equipeEditar);

    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 200, $errosDados, false);
        exit;
    }
    
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $equipeDAO = new EquipeDAO($conexaoBancoDados, 'tbl_equipes');
    $equipeComNomeInformado = $equipeDAO->buscarEquipePeloNome($equipeEditar->getNome());
    $podeEditarComNomeInformado = true;

    if (!$equipeComNomeInformado) {
        // pode editar com o nome informado
        $podeEditarComNomeInformado = true;
    } else {

        if ($equipeEditar->getId() != $equipeComNomeInformado['id']
        && $equipeEditar->getNome() === $equipeComNomeInformado['nome']) {
            // não pode editar com o nome informado
            $podeEditarComNomeInformado = false;
        } else {
            // pode editar com o nome informado
            $podeEditarComNomeInformado = true;
        }

    }

    if ($podeEditarComNomeInformado) {

        if ($equipeDAO->editarEquipe($equipeEditar)) {
            // equipe editada com sucesso
            RespostaHttp::resposta('Equipe editada com sucesso!', 200, [
                'id' => $equipeEditar->getId(),
                'nome' => $equipeEditar->getNome(),
                'descricao' => $equipeEditar->getDescricao(),
                'status' => $equipeEditar->getStatus()
            ], true);
        } else {
            RespostaHttp::resposta('Ocorreu um erro ao tentar-se editar a equipe!', 200, null, false);
        }

    } else {
        RespostaHttp::resposta('Já existe outra equipe cadastrada com o nome informado, informe outro nome!', 200, null, false);
    }

} catch (AuthException $e) {
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se editar a equipe!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se editar a equipe!', 200, null, false);
}