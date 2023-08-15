<?php

namespace SistemaSolicitacaoServico\App\DAOS;

use PDO;

class RelatorioDAO
{
    private $conexaoBancoDados;
    // query para buscar a quantidade de solicitações concluídas do cidadão
    private $qSolicitacoesConcluidasCidadao = "SELECT COUNT(*) AS qtd_solicitacoes_concluidas
    FROM tbl_solicitacoes_servico AS tbls, tbl_usuarios AS tblu,
    tbl_cidadaos AS tblc
    WHERE tbls.cidadao_id = tblc.id
    AND tblc.usuario_id = tblu.id
    AND tblu.id = :usuario_id
    AND tbls.status = 'Concluído';";
    // query para buscar a quantidade de solicitações canceladas do cidadão
    private $qSolicitacoesCanceladasCidadao = "SELECT COUNT(*) AS qtd_solicitacoes_concluidas
    FROM tbl_solicitacoes_servico AS tbls, tbl_usuarios AS tblu,
    tbl_cidadaos AS tblc
    WHERE tbls.cidadao_id = tblc.id
    AND tblc.usuario_id = tblu.id
    AND tblu.id = :usuario_id
    AND tbls.status = 'Cancelado';";

    public function __construct($conexaoBancoDados) {
        $this->conexaoBancoDados = $conexaoBancoDados;
    }

    public function obterDadosQuantitativosCidadao($usuarioId) {

    }

    public function obterDadosQuantitativosPerito($usuarioId) {

    }

    public function obterDadosQuantitativosGestorSecretariaSecretario() {

    }

    public function obterDadosQuantitativosGestorInstituicao($idInstituicao) {

    }

    public function obterDadosQuantitativosTecnico($idEquipe) {

    }
}