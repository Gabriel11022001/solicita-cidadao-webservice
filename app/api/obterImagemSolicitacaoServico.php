<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    Auth::validarToken();
    
    if (!isset($_GET['nome_imagem'])) {
        RespostaHttp::resposta('Parâmetro não definido na url!', 200, null, false);
        exit;
    }

    $nomeImagem = trim($_GET['nome_imagem']);
    
    if (empty($nomeImagem)) {
        RespostaHttp::resposta('Informe o nome da imagem da solicitação de serviço!', 200, null, false);
        exit;
    }

    $ext = explode('.', $nomeImagem)[1];
    $caminhoParaImagem = 'images/' . $nomeImagem;
    
    if (file_exists($caminhoParaImagem)) {
        $conteudoImagem = file_get_contents($caminhoParaImagem);

        if ($conteudoImagem) {
            $imagemBase64 = base64_encode($conteudoImagem);
            RespostaHttp::resposta('Leitura de imagem realizado com sucesso!', 200, [
                'imagem_base_64' => $imagemBase64,
                'ext' => $ext
            ], true);
        } else {
            RespostaHttp::resposta('Ocorreu um erro ao tentar-se realizar a leitura do arquivo de imagem em questão!', 200, null, false);
        }

    } else {
        RespostaHttp::resposta('Essa imagem não está salva no servidor!', 200, null, false);
    }

} catch (AuthException $e) {
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    
}