<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    $idUsuario = trim(ParametroRequisicao::obterParametro('id'));
    $tipoUsuario = trim(ParametroRequisicao::obterParametro('tipo_usuario'));
    $novoStatus = boolval(ParametroRequisicao::obterParametro('novo_status'));

    if (empty($idUsuario) || empty($tipoUsuario)) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 400, null);
        exit;
    }

    $idUsuario = intval($idUsuario);
    $tipoUsuario = strtolower($tipoUsuario);
    
    if ($tipoUsuario != 'cidadão' && $tipoUsuario != 'perito'
    && $tipoUsuario != 'gestor-secretaria' && $tipoUsuario != 'técnico'
    && $tipoUsuario != 'secretario(a)' && $tipoUsuario != 'gestor-instituição') {
        RespostaHttp::resposta('O tipo de usuário informado é inválido!', 400, null);
    }
    
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $usuarioDAO = null;

    switch ($tipoUsuario) {
        case 'cidadão':
            $usuarioDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');
            break;
        default:
            break;
    }

    $usuarioAlterarStatus = $usuarioDAO->buscarPeloId($idUsuario);
    
    if (count($usuarioAlterarStatus) === 0) {
        RespostaHttp::resposta('Não existe um usuário cadastrado com esse id no banco de dados!', 200, null);
        exit;
    }

    if ($usuarioDAO->alterarStatusUsuario($idUsuario, $novoStatus)) {
        RespostaHttp::resposta('O status do usuário foi alterado com sucesso!', 200, [
            'id' => $idUsuario,
            'novo_status' => $novoStatus
        ]);
    } else {
        RespostaHttp::resposta('ocorreu um erro ao tentar-se alterar o status do usuário em questão!', 400, null);
    }
    
} catch (Exception $e) {
    echo $e->getMessage();
}