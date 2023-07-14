function apresentarSolicitacoesNaTabela(solicitacoes, tipoListagem) {
    $('.corpo-tabela-listagem-solicitacoes').empty();
    const corpoListagemSolicitacoes = document.querySelector('.corpo-tabela-listagem-solicitacoes');
    
    if (tipoListagem === 'l_cidadao') {

        for (sol of solicitacoes) {
            const linhaSolicitacao = document.createElement('tr');
            const colunaProtocolo = document.createElement('td');
            // const colunaTitulo = document.createElement('td');
            const colunaStatus = document.createElement('td');
            // const colunaPrioridade = document.createElement('td');
            const colunaPosicaoFila = document.createElement('td');
            const colunaDataEnvio = document.createElement('td');
            const colunaOperacoes = document.createElement('td');
            // colunaTitulo.innerText = retornarLimitadoQtdCaracteres(sol.titulo);
            colunaStatus.innerText = sol.status;
            colunaDataEnvio.innerText = sol.data_registro;

            if (sol.posicao_fila === -1) {
                colunaPosicaoFila.innerText = '-----';
            } else {
                colunaPosicaoFila.innerText = sol.posicao_fila;
            }

            // colunaPrioridade.innerText = sol.prioridade;
            colunaProtocolo.innerText = retornarLimitadoQtdCaracteres(sol.protocolo, 20);
            colunaOperacoes.innerHTML = `
                <button type="button" class="btn-operacao" onClick="visualizarDadosSolicitacao(${sol.id});" data-toggle="modal" data-target="#modal-visualizar-solicitacao"><i class="fa-solid fa-eye"></i> Visualizar</button>
            `;

            if (sol.status != 'Concluído'
            && sol.status != 'Cancelado'
            && sol.status != 'Reprovado pelo perito') {
                colunaOperacoes.innerHTML += `
                    <button type="button" class="btn-operacao" onClick="validarSeExisteSolicitacaoComIdInformado(${sol.id});" data-toggle="modal" data-target="#modal-cancelar-solicitacao"><i class="fa-solid fa-ban"></i> Cancelar</button>
                `;
            }

            if (sol.status === 'Concluído') {
                colunaStatus.classList.add('status-verde');
            } else if (sol.status === 'Cancelado'
            || sol.status === 'Reprovado pelo perito') {
                colunaStatus.classList.add('status-vermelho');
            } else {
                colunaStatus.classList.add('status-azul-escuro');
            }

            colunaStatus.classList.add('estilo-status-solicitacao');
            colunaOperacoes.classList.add('coluna-operacoes');                    
            linhaSolicitacao.append(colunaProtocolo);
            // linhaSolicitacao.append(colunaTitulo);
            linhaSolicitacao.append(colunaPosicaoFila);
            // linhaSolicitacao.append(colunaPrioridade);
            linhaSolicitacao.append(colunaDataEnvio);
            linhaSolicitacao.append(colunaStatus);
            linhaSolicitacao.append(colunaOperacoes);
            corpoListagemSolicitacoes.append(linhaSolicitacao);
        }

    } else if (tipoListagem === 'l_gestor_secretaria_secretario') {

        for (sol of solicitacoes) {
            const linhaSolicitacao = document.createElement('tr');
            const colunaProtocolo = document.createElement('td');
            // const colunaTitulo = document.createElement('td');
            const colunaStatus = document.createElement('td');
            // const colunaPrioridade = document.createElement('td');
            const colunaPosicaoFila = document.createElement('td');
            const colunaDataEnvio = document.createElement('td');
            const colunaOperacoes = document.createElement('td');
            // colunaTitulo.innerText = retornarLimitadoQtdCaracteres(sol.titulo);
            colunaStatus.innerText = sol.status;
            colunaDataEnvio.innerText = sol.data_registro;

            if (sol.posicao_fila === -1) {
                colunaPosicaoFila.innerText = '-----';
            } else {
                colunaPosicaoFila.innerText = sol.posicao_fila;
            }

            // colunaPrioridade.innerText = sol.prioridade;
            colunaProtocolo.innerText = retornarLimitadoQtdCaracteres(sol.protocolo, 20);
            colunaOperacoes.innerHTML = `
                <a href="ancaminhar-solicitacao.html?id=${sol.id}" class="btn-operacao"><i class="fa-solid fa-share"></i> Encaminhar</a>
            `;
            colunaOperacoes.innerHTML += `
                <button type="button" class="btn-operacao" onClick="visualizarDadosSolicitacao(${sol.id});" data-toggle="modal" data-target="#modal-visualizar-solicitacao"><i class="fa-solid fa-eye"></i> Visualizar</button>
            `;
            colunaOperacoes.innerHTML += `
                <button type="button" class="btn-operacao" onClick="validarSeExisteSolicitacaoComIdInformado(${sol.id});" data-toggle="modal" data-target="#modal-cancelar-solicitacao"><i class="fa-solid fa-ban"></i> Cancelar</button>
            `;
            colunaStatus.classList.add('status-azul-escuro');
            colunaStatus.classList.add('estilo-status-solicitacao');
            colunaOperacoes.classList.add('coluna-operacoes');                    
            linhaSolicitacao.append(colunaProtocolo);
            // linhaSolicitacao.append(colunaTitulo);
            linhaSolicitacao.append(colunaPosicaoFila);
            // linhaSolicitacao.append(colunaPrioridade);
            linhaSolicitacao.append(colunaDataEnvio);
            linhaSolicitacao.append(colunaStatus);
            linhaSolicitacao.append(colunaOperacoes);
            corpoListagemSolicitacoes.append(linhaSolicitacao);
        }

    } else if (tipoListagem === 'l_perito') {

        for (sol of solicitacoes) {
            const linhaSolicitacao = document.createElement('tr');
            const colunaProtocolo = document.createElement('td');
            // const colunaTitulo = document.createElement('td');
            const colunaStatus = document.createElement('td');
            // const colunaPrioridade = document.createElement('td');
            const colunaPosicaoFila = document.createElement('td');
            const colunaDataEnvio = document.createElement('td');
            const colunaOperacoes = document.createElement('td');
            // colunaTitulo.innerText = retornarLimitadoQtdCaracteres(sol.titulo);
            colunaStatus.innerText = sol.status;
            colunaDataEnvio.innerText = sol.data_registro;
            
            if (sol.posicao_fila === -1) {
                colunaPosicaoFila.innerText = '-----';
            } else {
                colunaPosicaoFila.innerText = sol.posicao_fila;
            }

            // colunaPrioridade.innerText = sol.prioridade;
            colunaProtocolo.innerText = retornarLimitadoQtdCaracteres(sol.protocolo, 20);
            colunaOperacoes.innerHTML = `
                <a href="realizar-analise.html?id=${sol.id}" class="btn-operacao"><i class="fa-solid fa-share"></i> Realizar análise</a>
            `;
            colunaOperacoes.innerHTML += `
                <button type="button" class="btn-operacao" onClick="visualizarDadosSolicitacao(${sol.id});" data-toggle="modal" data-target="#modal-visualizar-solicitacao"><i class="fa-solid fa-eye"></i> Visualizar</button>
            `;
            colunaOperacoes.innerHTML += `
                <button type="button" class="btn-operacao" onClick="validarSeExisteSolicitacaoComIdInformado(${sol.id});" data-toggle="modal" data-target="#modal-cancelar-solicitacao"><i class="fa-solid fa-ban"></i> Cancelar</button>
            `;
            colunaStatus.classList.add('status-azul-escuro');
            colunaStatus.classList.add('estilo-status-solicitacao');
            colunaOperacoes.classList.add('coluna-operacoes');                    
            linhaSolicitacao.append(colunaProtocolo);
            // linhaSolicitacao.append(colunaTitulo);
            linhaSolicitacao.append(colunaPosicaoFila);
            // linhaSolicitacao.append(colunaPrioridade);
            linhaSolicitacao.append(colunaDataEnvio);
            linhaSolicitacao.append(colunaStatus);
            linhaSolicitacao.append(colunaOperacoes);
            corpoListagemSolicitacoes.append(linhaSolicitacao);
        }

    } else if (tipoListagem === 'l_gestor_instituicao') {
        
        for (sol of solicitacoes) {
            const linhaSolicitacao = document.createElement('tr');
            const colunaProtocolo = document.createElement('td');
            // const colunaTitulo = document.createElement('td');
            const colunaStatus = document.createElement('td');
            // const colunaPrioridade = document.createElement('td');
            const colunaPosicaoFila = document.createElement('td');
            const colunaDataEnvio = document.createElement('td');
            const colunaOperacoes = document.createElement('td');
            // colunaTitulo.innerText = retornarLimitadoQtdCaracteres(sol.titulo);
            colunaStatus.innerText = sol.status;
            colunaDataEnvio.innerText = sol.data_registro;
            
            if (sol.posicao_fila === -1) {
                colunaPosicaoFila.innerText = '-----';
            } else {
                colunaPosicaoFila.innerText = sol.posicao_fila;
            }

            // colunaPrioridade.innerText = sol.prioridade;
            colunaProtocolo.innerText = retornarLimitadoQtdCaracteres(sol.protocolo, 20);
            colunaOperacoes.innerHTML = `
                <a href="ancaminhar-solicitacao-equipe.html?id=${sol.id}" class="btn-operacao"><i class="fa-solid fa-share"></i> Encaminhar a equipe</a>
            `;
            colunaOperacoes.innerHTML += `
                <button type="button" class="btn-operacao" onClick="visualizarDadosSolicitacao(${sol.id});" data-toggle="modal" data-target="#modal-visualizar-solicitacao"><i class="fa-solid fa-eye"></i> Visualizar</button>
            `;
            colunaOperacoes.innerHTML += `
                <button type="button" class="btn-operacao" onClick="validarSeExisteSolicitacaoComIdInformado(${sol.id});" data-toggle="modal" data-target="#modal-cancelar-solicitacao"><i class="fa-solid fa-ban"></i> Cancelar</button>
            `;
            colunaStatus.classList.add('status-azul-escuro');
            colunaStatus.classList.add('estilo-status-solicitacao');
            colunaOperacoes.classList.add('coluna-operacoes');                    
            linhaSolicitacao.append(colunaProtocolo);
            // linhaSolicitacao.append(colunaTitulo);
            linhaSolicitacao.append(colunaPosicaoFila);
            // linhaSolicitacao.append(colunaPrioridade);
            linhaSolicitacao.append(colunaDataEnvio);
            linhaSolicitacao.append(colunaStatus);
            linhaSolicitacao.append(colunaOperacoes);
            corpoListagemSolicitacoes.append(linhaSolicitacao);
        }

    }

}

