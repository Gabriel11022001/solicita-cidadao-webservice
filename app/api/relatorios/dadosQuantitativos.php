<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\GestorInstituicaoDAO;
use SistemaSolicitacaoServico\App\DAOS\RelatorioDAO;
use SistemaSolicitacaoServico\App\DAOS\TecnicoDAO;
use SistemaSolicitacaoServico\App\DAOS\UsuarioDAO;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    Auth::validarToken();
    $idUsuario = intval(ParametroRequisicao::obterParametro('usuario_id'));
    $tipoUsuario = trim(ParametroRequisicao::obterParametro('tipo_usuario'));
    $errosDados = [];

    if (empty($idUsuario)) {
        $errosDados['usuario_id'] = 'Informe o id do usuário!';
    }

    if (empty($tipoUsuario)) {
        $errosDados['tipo_usuario'] = 'Informe o tipo de usuário!';
    }

    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 200, $errosDados, false);
        exit;
    }

    if ($tipoUsuario != 'cidadão' && $tipoUsuario != 'gestor-secretaria'
    && $tipoUsuario != 'técnico' && $tipoUsuario != 'perito'
    && $tipoUsuario != 'gestor-instituição' && $tipoUsuario != 'secretário') {
        $errosDados['tipo_usuario'] = 'O tipo de usuário informado está incorreto!';
    }

    if ($idUsuario <= 0) {
        $errosDados['usuario_id'] = 'Id do usuário inválido!';
    }

    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Ocorreram erros de validação de dados!', 200, $errosDados, false);
        exit;
    }

    $dados = [];
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $relatorioDAO = new RelatorioDAO($conexaoBancoDados);
    $usuarioDAO = new UsuarioDAO($conexaoBancoDados, 'tbl_usuarios');

    if (!$usuarioDAO->buscarPeloId($idUsuario)) {
        RespostaHttp::resposta('Não existe um usuário cadastrado no banco de dados com esse id!', 200, null, false);
        exit;
    }

    if ($tipoUsuario === 'cidadão') {
        $dados = $relatorioDAO->obterDadosQuantitativosCidadao($idUsuario);
    } elseif ($tipoUsuario === 'perito') {
        $dados = $relatorioDAO->obterDadosQuantitativosPerito($idUsuario);
    } elseif ($tipoUsuario === 'secretário' || $tipoUsuario === 'gestor-secretaria') {
        $dados = $relatorioDAO->obterDadosQuantitativosGestorSecretariaSecretario();
    } elseif ($tipoUsuario === 'técnico') {
        $idEquipe = null;
        $tecnicoDAO = new TecnicoDAO($conexaoBancoDados, 'tbl_tecnicos');
        $dadosTecnico = $tecnicoDAO->buscarUsuarioPeloId($idUsuario);
        $idEquipe = $dadosTecnico['equipe_id'];
        $dados = $relatorioDAO->obterDadosQuantitativosTecnico($idEquipe);
    } else {
        $idInstituicao = null;
        $gestorInstituicaoDAO = new GestorInstituicaoDAO($conexaoBancoDados, 'tbl_gestores_instituicao');
        $dadosGestorInstituicao = $gestorInstituicaoDAO->buscarUsuarioPeloId($idUsuario);
        $idInstituicao = $dadosGestorInstituicao['instituicao_id'];
        $dados = $relatorioDAO->obterDadosQuantitativosGestorInstituicao($idInstituicao);
    }

    if (count($dados) === 0) {
        RespostaHttp::resposta('Não foram encontrados dados para o usuário em questão!', 200, $dados, false);
        exit;
    }

    RespostaHttp::resposta('Foram encontrados os seguintes dados para o usuário em questão!', 200, $dados, true);
} catch (AuthException $e) {

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar os dados quantitativos!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se buscar os dados quantitativos!' . $e->getMessage(), 200, null, false);
}