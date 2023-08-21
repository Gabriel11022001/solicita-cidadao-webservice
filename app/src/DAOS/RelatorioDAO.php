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
    private $qSolicitacoesCanceladasCidadao = "SELECT COUNT(*) AS qtd_solicitacoes_canceladas
    FROM tbl_solicitacoes_servico AS tbls, tbl_usuarios AS tblu,
    tbl_cidadaos AS tblc
    WHERE tbls.cidadao_id = tblc.id
    AND tblc.usuario_id = tblu.id
    AND tblu.id = :usuario_id
    AND tbls.status = 'Cancelado';";
    // query que retorna a quantidade de solicitações do cidadão com status "Aguardando encaminhamento"
    private $qSolicitacoesAguardandoEncaminhamentoCidadao = "SELECT COUNT(*) AS qtd_solicitacoes_aguardando_encaminhamento
    FROM tbl_solicitacoes_servico AS tbls, tbl_usuarios AS tblu,
    tbl_cidadaos AS tblc
    WHERE tbls.cidadao_id = tblc.id
    AND tblc.usuario_id = tblu.id
    AND tblu.id = :usuario_id
    AND tbls.status = 'Aguardando encaminhamento';";
    // query que retorna a quantidade de solicitações do cidadão com status "Aguardando análise do perito"
    private $qSolicitacoesAguardandoAnalisePeritoCidadao = "SELECT COUNT(*) AS qtd_solicitacoes_aguardando_analise_perito
    FROM tbl_solicitacoes_servico AS tbls, tbl_usuarios AS tblu,
    tbl_cidadaos AS tblc
    WHERE tbls.cidadao_id = tblc.id
    AND tblc.usuario_id = tblu.id
    AND tblu.id = :usuario_id
    AND tbls.status = 'Aguardando análise do perito';";
    // query que retorna a quantidade de solicitações do cidadão com status "Aguardando encaminhamento a equipe responsável"
    private $qSolicitacoesAguardandoEncaminhamentoAEquipeResponsavelCidadao = "SELECT COUNT(*) AS qtd_solicitacoes_aguardando_encaminhamento_equipe_responsavel
    FROM tbl_solicitacoes_servico AS tbls, tbl_usuarios AS tblu,
    tbl_cidadaos AS tblc
    WHERE tbls.cidadao_id = tblc.id
    AND tblc.usuario_id = tblu.id
    AND tblu.id = :usuario_id
    AND tbls.status = 'Aguardando encaminhamento a equipe responsável';";
    // query que retorna a quantidade de solicitações do cidadão com status "Aguardando tratamento"
    private $qSolicitacoesAguardandoTratamentoCidadao = "SELECT COUNT(*) AS qtd_solicitacoes_aguardando_tratamento
    FROM tbl_solicitacoes_servico AS tbls, tbl_usuarios AS tblu,
    tbl_cidadaos AS tblc
    WHERE tbls.cidadao_id = tblc.id
    AND tblc.usuario_id = tblu.id
    AND tblu.id = :usuario_id
    AND tbls.status = 'Aguardando tratamento';";
    // query que retorna a quantidade de solicitações do cidadão com status "Aprovado pelo perito"
    private $qSolicitacoesAprovadasPeloPeritoCidadao = "SELECT COUNT(*) AS qtd_solicitacoes_aprovadas_pelo_perito
    FROM tbl_solicitacoes_servico AS tbls, tbl_usuarios AS tblu,
    tbl_cidadaos AS tblc
    WHERE tbls.cidadao_id = tblc.id
    AND tblc.usuario_id = tblu.id
    AND tblu.id = :usuario_id
    AND tbls.status = 'Aprovado pelo perito';";
    private $qSolicitacoesReprovadasPeloPeritoCidadao = "SELECT COUNT(*) AS qtd_solicitacoes_reprovadas_pelo_perito
    FROM tbl_solicitacoes_servico AS tbls, tbl_usuarios AS tblu,
    tbl_cidadaos AS tblc
    WHERE tbls.cidadao_id = tblc.id
    AND tblc.usuario_id = tblu.id
    AND tblu.id = :usuario_id
    AND tbls.status = 'Reprovado pelo perito';";

    public function __construct($conexaoBancoDados) {
        $this->conexaoBancoDados = $conexaoBancoDados;
    }
    
    public function obterDadosQuantitativosCidadao($usuarioId) {
        $dados = [];
        // solicitações concluídas
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesConcluidasCidadao);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);
        // solicitações canceladas
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesCanceladasCidadao);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);
        // solicitações aguardando encaminhamento
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesAguardandoEncaminhamentoCidadao);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);
        // solicitações aguardando encaminhamento a equipe responsável
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesAguardandoEncaminhamentoAEquipeResponsavelCidadao);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);
        // solicitações aprovadas pelo perito
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesAprovadasPeloPeritoCidadao);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);
        // solicitações reprovadas pelo perito
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesReprovadasPeloPeritoCidadao);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);
        // solicitações aguardando tratamento
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesAguardandoTratamentoCidadao);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);
        // solicitações aguardando análise do perito
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesAguardandoAnalisePeritoCidadao);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);

        return $dados;
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