function buscarSolicitacoesCidadao(idCidadao) {
    $('.corpo-tabela-listagem-solicitacoes').empty();
    const endpoint = urls.url_ambiente + '/solicitacao-servico/buscar-solicitacoes-servico-cidadao?cidadao_id=' + idCidadao;
    const alertaErroBuscarSolicitacoes = $('.alerta-erro-buscar-solicitacoes');
    const corpoListagemSolicitacoes = document.querySelector('.corpo-tabela-listagem-solicitacoes');
    alertaErroBuscarSolicitacoes.hide();
    alertaErroBuscarSolicitacoes.removeClass('alert-danger');
    alertaErroBuscarSolicitacoes.text('');
    $.ajax({
        url: endpoint,
        type: 'GET',
        contentType: 'application/json; charset=UTF-8',
        beforeSend: () => {
            apresentarTelaLoad();
        },
        success: (resposta) => {
            esconderTelaLoad();
            const mensagem = resposta.mensagem;

            if (mensagem === 'Não existem solicitações de serviço cadastradas!') {
                corpoListagemSolicitacoes.text(mensagem);
            } else if (mensagem === 'Ocorreu um erro ao tentar-se consultar as solicitações de serviço!') {
                alertaErroBuscarSolicitacoes.show();
                alertaErroBuscarSolicitacoes.text(mensagem);
                alertaErroBuscarSolicitacoes.addClass('alert-danger');
            } else {
                const solicitacoes = resposta.conteudo;
                apresentarSolicitacoesNaTabela(solicitacoes, 'l_cidadao');
            }

        },
        error: (resposta) => {
            esconderTelaLoad();
            // console.log(resposta);
        }
    });
}

