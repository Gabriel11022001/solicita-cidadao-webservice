<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\DAOS\GestorInstituicaoDAO;
use SistemaSolicitacaoServico\App\DAOS\GestorSecretariaDAO;
use SistemaSolicitacaoServico\App\DAOS\PeritoDAO;
use SistemaSolicitacaoServico\App\DAOS\SecretarioDAO;
use SistemaSolicitacaoServico\App\DAOS\TecnicoDAO;
use SistemaSolicitacaoServico\App\DAOS\UsuarioDAO;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {

    if (!isset($_GET['id'])) {
        RespostaHttp::resposta('O id não está definido como parâmetro na url!', 200, null, false);
        exit;
    }

    $id = intval($_GET['id']);
    
    if (empty($id)) {
        RespostaHttp::resposta('Informe o id do usuário!', 200, null, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');
    $peritoDAO = new PeritoDAO($conexaoBancoDados, 'tbl_peritos');
    $secretarioDAO = new SecretarioDAO($conexaoBancoDados, 'tbl_secretarios');
    $gestorInstituicaoDAO = new GestorInstituicaoDAO($conexaoBancoDados, 'tbl_gestores_instituicao');
    $gestorSecretariaDAO = new GestorSecretariaDAO($conexaoBancoDados, 'tbl_gestores_secretaria');
    $tecnicoDAO = new TecnicoDAO($conexaoBancoDados, 'tbl_tecnicos');
    $usuarioCidadao = $cidadaoDAO->buscarUsuarioPeloId($id);
    $usuarioPerito = $peritoDAO->buscarUsuarioPeloId($id);
    $usuarioTecnico = $tecnicoDAO->buscarUsuarioPeloId($id);
    $usuarioSecretario = $secretarioDAO->buscarUsuarioPeloId($id);
    $usuarioGestorSecretaria = $gestorSecretariaDAO->buscarUsuarioPeloId($id);
    $usuarioGestorInstituicao = $gestorInstituicaoDAO->buscarUsuarioPeloId($id);
    $tipoUsuario = '';
    $usuario = null;

    if ($usuarioCidadao) {
        $tipoUsuario = 'cidadão';
        $usuario = $usuarioCidadao;
    } elseif ($usuarioPerito) {
        $tipoUsuario = 'perito';
        $usuario = $usuarioPerito;
    } elseif ($usuarioTecnico) {
        $tipoUsuario = 'técnico';
        $usuario = $usuarioTecnico;
    } elseif ($usuarioSecretario) {
        $tipoUsuario = 'secretario';
        $usuario = $usuarioSecretario;
    } elseif ($usuarioGestorInstituicao) {
        $tipoUsuario = 'gestor-instituição';
        $usuario = $usuarioGestorInstituicao;
    } elseif ($usuarioGestorSecretaria) {
        $tipoUsuario = 'gestor-secretaria';
        $usuario = $usuarioGestorSecretaria;
    } else {
        // usuário não encontrado
        RespostaHttp::resposta('Não existe um usuário cadastrado no banco de dados com esse id!');
        exit;
    }

    $usuario['tipo_usuario'] = $tipoUsuario;
    RespostaHttp::resposta('Usuário encontrado com sucesso!', 200, $usuario, true);
} catch (Exception $e) {
    
}