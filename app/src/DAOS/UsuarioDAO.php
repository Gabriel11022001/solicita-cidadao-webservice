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

    public function editar($dadosUsuarioEditar) {
        $query = 'UPDATE tbl_usuarios SET nome = :nome, sobrenome = :sobrenome,
        telefone = :telefone, cpf = :cpf, email = :email, status = :status,
        sexo = :sexo, data_nascimento = :data_nascimento, logradouro = :logradouro,
        complemento = :complemento, cep = :cep, cidade = :cidade, bairro = :bairro,
        estado = :estado, numero_residencia = :numero_residencia
        WHERE id = :id;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':nome', $dadosUsuarioEditar['nome']['dado'], $dadosUsuarioEditar['nome']['tipo_dado']);
        $stmt->bindValue(':sobrenome', $dadosUsuarioEditar['sobrenome']['dado'], $dadosUsuarioEditar['sobrenome']['tipo_dado']);
        $stmt->bindValue(':telefone', $dadosUsuarioEditar['telefone']['dado'], $dadosUsuarioEditar['telefone']['tipo_dado']);
        $stmt->bindValue(':email', $dadosUsuarioEditar['email']['dado'], $dadosUsuarioEditar['email']['tipo_dado']);
        $stmt->bindValue(':sexo', $dadosUsuarioEditar['sexo']['dado'], $dadosUsuarioEditar['sexo']['tipo_dado']);
        $stmt->bindValue(':cpf', $dadosUsuarioEditar['cpf']['dado'], $dadosUsuarioEditar['cpf']['tipo_dado']);
        $stmt->bindValue(':status', $dadosUsuarioEditar['status']['dado'], $dadosUsuarioEditar['status']['tipo_dado']);
        $stmt->bindValue(':data_nascimento', $dadosUsuarioEditar['data_nascimento']['dado'], $dadosUsuarioEditar['data_nascimento']['tipo_dado']);
        $stmt->bindValue(':cep', $dadosUsuarioEditar['cep']['dado'], $dadosUsuarioEditar['cep']['tipo_dado']);
        $stmt->bindValue(':logradouro', $dadosUsuarioEditar['logradouro']['dado'], $dadosUsuarioEditar['logradouro']['tipo_dado']);
        $stmt->bindValue(':complemento', $dadosUsuarioEditar['complemento']['dado'], $dadosUsuarioEditar['complemento']['tipo_dado']);
        $stmt->bindValue(':estado', $dadosUsuarioEditar['estado']['dado'], $dadosUsuarioEditar['estado']['tipo_dado']);
        $stmt->bindValue(':numero_residencia', $dadosUsuarioEditar['numero_residencia']['dado'], $dadosUsuarioEditar['numero_residencia']['tipo_dado']);
        $stmt->bindValue(':cidade', $dadosUsuarioEditar['cidade']['dado'], $dadosUsuarioEditar['cidade']['tipo_dado']);
        $stmt->bindValue(':bairro', $dadosUsuarioEditar['bairro']['dado'], $dadosUsuarioEditar['bairro']['tipo_dado']);
        $stmt->bindValue(':id', $dadosUsuarioEditar['id']['dado'], $dadosUsuarioEditar['id']['tipo_dado']);

        return $stmt->execute();
    }

    public function alterarIdInstituicao($idUsuario, $idNovaInstituicao, $tabela) {
        $query = 'UPDATE ' . $tabela . ' SET instituicao_id = :instituicao_id WHERE usuario_id = :usuario_id;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':instituicao_id', $idNovaInstituicao, PDO::PARAM_INT);
        $stmt->bindValue(':usuario_id', $idUsuario, PDO::PARAM_INT);

        return $stmt->execute();
    }
}