function buscarSolicitacoesGestorSecretariaSecretario() {
    $('.corpo-tabela-listagem-solicitacoes').empty();
    const endpoint = urls.url_ambiente + '/solicitacao-servico';
    const alertaErroBuscarSolicitacoes = $('.alerta-erro-buscar-solicitacoes');
    const corpoListagemSolicitacoes = $('.corpo-tabela-listagem-solicitacoes');
    alertaErroBuscarSolicitacoes.hide();
    alertaErroBuscarSolicitacoes.removeClass('alert-danger');
    alertaErroBuscarSolicitacoes.text('');
    $.ajax({
        url: endpoint,
        type: 'GET',
        contentType: 'application/json; charset=UTF-8',
        beforeSend: () => {
            apresentarTelaLoad();
        },
        success: (resposta) => {
            esconderTelaLoad();
            const mensagem = resposta.mensagem;

            if (mensagem === 'Não existem solicitações de serviço cadastradas no banco de dados!') {
                corpoListagemSolicitacoes.text(mensagem);
            } else if (mensagem === 'Ocorreu um erro ao tentar-se buscar todas as solicitações de serviço!') {
                alertaErroBuscarSolicitacoes.show();
                alertaErroBuscarSolicitacoes.addClass('alert-danger');
                alertaErroBuscarSolicitacoes.text(mensagem);
            } else {
                const solicitacoes = resposta.conteudo;
                apresentarSolicitacoesNaTabela(solicitacoes, 'l_gestor_secretaria_secretario');
            }

        },
        error: (resposta) => {
            esconderTelaLoad();
            // console.log(resposta);
        }
    });
}

