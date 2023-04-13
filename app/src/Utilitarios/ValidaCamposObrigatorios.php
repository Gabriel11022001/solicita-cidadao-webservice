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
}