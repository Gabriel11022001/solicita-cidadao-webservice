<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\EquipeDAO;
use SistemaSolicitacaoServico\App\Entidades\Equipe;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCamposObrigatorios;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

try {
    Auth::validarToken();
    $equipe = new Equipe();
    $equipe->setNome(mb_strtoupper(trim(ParametroRequisicao::obterParametro('nome'))));
    $equipe->setDescricao(trim(ParametroRequisicao::obterParametro('descricao')));
    $errosCampos = ValidaCamposObrigatorios::validarFormularioCadastrarEquipe($equipe);
    
    if (count($errosCampos) > 0) {
        RespostaHttp::resposta('Preencha todos os campos obrigatórios!', 200, $errosCampos, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $equipeDAO = new EquipeDAO($conexaoBancoDados, 'tbl_equipes');

    // validando se já existe uma outra equipe cadastrada com esse nome
    if ($equipeDAO->buscarEquipePeloNome($equipe->getNome())) {
        RespostaHttp::resposta('Já existe outra equipe cadastrada com o nome informado, informe outro nome!', 200, null, false);
        exit;
    }

    $dadosCadastrarEquipe = [
        'nome' => [ 'dado' => $equipe->getNome(), 'tipo_dado' => PDO::PARAM_STR ],
        'descricao' => [ 'dado' => $equipe->getDescricao(), 'tipo_dado' => PDO::PARAM_STR ]
    ];
    
    if ($equipeDAO->salvar($dadosCadastrarEquipe)) {
        $idEquipe = intval($conexaoBancoDados->lastInsertId());
        $dadosRetorno = [
            'id' => $idEquipe,
            'nome' => $equipe->getNome(),
            'descricao' => $equipe->getDescricao()
        ];
        RespostaHttp::resposta('Equipe cadastrada com sucesso!', 201, $dadosRetorno, true);
    } else {
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar a equipe!', 200, null, false);
    }
    
} catch (AuthException $e) {
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se cadastrar a equipe!', $e->getMessage());
}