function buscarSolicitacoesInstituicao(idInstituicao) {
    $('.corpo-tabela-listagem-solicitacoes').empty();
    const endpoint = urls.url_ambiente + '/solicitacao-servico/buscar-solicitacoes-instituicao-para-encaminhar-a-equipe?instituicao_id=' + idInstituicao;
    const alertaErroBuscarSolicitacoes = $('.alerta-erro-buscar-solicitacoes');
    const corpoListagemSolicitacoes = $('.corpo-tabela-listagem-solicitacoes');
    alertaErroBuscarSolicitacoes.hide();
    alertaErroBuscarSolicitacoes.removeClass('alert-danger');
    alertaErroBuscarSolicitacoes.text('');
    $.ajax({
        url: endpoint,
        type: 'GET',
        contentType: 'application/json; charset=UTF-8',
        beforeSend: () => {
            apresentarTelaLoad();
        },
        success: (resposta) => {
            esconderTelaLoad();
            const mensagem = resposta.mensagem;

            if (mensagem === 'Ocorreu um erro ao tentar-se buscar as solicitações de serviço para serem encaminhadas as equipes!') {
                alertaErroBuscarSolicitacoes.show();
                alertaErroBuscarSolicitacoes.addClass('alert-danger');
                alertaErroBuscarSolicitacoes.text(mensagem);
            } else if (mensagem === 'Não existem solicitações para essa instituição cadastradas no banco de dados!') {
                corpoListagemSolicitacoes.text(mensagem);
            } else {
                const solicitacoes = resposta.conteudo;
                apresentarSolicitacoesNaTabela(solicitacoes, 'l_gestor_instituicao');
            }

        },
        error: (resposta) => {
            esconderTelaLoad();
            // console.log(resposta);
        }
    });
}

function buscarSolicitacoesPerito(idPerito) {
    $('.corpo-tabela-listagem-solicitacoes').empty();
    const endpoint = urls.url_ambiente + '/solicitacao-servico/buscar-solicitacoes-perito?id_perito=' + idPerito;
    const alertaErroBuscarSolicitacoes = $('.alerta-erro-buscar-solicitacoes');
    const corpoListagemSolicitacoes = $('.corpo-tabela-listagem-solicitacoes');
    alertaErroBuscarSolicitacoes.hide();
    alertaErroBuscarSolicitacoes.removeClass('alert-danger');
    alertaErroBuscarSolicitacoes.text('');
    $.ajax({
        url: endpoint,
        type: 'GET',
        contentType: 'application/json; charset=UTF-8',
        beforeSend: () => {
            apresentarTelaLoad();
        },
        success: (resposta) => {
            esconderTelaLoad();
            const mensagem = resposta.mensagem;

            if (mensagem === 'Ocorreu um erro ao tentar-se buscar as solicitações do perito em questão!') {
                alertaErroBuscarSolicitacoes.show();
                alertaErroBuscarSolicitacoes.addClass('alert-danger');
                alertaErroBuscarSolicitacoes.text(mensagem);
            } else if (mensagem === 'Não existem solicitações cadastradas no banco de dados!') {
                corpoListagemSolicitacoes.text(mensagem);
            } else {
                const solicitacoes = resposta.conteudo;
                apresentarSolicitacoesNaTabela(solicitacoes, 'l_perito');
            }

        },
        error: (resposta) => {
            esconderTelaLoad();
            // console.log(resposta);
        }
    });
}

