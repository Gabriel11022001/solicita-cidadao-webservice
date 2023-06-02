<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\DAOS\GestorInstituicaoDAO;
use SistemaSolicitacaoServico\App\DAOS\GestorSecretariaDAO;
use SistemaSolicitacaoServico\App\DAOS\PeritoDAO;
use SistemaSolicitacaoServico\App\DAOS\SecretarioDAO;
use SistemaSolicitacaoServico\App\DAOS\TecnicoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');
    $peritoDAO = new PeritoDAO($conexaoBancoDados, 'tbl_peritos');
    $gestorSecretariaDAO = new GestorSecretariaDAO($conexaoBancoDados, 'tbl_gestores_secretaria');
    $gestorInstituicaoDAO = new GestorInstituicaoDAO($conexaoBancoDados, 'tbl_gestores_instituicao');
    $tecnicoDAO = new TecnicoDAO($conexaoBancoDados, 'tbl_tecnicos');
    $secretarioDAO = new SecretarioDAO($conexaoBancoDados, 'tbl_secretarios');
    $cidadaos = $cidadaoDAO->buscarTodosCidadaos();
    $peritos = $peritoDAO->buscarTodosPeritos();
    $gestoresSecretaria = $gestorSecretariaDAO->buscarTodosGestoresSecretaria();
    $gestoresInstituicao = $gestorInstituicaoDAO->buscarTodosGestoresInstituicao();
    $tecnicos = $tecnicoDAO->buscarTodosTecnicos();
    $secretarios = $secretarioDAO->buscarTodosSecretarios();
    $usuarios = [];
    
    if (!count($cidadaos) > 0 && !count($peritos) > 0
    && !count($tecnicos) > 0 && !count($secretarios) > 0
    && !count($gestoresInstituicao) > 0 && !count($gestoresSecretaria) > 0) {
        // não existem usuários cadastrados no banco de dados
        RespostaHttp::resposta('Não existem usuários cadastrados no banco de dados!', 200, [], true);
        exit;
    }

    if (count($cidadaos) > 0) {

        foreach ($cidadaos as $cidadao) {
            $cidadao['tipo_usuario'] = 'cidadão';
            $usuarios[] = $cidadao; 
        }

    }

    if (count($peritos) > 0) {

        foreach ($peritos as $perito) {
            $perito['tipo_usuario'] = 'perito';
            $usuarios[] = $perito;
        }

    }

    if (count($tecnicos) > 0) {

        foreach ($tecnicos as $tecnico) {
            $tecnico['tipo_usuario'] = 'técnico';
            $usuarios[] = $tecnico;
        }

    }

    if (count($secretarios) > 0) {

        foreach ($secretarios as $secretario) {
            $secretario['tipo_usuario'] = 'secretário';
            $usuarios[] = $secretario;
        }

    }

    if (count($gestoresInstituicao) > 0) {

        foreach ($gestoresInstituicao as $gestorInstituicao) {
            $gestorInstituicao['tipo_usuario'] = 'gestor-instituição';
            $usuarios[] = $gestorInstituicao;
        }

    }

    if (count($gestoresSecretaria) > 0) {

        foreach ($gestoresSecretaria as $gestorSecretaria) {
            $gestorSecretaria['tipo_usuario'] = 'gestor-secretaria';
            $usuarios[] = $gestorSecretaria;
        }

    }

    if (count($usuarios) === 1) {
        RespostaHttp::resposta('Existe 1 usuário cadastrado no banco de dados!', 200, $usuarios, true);
    } else {
        RespostaHttp::resposta('Existe um total de ' . count($usuarios) . ' usuários cadastrados no banco de dados!', 200, $usuarios, true);
    }

} catch (Exception $e) {
    var_dump($e->getMessage());
}