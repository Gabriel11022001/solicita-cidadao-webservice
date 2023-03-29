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
        $arquivoCarregar = '';

        if ($endpoint === '/cidadao') {
            $arquivoCarregar = 'cadastrarCidadao.php';
        } else {
            self::requisicaoInvalida();

            return;
        }

        self::carregarArquivoRequisicao($arquivoCarregar);
    }

    private static function get($endpoint) {
        $arquivoCarregar = '';

        if ($endpoint === '/cidadao') {
            $arquivoCarregar = 'buscarTodosCidadaos.php';
        } elseif ($endpoint === '/tecnico') {
            $arquivoCarregar = 'buscarTodosTecnicosInstituicoes.php';
        } elseif ($endpoint === '/gestor-secretaria') {
            $arquivoCarregar = 'buscarTodosGestoresSecretaria.php';
        } elseif ($endpoint === '/secretario-secretaria') {
            $arquivoCarregar = 'buscarTodosSecretariosSecretaria.php';
        } elseif ($endpoint === '/gestor-instituicao') {
            $arquivoCarregar = 'buscarTodosGestoresInstituicoes.php';
        } elseif ($endpoint === '/perito') {
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
        RespostaHttp::resposta('Endpoint inválido!', null, 404);
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