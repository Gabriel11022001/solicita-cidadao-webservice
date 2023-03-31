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
        } elseif ($endpoint === '/cidadao/buscar-pelo-cpf-senha') {
            $arquivoCarregar = 'buscarCidadaoPeloCpfSenha.php';
        } elseif (str_contains($endpoint, '/cidadao/buscar-pelo-id')) {
            $arquivoCarregar = 'buscarCidadaoPeloId.php';
        } elseif (str_contains($endpoint, '/cidadao/buscar-pelo-cpf')) {
            $arquivoCarregar = 'buscarCidadaoPeloCpf.php';
        } elseif (str_contains($endpoint, '/usuario/buscar-perfis-usuario-pelo-cpf')) {
            $arquivoCarregar = 'buscarPerfisUsuarioPeloCpf.php';
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
        RespostaHttp::resposta('Endpoint inválido!', 404, null);
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