function buscarSolicitacoes() {
    const dadosUsuarioLogado = JSON.parse(sessionStorage.getItem('usuario_logado'));
    const nivelAcessoUsuarioLogado = dadosUsuarioLogado.tipo_perfil_usuario_logado;
    console.log(dadosUsuarioLogado);
    
    if (nivelAcessoUsuarioLogado === 'cidadao') {
        buscarSolicitacoesCidadao(dadosUsuarioLogado.id);
    } else if (nivelAcessoUsuarioLogado === 'gestor_secretaria'
    || nivelAcessoUsuarioLogado === 'secretario') {
        buscarSolicitacoesGestorSecretariaSecretario();
    } else if (nivelAcessoUsuarioLogado === 'gestor_instituicao') {
        buscarSolicitacoesInstituicao(dadosUsuarioLogado.instituicao_id);
    } else if (nivelAcessoUsuarioLogado === 'perito') {
        const idPerito = dadosUsuarioLogado.id;
        buscarSolicitacoesPerito(idPerito);
    } else {
        
    }

}

function visualizarDadosSolicitacao(idSolicitacao) {
    const alertaErroVisualizarSolicitacao = $('.alerta-visualizar-solicitacao');
    alertaErroVisualizarSolicitacao.hide();
    alertaErroVisualizarSolicitacao.removeClass('alert-danger');
    alertaErroVisualizarSolicitacao.text('');
    const endpoint = urls.url_ambiente + '/solicitacao-servico/buscar-pelo-id?id=' + idSolicitacao;
    $.ajax({
        url: endpoint,
        type: 'GET',
        contentType: 'application/json; charset=UTF-8',
        beforeSend: () => {
            apresentarTelaLoad();
        },
        success: (resposta) => {
            esconderTelaLoad();

            if (resposta.mensagem === 'Solicitação encontrada com sucesso!') {
                // apresentando os dados da solicitação na modal
                console.log(resposta.conteudo);
                $('.protocolo-visualizar-na-modal').text(resposta.conteudo.protocolo);
                $('.titulo-visualizar-na-modal').text(resposta.conteudo.titulo);
                $('.descricao-visualizar-na-modal').text(resposta.conteudo.descricao);
                $('.status-visualizar-na-modal').text(resposta.conteudo.status);
                $('.posicao-fila-visualizar-na-modal').text(resposta.conteudo.posicao_fila);
                $('.prioridade-visualizar-na-modal').text(resposta.conteudo.prioridade);
                $('.data-envio-visualizar-na-modal').text(resposta.conteudo.data_registro);
                $('.logradouro-visualizar-na-modal').text(resposta.conteudo.logradouro);
                $('.bairro-visualizar-na-modal').text(resposta.conteudo.bairro);
                $('.cidade-visualizar-na-modal').text(resposta.conteudo.cidade);
                $('.estado-visualizar-na-modal').text(resposta.conteudo.estado);
                $('.complemento-visualizar-na-modal').text(resposta.conteudo.complemento === '' ? '--- sem complemento ---' : resposta.conteudo.complemento);
                $('.numero-visualizar-na-modal').text(resposta.conteudo.numero);
                $('.cep-visualizar-na-modal').text(resposta.conteudo.cep);

                if (resposta.conteudo.url_foto != '') {
                    obterImagemSolicitacao(resposta.conteudo.url_foto, $('.foto-visualizar-na-modal'));
                }

            } else {
                alertaErroVisualizarSolicitacao.show();
                alertaErroVisualizarSolicitacao.text(resposta.mensagem);
                alertaErroVisualizarSolicitacao.addClass('alert-danger');
            }

        },
        error: (resposta) => {
            esconderTelaLoad();
            // console.log(resposta);
        }
    });
}

function obterImagemSolicitacao(nomeImagem, tagImg) {
    const alertaErroVisualizarSolicitacao = $('.alerta-visualizar-solicitacao');
    alertaErroVisualizarSolicitacao.hide();
    alertaErroVisualizarSolicitacao.text('');
    alertaErroVisualizarSolicitacao.removeClass('alert-danger');
    const endpoint = urls.url_ambiente + '/solicitacao-servico/obter-imagem-solicitacao?nome_imagem=' + nomeImagem;
    $.ajax({
        url: endpoint,
        type: 'GET',
        contentType: 'application/json; charset=UTF-8',
        beforeSend: () => {
            apresentarTelaLoad();
        },
        success: (resposta) => {
            esconderTelaLoad();
            
            if (resposta.mensagem === 'Leitura de imagem realizado com sucesso!') {
                const imagemBase64 = 'data:image/' + resposta.conteudo.ext + ';base64,' + resposta.conteudo.imagem_base_64;
                tagImg.attr('src', imagemBase64);
            } else {
                alertaErroVisualizarSolicitacao.show();
                alertaErroVisualizarSolicitacao.text(resposta.mensagem);
                alertaErroVisualizarSolicitacao.addClass('alert-danger');
            }

        },
        error: (resposta) => {
            esconderTelaLoad();
            // console.log(resposta);
        }
    });
}

