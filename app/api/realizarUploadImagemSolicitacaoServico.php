<?php

use SistemaSolicitacaoServico\App\Exceptions\ExtensaoArquivoInvalidoException;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\UploadArquivo;

try {

    if (!isset($_FILES['imagem_solicitacao_servico'])) {
        RespostaHttp::resposta('Informe a imagem da solicitação de serviço!', 200, null, false);
        exit;
    }

    if (empty($_FILES['imagem_solicitacao_servico']['name'])) {
        RespostaHttp::resposta('Informe a imagem da solicitação de serviço!', 200, null, false);
        exit;
    }

    $arquivo = $_FILES['imagem_solicitacao_servico'];
    $resultadoUpload = UploadArquivo::realizarUploadDeImagem($arquivo);
    
    if ($resultadoUpload) {
        RespostaHttp::resposta('Upload realizado com sucesso!', 200, $resultadoUpload, true);
    } else {
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se realizar o upload da imagem da solicitação de serviço!', 200, null, false);
    }

} catch (ExtensaoArquivoInvalidoException $e) {
    var_dump($e->getMessage());
} catch (Exception $e) {

}