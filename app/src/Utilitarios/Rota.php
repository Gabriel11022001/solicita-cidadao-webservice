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
        } elseif ($endpoint === '/cidadao/enviar-codigo-verificacao-email-cidadao') {
            $arquivoCarregar = 'registrarEEnviarCodigoVerificacaoEmailCidadao.php';
        } elseif ($endpoint === '/servico') {
            $arquivoCarregar = 'cadastrarTipoServico.php';
        } elseif ($endpoint === '/instituicao') {
            $arquivoCarregar = 'cadastrarInstituicao.php';
        } elseif ($endpoint === '/usuario') {
            $arquivoCarregar = 'cadastrarUsuario.php';
        } elseif ($endpoint === '/equipe') {
            $arquivoCarregar = 'cadastrarEquipe.php';
        } elseif ($endpoint === '/solicitacao-servico/upload-imagem-solicitacao') {
            // endpoint para fazer o upload da imagem da solicitação de serviço
            $arquivoCarregar = 'realizarUploadImagemSolicitacaoServico.php';
        } else {
            self::requisicaoInvalida();
            
            return;
        }

        self::carregarArquivoRequisicao($arquivoCarregar);
    }

    private static function get($endpoint) {
        $arquivoCarregar = '';

        if (str_contains($endpoint, '/cidadao')) {
         
            switch ($endpoint) {
                case str_contains($endpoint, '/buscar-pelo-id'):
                    $arquivoCarregar = 'buscarCidadaoPeloId.php';
                    break;
                case str_contains($endpoint, '/buscar-pelo-cpf-senha'):
                    $arquivoCarregar = 'buscarCidadaoPeloCpfSenha.php';
                    break;
                case str_contains($endpoint, '/buscar-pelo-cpf'):
                    $arquivoCarregar = 'buscarCidadaoPeloCpf.php';
                    break;
                case str_contains($endpoint, '/buscar-pelo-email'):
                    $arquivoCarregar = 'buscarCidadaoPeloEmail.php';
                    break;
                default:
                    $arquivoCarregar = 'buscarTodosCidadaos.php';
                    break;
            }

        } elseif (str_contains($endpoint, '/servico')) {

            switch ($endpoint) {
                case str_contains($endpoint, '/buscar-pelo-id'):
                    $arquivoCarregar = 'buscarTipoServicoPeloId.php';
                    break;
                case str_contains($endpoint, '/buscar-com-filtro-de-texto'):
                    $arquivoCarregar = 'buscarTiposServicoComFiltroDeTexto.php';
                    break;
                default:
                    $arquivoCarregar = 'buscarTodosTiposServico.php';
                    break;
            }

        } elseif (str_contains($endpoint, '/usuario')) {

            switch ($endpoint) {
                case str_contains($endpoint, '/buscar-perfis-usuario-pelo-cpf-e-senha'):
                    $arquivoCarregar = 'buscarPerfisUsuarioPeloCpfESenha.php';
                    break;
                case str_contains($endpoint, '/buscar-perfis-usuario-pelo-cpf'):
                    $arquivoCarregar = 'buscarPerfisUsuarioPeloCpf.php';
                    break;
                case str_contains($endpoint, '/buscar-pelo-id'):
                    $arquivoCarregar = 'buscarUsuarioPeloId.php';
                    break;
                default:
                    $arquivoCarregar = 'buscarTodosUsuarios.php';
                    break;
            }

        } elseif (str_contains($endpoint, '/instituicao')) {

            switch ($endpoint) {
                case str_contains($endpoint, '/buscar-pelo-id'):
                    $arquivoCarregar = 'buscarInstituicaoPeloId.php';
                    break;
                case str_contains($endpoint, '/buscar-com-filtro-de-texto'):
                    $arquivoCarregar = 'buscarInstituicoesComFiltroDeTexto.php';
                    break;
                default:
                    $arquivoCarregar = 'buscarTodasInstituicoes.php';
                    break;
            }

        } elseif (str_contains($endpoint, '/equipe')) {

            switch ($endpoint) {
                case str_contains($endpoint, '/buscar-pelo-id'):
                    $arquivoCarregar = 'buscarEquipePeloId.php';
                    break;
                case str_contains($endpoint, 'buscar-com-filtro-de-texto'):
                    $arquivoCarregar = 'buscarEquipeComFiltroDeTexto.php';
                    break;
                default:
                    $arquivoCarregar = 'buscarTodasEquipes.php';
                    break;
            }

        } elseif (str_contains($endpoint, '/solicitacao-servico')) {

            switch ($endpoint) {
                case str_contains($endpoint, '/obter-imagem-solicitacao'):
                    $arquivoCarregar = 'obterImagemSolicitacaoServico.php';
                    break;
            }

        } else {
            self::requisicaoInvalida();

            return;
        }

        self::carregarArquivoRequisicao($arquivoCarregar);
    }

    private static function put($endpoint) {
        $arquivoCarregar = '';

        if ($endpoint === '/usuario/alterar-status') {
            $arquivoCarregar = 'alterarStatusUsuario.php';
        } elseif ($endpoint === '/servico/alterar-status') {
            $arquivoCarregar = 'alterarStatusTipoServico.php';
        } elseif ($endpoint === '/servico') {
            $arquivoCarregar = 'editarTipoServico.php';
        } elseif ($endpoint === '/instituicao/alterar-status') {
            $arquivoCarregar = 'alterarStatusInstituicao.php';
        } elseif ($endpoint === '/instituicao') {
            $arquivoCarregar = 'editarInstituicao.php';
        } elseif ($endpoint === '/equipe/alterar-status') {
            $arquivoCarregar = 'alterarStatusEquipe.php';
        } else {
            self::requisicaoInvalida();
            
            return;
        }

        self::carregarArquivoRequisicao($arquivoCarregar);
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