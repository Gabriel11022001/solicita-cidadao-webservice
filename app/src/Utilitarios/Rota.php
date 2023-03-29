<?php

namespace SistemaSolicitacaoServico\App\Utilitarios;

class Rota
{
    
    public static function implementarRequisicao($endpoint, $metodoHttp) {

        if ($metodoHttp === 'POST') {
            // requisições post
            self::post($endpoint);
            return;
        }

        if ($metodoHttp === 'GET') {
            // requisições get
            self::get($endpoint);
            return;
        }

        if ($metodoHttp === 'DELETE') {
            // requisições delete
            self::delete($endpoint);
            return;
        }

        if ($metodoHttp === 'PUT') {
            // requisições put
            self::put($endpoint);
            return;
        }

    }

    private static function post($endpoint) {

    }

    private static function get($endpoint) {
        $arquivoCarregar = '';

        if ($endpoint === '/cidadaos') {
            $arquivoCarregar = 'buscarTodosCidadaos.php';
        } elseif ($endpoint === '/tecnicos-instituicao') {
            $arquivoCarregar = 'buscarTodosTecnicosInstituicoes.php';
        } elseif ($endpoint === '/gestores-secretaria') {
            $arquivoCarregar = 'buscarTodosGestoresSecretaria.php';
        } elseif ($endpoint === '/secretarios-secretaria') {
            $arquivoCarregar = 'buscarTodosSecretariosSecretaria.php';
        } elseif ($endpoint === '/gestores-instituicoes') {
            $arquivoCarregar = 'buscarTodosGestoresInstituicoes.php';
        } elseif ($endpoint === '/peritos') {
            $arquivoCarregar = 'buscarTodosPeritos.php';
        } else {
            self::requisicaoInvalida();
            return;
        }

        self::carregarArquivoRequisicao($arquivoCarregar);
    }

    private static function put($endpoint) {
        
    }

    private static function delete($endpoint) {

    }

    private static function requisicaoInvalida() {
        $respostaHttpRequisicaoNaoEncontrada = new RespostaHttp();
        $respostaHttpRequisicaoNaoEncontrada->definirParametrosResposta('Requisição inválida!', 404);
        $respostaHttpRequisicaoNaoEncontrada->resposta();
    }

    private static function carregarArquivoRequisicao($arquivoCarregar) {

        if ($arquivoCarregar != '') {
            $arquivoCarregar = 'api/' . $arquivoCarregar;

            if (file_exists($arquivoCarregar)) {
                require_once $arquivoCarregar;
            }

        }

    }
}