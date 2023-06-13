<?php

namespace SistemaSolicitacaoServico\App\DAOS;

use PDO;

class UsuarioDAO extends DAO    
{
    
    public function __construct($conexaoBancoDados, $nomeTabela) {
        parent::__construct($conexaoBancoDados, $nomeTabela);
    }

    public function buscarPeloCpfSenha($cpf, $senha) {
        $query = 'SELECT * FROM tbl_usuarios AS u
        INNER JOIN ' . $this->nomeTabela . ' AS fu
        ON u.id = fu.usuario_id AND u.cpf = :cpf AND u.senha = :senha;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':cpf', $cpf, PDO::PARAM_STR);
        $stmt->bindValue(':senha', $senha, PDO::PARAM_STR);
        $stmt->execute();
        $entidade = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($entidade === false) {

            return [];
        }

        return $entidade;
    }

    public function buscarPeloCpf($cpf) {
        $query = 'SELECT * FROM tbl_usuarios AS u INNER JOIN ' . $this->nomeTabela . ' AS fu ON u.id = fu.usuario_id AND u.cpf = :cpf;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':cpf', $cpf);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function alterarStatusUsuario($id, $novoStatus) {
        $query = 'UPDATE ' . $this->nomeTabela . ' SET status = :status WHERE id = :id;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':status', $novoStatus, PDO::PARAM_BOOL);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    public function buscarPeloEmail($email) {
        $query = 'SELECT * FROM tbl_usuarios AS u INNER JOIN ' . $this->nomeTabela . ' AS fu ON u.email = :email AND u.id = fu.usuario_id;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarUsuarioPeloIdESenha($id, $senha) {
        $query = 'SELECT id, senha FROM ' . $this->nomeTabela . ' WHERE id = :id AND senha = :senha;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':senha', $senha, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function alterarSenhaUsuario($id, $novaSenha) {
        $query = 'UPDATE ' . $this->nomeTabela . ' SET senha = :nova_senha WHERE id = :id;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':nova_senha', $novaSenha, PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function buscarUsuarioPeloId($id) {
        $query = 'SELECT * FROM tbl_usuarios AS tblu INNER JOIN ' . $this->nomeTabela . ' AS tblfu
        ON tblu.id = tblfu.usuario_id AND tblu.id = :id;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}