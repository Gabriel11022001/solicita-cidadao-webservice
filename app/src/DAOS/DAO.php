<?php

namespace SistemaSolicitacaoServico\App\DAOS;

use Exception;
use PDO;

abstract class DAO
{
    
    private $tabelasExistentes = [
        'tbl_cidadaos',
        'tbl_peritos',
        'tbl_gestores_secretaria',
        'tbl_gestores_instituicao',
        'tbl_secretarios',
        'tbl_tecnicos',
        'tbl_servicos',
        'tbl_notificacoes',
        'tbl_solicitacoes_servico',
        'tbl_laudos',
        'tbl_evidencias',
        'tbl_motivos_cancelamentos'
    ];
    protected $conexaoBancoDados;
    protected $nomeTabela;

    public function __construct($conexaoBancoDados, $nomeTabela) {
        $this->conexaoBancoDados = $conexaoBancoDados;
        $this->validarSeTabelaEValida($nomeTabela);
        $this->nomeTabela = $nomeTabela;
    }

    private function validarSeTabelaEValida($nomeTabela) {

        if (!in_array($nomeTabela, $this->tabelasExistentes)) {
            throw new Exception('Essa tabela não existe no banco de dados!');
        }

    }

    public function salvar($dados) {
        $query = 'INSERT INTO ' . $this->nomeTabela . '(';
        $indiceColuna = 0;
        $tamanhoArrayDados = count($dados);
        $colunas = array_keys($dados);

        foreach ($colunas as $coluna) {
            
            if ($indiceColuna === ($tamanhoArrayDados - 1)) {
                $query .= $coluna;
            } else {
                $query .= $coluna . ', ';
            }

            $indiceColuna++;
        }

        $indiceColuna = 0;
        $query .= ') VALUES(';

        foreach ($colunas as $coluna) {
            
            if ($indiceColuna === ($tamanhoArrayDados - 1)) {
                $query .= ':' . $coluna;
            } else {
                $query .= ':' . $coluna . ', ';
            }

            $indiceColuna++;
        }

        $query .= ');';
        $stmt = $this->conexaoBancoDados->prepare($query);

        foreach ($colunas as $coluna) {
            $colunaBind = ':' . $coluna;
            $dado = $dados[$coluna]['dado'];
            $tipoDado = $dados[$coluna]['tipo_dado'];
            $stmt->bindValue($colunaBind, $dado, $tipoDado);
        }

        return $stmt->execute();
    }
    
    public function buscarTodos() {
        $query = 'SELECT * FROM ' . $this->nomeTabela . ';';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPeloId($id) {
        $query = 'SELECT * FROM ' . $this->nomeTabela . ' WHERE id = :id;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $entidade = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($entidade === false) {

            return [];
        }

        return $entidade;
    }
}