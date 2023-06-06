<?php

namespace SistemaSolicitacaoServico\App\Utilitarios;

use SistemaSolicitacaoServico\App\Exceptions\ExtensaoArquivoInvalidoException;

class UploadArquivo
{

    public static function realizarUploadDeImagem($arquivo) {
        $novoNomeArquivo = md5(date('d-m-Y H:i:s') . $arquivo['name']);
        $extensao = '';

        if ($arquivo['type'] === 'image/png') {
            $extensao = '.png';
        } elseif ($arquivo['type'] === 'image/jpeg') {
            $extensao = '.jpeg';
        } else {
            throw new ExtensaoArquivoInvalidoException('O arquivo deve possuir extensão .png ou .jpeg!');
        }

        $novoNomeArquivo .= $extensao;
        $caminhoTemporario = $arquivo['tmp_name'];
        $destino = 'images/' . $novoNomeArquivo;
        
        if (move_uploaded_file($caminhoTemporario, $destino)) {

            return [
                'nome_arquivo' => $novoNomeArquivo
            ];
        }
        
        return false;   
    }

    /**
     * Método que serve para deletar um arquivo no servidor,
     * geralmente, invocado quando ocorre um erro no cadastro ou edição de
     * uma solicitação de serviço
     */
    public static function deletarArquivoServidor($nomeArquivo) {

    }
}