function validarSeExisteSolicitacaoComIdInformado(id) {
    const alertaCancelarSolicitacao = $('.alerta-cancelar-solicitacao');
    alertaCancelarSolicitacao.hide();
    alertaCancelarSolicitacao.removeClass('alert-danger');
    alertaCancelarSolicitacao.removeClass('alert-success');
    alertaCancelarSolicitacao.text('');
    $('label').hide();
    $('#motivo').hide();
    $('.btn-confirmar-cancelamento').hide();
    const endpoint = urls.url_ambiente + '/solicitacao-servico/buscar-pelo-id?id=' + id;
    $.ajax({
        url: endpoint,
        type: 'GET',
        contentType: 'application/json; charset=UTF-8',
        beforeSend: function () {
            apresentarTelaLoad();
        },
        success: function (resposta) {
            esconderTelaLoad();
            const msg = resposta.mensagem;

            if (msg === 'Solicitação encontrada com sucesso!') {
                $('label').show();
                $('#motivo').show();
                $('.btn-confirmar-cancelamento').show();
                $('.btn-confirmar-cancelamento').click(() => {
                    cancelarSolicitacao(id);
                });
            } else {
                alertaCancelarSolicitacao.show();
                alertaCancelarSolicitacao.addClass('alert-danger');
                alertaCancelarSolicitacao.text(msg);
            }

        },
        error: function (resposta) {
            esconderTelaLoad();
            // console.log(resposta);
        }
    });
}

function cancelarSolicitacao(id) {
    const alertaCancelarSolicitacao = $('.alerta-cancelar-solicitacao');
    alertaCancelarSolicitacao.hide();
    alertaCancelarSolicitacao.removeClass('alert-danger');
    alertaCancelarSolicitacao.removeClass('alert-success');
    alertaCancelarSolicitacao.text('');
    $('#motivo').removeClass('is-valid');
    $('#motivo').removeClass('is-invalid');
    $('.feedback-motivo').removeClass('invalid-feedback');
    $('.feedback-motivo').text('');
    const motivo = {
        motivo: $('#motivo').val().trim(),
        id_solicitacao: id
    };
    const motivoJson = JSON.stringify(motivo);
    const endpoint = urls.url_ambiente + '/cancelamento-solicitacao-servico';
    $.ajax({
        url: endpoint,
        type: 'POST',
        contentType: 'application/json; charset=UTF-8',
        data: motivoJson,
        beforeSend: () => {
            apresentarTelaLoad();
        },
        success: (resposta) => {
            esconderTelaLoad();
            const mensagem = resposta.mensagem;

            if (mensagem === 'Solicitação cancelada com sucesso!') {
                alertaCancelarSolicitacao.addClass('alert-success');
                $('.btn-confirmar-cancelamento').hide();
                $('#motivo').val('');
                buscarSolicitacoes();
            } else {
                alertaCancelarSolicitacao.addClass('alert-danger');
                
                if (resposta.conteudo != null) {

                    if (resposta.conteudo.motivo) {
                        $('#motivo').addClass('is-invalid');
                        $('.feedback-motivo').addClass('invalid-feedback');
                        $('.feedback-motivo').text(resposta.conteudo.motivo);
                    } else {    
                        $('#motivo').addClass('is-valid');
                        $('.feedback-motivo').text('');
                    }
                    
                }

            }

            alertaCancelarSolicitacao.show();
            alertaCancelarSolicitacao.text(mensagem);
        },
        error: (resposta) => {
            esconderTelaLoad();
            // console.log(resposta);
        }
    });
}

$('#btn-fechar').click(function () {

    if ($('#motivo').val().trim() != '') {
        $('#motivo').val('');
    }

});

$('.close').click(function () {

    if ($('#motivo').val().trim() != '') {
        $('#motivo').val('');
    }

});

function definirSeBotaoRealizarSolicitacaoVaiAparecer() {
    const dadosUsuarioLogado = JSON.parse(sessionStorage.getItem('usuario_logado'));

    if (dadosUsuarioLogado.tipo_perfil_usuario_logado != 'cidadao'
    && dadosUsuarioLogado.tipo_perfil_usuario_logado != 'secretario') {
        $('#btn-realizar-solicitacao-servico').hide();
    }
    
}

$(document).ready(function () {
    definirSeBotaoRealizarSolicitacaoVaiAparecer();
    buscarSolicitacoes();
});