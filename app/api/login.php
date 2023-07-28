<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\DAOS\GestorInstituicaoDAO;
use SistemaSolicitacaoServico\App\DAOS\GestorSecretariaDAO;
use SistemaSolicitacaoServico\App\DAOS\PeritoDAO;
use SistemaSolicitacaoServico\App\DAOS\SecretarioDAO;
use SistemaSolicitacaoServico\App\DAOS\TecnicoDAO;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCpf;

try {
    $cpf = trim(ParametroRequisicao::obterParametro('cpf'));
    $senha = trim(ParametroRequisicao::obterParametro('senha'));
    $plataformaLogin = trim(ParametroRequisicao::obterParametro('plataforma_login'));
    $errosDados = [];

    if (empty($cpf)) {
        $errosDados['cpf'] = 'Informe o cpf!';
    }

    if (empty($senha)) {
        $errosDados['senha'] = 'Informe a senha!';
    }

    if (empty($plataformaLogin)) {
        $errosDados['plataforma_login'] = 'Informe se o login está sendo feito pelo aplicativo ou pelo sistema web!';
    }

    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 200, $errosDados, false);
        exit;
    }

    if (!ValidaCpf::validarCPF($cpf)) {
        $errosDados['cpf'] = 'Cpf inválido!';
    }

    if (strlen($senha) < 6) {
        $errosDados['senha'] = 'A senha deve possuir no mínimo 6 caracteres!';
    }

    if ($plataformaLogin != 'web' && $plataformaLogin != 'app') {
        $errosDados['plataforma_login'] = 'Informe se o login está sendo feito pelo aplicativo ou pelo sistema web!';
    }

    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Ocorreram erros de validação de dados!', 200, $errosDados, false);
        exit;
    }

    $senha = md5($senha);
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();

    if ($plataformaLogin === 'app') {
        // tentativa de login no app do cidadão
        $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');
        $perfilCidadao = $cidadaoDAO->buscarPeloCpfSenha($cpf, $senha);

        if (count($perfilCidadao) === 0) {
            // cpf ou senha inválidos
            RespostaHttp::resposta('Cpf ou senha inválidos!', 200, null, false);
        } else {

            if (!$perfilCidadao['status']) {
                RespostaHttp::resposta('Seu acesso está bloqueado, entre em contato com o administrador do sistema para realizar o desbloqueio!', 200, null, false);
            } else {
                $token = Auth::gerarToken();
                $dadosRetorno = [
                    'token' => $token,
                    'usuario' => $perfilCidadao
                ];
                RespostaHttp::resposta('Login efetuado com sucesso!', 200, $dadosRetorno, true);
            }

        }

    } else {
        // tentativa de login no sistema web
        $perfis = [];
        $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');
        $peritoDAO = new PeritoDAO($conexaoBancoDados, 'tbl_peritos');
        $tecnicoDAO = new TecnicoDAO($conexaoBancoDados, 'tbl_tecnicos');
        $gestorInstituicaoDAO = new GestorInstituicaoDAO($conexaoBancoDados, 'tbl_gestores_instituicao');
        $gestorSecretariaDAO = new GestorSecretariaDAO($conexaoBancoDados, 'tbl_gestores_secretaria');
        $secretarioDAO = new SecretarioDAO($conexaoBancoDados, 'tbl_secretarios');
        $perfilCidadaoUsuario = $cidadaoDAO->buscarPeloCpfSenha($cpf, $senha);
        $perfilPeritoUsuario = $cidadaoDAO->buscarPeloCpfSenha($cpf, $senha);
        $perfilSecretarioUsuario = $secretarioDAO->buscarPeloCpfSenha($cpf, $senha);
        $perfilTecnicoUsuario = $tecnicoDAO->buscarPeloCpfSenha($cpf, $senha);
        $perfilGestorSecretariaUsuario = $gestorSecretariaDAO->buscarPeloCpfSenha($cpf, $senha);
        $perfilGestorInstituicaoUsuario = $gestorInstituicaoDAO->buscarPeloCpfSenha($cpf, $senha);

        if (count($perfilCidadaoUsuario) > 0) {
            $perfis['cidadao'] = $perfilCidadaoUsuario;
        }

        if (count($perfilPeritoUsuario) > 0) {
            $perfis['perito'] = $perfilPeritoUsuario;
        }

        if (count($perfilTecnicoUsuario) > 0) {
            $perfis['tecnico'] = $perfilTecnicoUsuario;
        }
        
        if (count($perfilGestorInstituicaoUsuario) > 0) {
            $perfis['gestor_instituicao'] = $perfilGestorInstituicaoUsuario;
        }

        if (count($perfilGestorSecretariaUsuario) > 0) {
            $perfis['gestor_secretaria'] = $perfilGestorSecretariaUsuario;
        }

        if (count($perfilSecretarioUsuario) > 0) {
            $perfis['secretario'] = $perfilSecretarioUsuario;
        }

        if (count($perfis) === 0) {
            RespostaHttp::resposta('Cpf ou senha inválidos!', 200, null, false);
        } else {
            $token = Auth::gerarToken();
            $dadosRetorno = [
                'token' => $token,
                'perfis' => $perfis
            ];
            RespostaHttp::resposta('Login efetuado com sucesso!', 200, $dadosRetorno, true);
        }

    }

} catch (AuthException $e) {
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se realizar login!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se realizar login!', 200, null, false);
}