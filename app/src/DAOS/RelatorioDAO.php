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
    // query que retorna a quantidade de solicitações que o perito já aprovou
    private $qSolicitacoesPeritoJaAprovou = 'SELECT tblp.qtd_solicitacoes_aprovou FROM tbl_peritos AS tblp,
    tbl_usuarios AS tblu
    WHERE tblu.id = tblp.usuario_id
    AND tblp.usuario_id = :usuario_id';
    // query que retorna a quantidade de solicitações que o perito já reprovou
    private $qSolicitacoesPeritoJaReprovou = 'SELECT tblp.qtd_solicitacoes_reprovou FROM tbl_peritos AS tblp,
    tbl_usuarios AS tblu
    WHERE tblu.id = tblp.usuario_id
    AND tblp.usuario_id = :usuario_id;';
    private $qSolicitacoesPeritoAguardandoAnaliseDoMesmo = "SELECT COUNT(*) AS qtd_solicitacoes_aguardando_analise_perito
    FROM tbl_solicitacoes_servico AS tbls, tbl_usuarios AS tblu, tbl_peritos AS tblp WHERE tblu.id = tblp.usuario_id
    AND tbls.perito_id = tblp.id
    AND tblp.usuario_id = :usuario_id
    AND tbls.status = 'Aguardando análise do perito';";
    // query que retorna a quantidade total de solicitações concluídas
    private $qSolicitacoesConcluidasGestorSecretariaSecretario = "SELECT COUNT(*) AS qtd_solicitacoes_concluidas
    FROM tbl_solicitacoes_servico WHERE status = 'Concluído';";
    // query que retorna a quantidade total de solicitações canceladas
    private $qSolicitacoesCanceladasGestorSecretariaSecretario = "SELECT COUNT(*) AS qtd_solicitacoes_canceladas
    FROM tbl_solicitacoes_servico WHERE status = 'Cancelado';";
    // query que retorna a quantidade total de solicitações aguardando encaminhamento
    private $qSolicitacoesAguardandoEncaminhamentoGestorSecretariaSecretario = "SELECT COUNT(*) AS qtd_solicitacoes_aguardando_encaminhamento
    FROM tbl_solicitacoes_servico WHERE status = 'Aguardando encaminhamento';";
    // query que retorna a quantidade total de solicitações aguardando análise do perito
    private $qSolicitacoesAguardandoAnalisePeritoGestorSecretariaSecretario = "SELECT COUNT(*) AS qtd_solicitacoes_aguardando_analise_perito
    FROM tbl_solicitacoes_servico WHERE status = 'Aguardando análise do perito';";
    // query que retorna a quantidade total de solicitações aprovadas pelo perito
    private $qSolicitacoesAprovadasPeloPeritoGestorSecretariaSecretario = "SELECT COUNT(*) AS qtd_solicitacoes_aprovadas_pelo_perito
    FROM tbl_solicitacoes_servico WHERE status = 'Aprovado pelo perito';";
    // query que retorna a quantidade total de solicitações reprovadas pelo perito
    private $qSolicitacoesReprovadasPeloPeritoGestorSecretariaSecretario = "SELECT COUNT(*) AS qtd_solicitacoes_reprovadas_pelo_perito
    FROM tbl_solicitacoes_servico WHERE status = 'Reprovado pelo perito';";
    // query que retorna a quantidade total de solicitações aguardando encaminhamento a equipe responsável
    private $qSolicitacoesAguardandoEncaminhamentoEquipeResponsavelGestorSecretariaSecretario = "SELECT COUNT(*) AS qtd_solicitacoes_aguardando_encaminhamento_equipe_responsavel
    FROM tbl_solicitacoes_servico WHERE status = 'Aguardando encaminhamento a equipe responsável';";
    // query que retorna a quantidade total de solicitações aguardando tratamento
    private $qSolicitacoesAguardandoTratamentoGestorSecretariaSecretario = "SELECT COUNT(*) AS qtd_solicitacoes_aguardando_tratamento
    FROM tbl_solicitacoes_servico WHERE status = 'Aguardando tratamento';";
    // query que retorna a quantidade de solicitações da equipe que estão aguardando tratamento
    private $qSolicitacoesAguardandoTratamentoEquipeTecnico = "SELECT COUNT(*) AS qtd_solicitacoes_aguardando_tratamento FROM tbl_solicitacoes_servico
    WHERE equipe_id = :equipe_id
    AND status = 'Aguardando tratamento';";
    // query que retorna a quantidade de solicitações que já foram tratadas pela equipe
    private $qSolicitacoesJaForamTratadasEquipeTecnico = "SELECT COUNT(*) AS qtd_solicitacoes_ja_foram_tratadas
    FROM tbl_solicitacoes_servico WHERE equipe_id = :equipe_id
    AND status = 'Concluído';";
    // query que retorna a quantidade de solicitações que já foram canceladas pela equipe
    private $qSolicitacoesJaForamCanceladasEquipeTecnico = "SELECT COUNT(*) AS qtd_solicitacoes_ja_foram_canceladas
    FROM tbl_solicitacoes_servico WHERE equipe_id = :equipe_id
    AND status = 'Cancelado';";
    // query que retorna a média das notas das solicitações da equipe
    private $qSolicitacoesMediaNotasEquipeTecnico = "SELECT ROUND(AVG(nota_avaliativa)::numeric, 2) AS nota_media FROM tbl_solicitacoes_servico
    WHERE nota_avaliativa IS NOT NULL
    AND status = 'Concluído'
    AND equipe_id = :equipe_id;";
    // query que retorna a quantidade de solicitações da equipe aguardando tratamento que ultrapassaram a data limite para tratamento
    private $qSolicitacoesStatusAguardandoTratamentoQueUltrapassaramDataLimiteTratamento = "SELECT COUNT(*) AS qtd_solicitacoes_extrapolaram_data_limite_tratamento
    FROM tbl_solicitacoes_servico
    WHERE status = 'Aguardando tratamento'
    AND equipe_id = :equipe_id
    AND data_limite_para_tratamento IS NOT NULL
    AND TO_CHAR(CURRENT_DATE, 'YYYY-MM-DD') > TO_CHAR(data_limite_para_tratamento, 'YYYY-MM-DD');";

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
        $dados = [];
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesPeritoAguardandoAnaliseDoMesmo);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesPeritoJaAprovou);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesPeritoJaReprovou);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $dados;
    }

    public function obterDadosQuantitativosGestorSecretariaSecretario() {
        $dados = [];
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesConcluidasGestorSecretariaSecretario);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesCanceladasGestorSecretariaSecretario);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesAguardandoEncaminhamentoGestorSecretariaSecretario);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesAguardandoAnalisePeritoGestorSecretariaSecretario);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesAguardandoTratamentoGestorSecretariaSecretario);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesAprovadasPeloPeritoGestorSecretariaSecretario);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesReprovadasPeloPeritoGestorSecretariaSecretario);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesAguardandoEncaminhamentoEquipeResponsavelGestorSecretariaSecretario);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);

        return $dados;
    }   

    public function obterDadosQuantitativosGestorInstituicao($idInstituicao) {

    }

    public function obterDadosQuantitativosTecnico($idEquipe) {
        $dados = [];
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesAguardandoTratamentoEquipeTecnico);
        $stmt->bindValue(':equipe_id', $idEquipe, PDO::PARAM_INT);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesJaForamTratadasEquipeTecnico);
        $stmt->bindValue(':equipe_id', $idEquipe, PDO::PARAM_INT);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesStatusAguardandoTratamentoQueUltrapassaramDataLimiteTratamento);
        $stmt->bindValue(':equipe_id', $idEquipe, PDO::PARAM_INT);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesJaForamCanceladasEquipeTecnico);
        $stmt->bindValue(':equipe_id', $idEquipe, PDO::PARAM_INT);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $this->conexaoBancoDados->prepare($this->qSolicitacoesMediaNotasEquipeTecnico);
        $stmt->bindValue(':equipe_id', $idEquipe, PDO::PARAM_INT);
        $stmt->execute();
        $dados[] = $stmt->fetch(PDO::FETCH_ASSOC);

        return $dados;
    }
}