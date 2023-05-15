<?php

namespace SistemaSolicitacaoServico\App\Utilitarios;

class ValidaCamposObrigatorios
{

    public static function validarFormularioCadastroCidadao(
        $nome,
        $sobrenome,
        $email,
        $telefone,
        $cpf,
        $sexo,
        $dataNascimento,
        $logradouro,
        $cep,
        $bairro,
        $cidade,
        $unidadeFederativa,
        $senha,
        $senhaConfirmacao
    ) {
        $erros = [];

        if (empty($nome)) {
            $erros['nome'] = 'O nome é um dado obrigatório!';
        }

        if (empty($sobrenome)) {
            $erros['sobrenome'] = 'O sobrenome é um dado obrigatório!';
        }

        if (empty($telefone)) {
            $erros['telefone'] = 'O telefone é um dado obrigatório!';
        }

        if (empty($email)) {
            $erros['email'] = 'O e-mail é um dado obrigatório!';
        }

        if (empty($cpf)) {
            $erros['cpf'] = 'O cpf é um dado obrigatório!';
        }

        if (empty($sexo)) {
            $erros['sexo'] = 'O sexo é um dado obrigatório!';
        }

        if (empty($dataNascimento)) {
            $erros['data_nascimento'] = 'A data de nascimento é um dado obrigatório!';
        }

        if (empty($logradouro)) {
            $erros['logradouro'] = 'O logradouro é um dado obrigatório!';
        }

        if (empty($cep)) {
            $erros['cep'] = 'O cep é um dado obrigatório!';
        }

        if (empty($bairro)) {
            $erros['bairro'] = 'O bairro é um dado obrigatório!';
        }

        if (empty($cidade)) {
            $erros['cidade'] = 'A cidade é um dado obrigatório!';
        }

        if (empty($unidadeFederativa)) {
            $erros['unidade_federativa'] = 'A unidade federativa é um dado obrigatório!';
        }

        if (empty($senha)) {
            $erros['senha'] = 'A senha é um dado obrigatório!';
        }

        if (empty($senhaConfirmacao)) {
            $erros['senha_confirmacao'] = 'A senha de confirmação é um dado obrigatório!';
        }

        return $erros;
    }

    public static function validarFormularioEditarTipoServico($tipoServico) {
        $erros = [];

        if (empty($tipoServico->getId())) {
            $erros['id'] = 'Informe o id do tipo de serviço!';
        }

        if (empty($tipoServico->getNome())) {
            $erros['nome'] = 'Informe o nome do tipo de serviço!';
        }

        if (empty($tipoServico->getDescricao())) {
            $erros['descricao'] = 'Informe a descrição do tipo de serviço!';
        }

        return $erros;
    }

    public static function validarFormularioCadastrarInstituicao($instituicao) {
        $erros = [];

        if (empty(trim($instituicao->getNome()))) {
            $erros['nome'] = 'Informe o nome da instituição!';
        }

        if (empty(trim($instituicao->getDescricao()))) {
            $erros['descricao'] = 'Informe a descrição da instituição!';
        }
        
        if (empty(trim($instituicao->getEndereco()->getLogradouro()))) {
            $erros['logradouro'] = 'Informe o logradouro do endereço da instituição!';
        }

        if (empty(trim($instituicao->getEndereco()->getBairro()))) {
            $erros['bairro'] = 'Informe o bairro do endereço da instituição!';
        }

        if (empty(trim($instituicao->getEndereco()->getCidade()))) {
            $erros['cidade'] = 'Informe a cidade do endereço da instituição!';
        }

        if (empty(trim($instituicao->getEndereco()->getCep()))) {
            $erros['cep'] = 'Informe o cep do endereço da instituição!';
        }

        if (empty($instituicao->getCnpj())) {
            $erros['cnpj'] = 'Informe o cnpj da instituição!';
        }

        if (empty($instituicao->getTelefone())) {
            $erros['telefone'] = 'Informe o telefone da instituição!';
        }
        
        if (empty($instituicao->getEmail())) {
            $erros['email'] = 'Informe o email da instituição!';
        }

        if (empty(trim($instituicao->getEndereco()->getEstado()))) {
            $erros['estado'] = 'Informe a unidade federativa da instituição!';
        }

        return $erros;
    }

    public static function validarFormularioEditarInstituicao($instituicao) {
        $erros = [];

        if (empty(trim($instituicao->getNome()))) {
            $erros['nome'] = 'Informe o nome da instituição!';
        }

        if (empty(trim($instituicao->getDescricao()))) {
            $erros['descricao'] = 'Informe a descrição da instituição!';
        }

        if (empty(trim($instituicao->getEndereco()->getLogradouro()))) {
            $erros['logradouro'] = 'Informe o logradouro do endereço da instituição!';
        }

        if (empty(trim($instituicao->getEndereco()->getBairro()))) {
            $erros['bairro'] = 'Informe o bairro do endereço da instituição!';
        }

        if (empty(trim($instituicao->getEndereco()->getCidade()))) {
            $erros['cidade'] = 'Informe a cidade do endereço da instituição!';
        }

        if (empty(trim($instituicao->getEndereco()->getCep()))) {
            $erros['cep'] = 'Informe o cep do endereço da instituição!';
        }

        if (empty($instituicao->getCnpj())) {
            $erros['cnpj'] = 'Informe o cnpj da instituição!';
        }

        if (empty($instituicao->getTelefone())) {
            $erros['telefone'] = 'Informe o telefone da instituição!';
        }
        
        if (empty($instituicao->getEmail())) {
            $erros['email'] = 'Informe o email da instituição!';
        }

        if (empty(trim($instituicao->getEndereco()->getEstado()))) {
            $erros['estado'] = 'Informe a unidade federativa da instituição!';
        }

        if (empty(trim($instituicao->getId()))) {
            $erros['id'] = 'Informe o id da instituição!';
        }

        return $erros;
    }

    public static function validarFormularioCadastrarUsuario($usuario, $tipoUsuarioCadastrar) {
        $errosFormulario = [];

        if (empty($usuario->getNome())) {
            $errosFormulario['nome'] = 'Informe o nome do usuário!';
        }

        if (empty($usuario->getSobrenome())) {
            $errosFormulario['sobrenome'] = 'Informe o sobrenome do usuário!';
        }

        return $errosFormulario;
    }
}