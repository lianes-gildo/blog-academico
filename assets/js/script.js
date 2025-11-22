// =====================================
// BLOG ACADÊMICO - JS FRONTEND
// Responsável por:
// - Dropdown do usuário
// - Preview de imagem em formulários (posts/perfil)
// - Curtir (AJAX)
// - Comentar (AJAX)
// - Compartilhar (navigator.share / link copiado)
// =====================================

document.addEventListener('DOMContentLoaded', function () {

    // -------------------------------------
    // 1) Dropdown do usuário no header
    // -------------------------------------
    const botaoUsuario = document.querySelector('.usuario-botao');
    const menuUsuario = document.querySelector('.menu-usuario');

    if (botaoUsuario && menuUsuario) {
        botaoUsuario.addEventListener('click', function (e) {
            e.stopPropagation();
            menuUsuario.classList.toggle('aberto');
        });

        document.addEventListener('click', function () {
            menuUsuario.classList.remove('aberto');
        });

        menuUsuario.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    // -------------------------------------
    // 2) Preview de imagem em formulários
    //    (adicionar_post, editar_post, perfil)
    // -------------------------------------
    const inputImagem = document.getElementById('input-imagem-preview');
    const previewImagem = document.getElementById('preview-imagem');

    if (inputImagem && previewImagem) {
        inputImagem.addEventListener('change', function () {
            const arquivo = this.files[0];
            if (!arquivo) return;
            const leitor = new FileReader();
            leitor.onload = function (ev) {
                previewImagem.src = ev.target.result;
            };
            leitor.readAsDataURL(arquivo);
        });
    }

    // -------------------------------------
    // 3) Botão like (curtir / descurtir via AJAX)
    //    Elementos esperados:
    //    - Botão com classe .botao-like e data-id="<id do post>"
    //    - Span/elemento com id="contador-gostos"
    // -------------------------------------
    const botaoLike = document.querySelector('.botao-like');
    if (botaoLike) {
        botaoLike.addEventListener('click', function () {
            const idPost = this.getAttribute('data-id');
            if (!idPost) return;

            // Enviar requisição AJAX para /backend/curtir.php
            fetch('/backend/curtir.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json;charset=utf-8' },
                body: JSON.stringify({ id: idPost })
            })
                .then(res => res.json())
                .then(dados => {
                    if (!dados.sucesso) {
                        alert(dados.mensagem || 'Erro ao curtir.');
                        return;
                    }
                    // Atualizar contador de gostos
                    const spanGostos = document.getElementById('contador-gostos');
                    if (spanGostos) {
                        spanGostos.textContent = dados.gostos;
                    }
                    // Atualizar estado visual do botão
                    if (dados.atedeu_like) {
                        botaoLike.classList.add('ativo');
                    } else {
                        botaoLike.classList.remove('ativo');
                    }
                })
                .catch(() => {
                    alert('Erro de comunicação com o servidor ao curtir.');
                });
        });
    }

    // -------------------------------------
    // 4) Comentários via AJAX na página artigo
    //    Elementos esperados:
    //    - textarea com id="texto-comentario"
    //    - botão com id="botao-enviar-comentario" e data-id="<postId>"
    //    - div .lista-comentarios onde os novos comentários serão inseridos
    //    - span/div .comentario-status para feedback "enviado"
    // -------------------------------------
    const botaoComentario = document.getElementById('botao-enviar-comentario');
    if (botaoComentario) {
        const textareaComentario = document.getElementById('texto-comentario');
        const listaComentarios = document.querySelector('.lista-comentarios');
        const statusComentario = document.querySelector('.comentario-status');

        botaoComentario.addEventListener('click', function () {
            const idPost = this.getAttribute('data-id');
            const texto = textareaComentario ? textareaComentario.value.trim() : '';

            if (!texto) {
                if (statusComentario) {
                    statusComentario.textContent = 'Escreva um comentário antes de enviar.';
                    statusComentario.style.color = 'var(--cor-danger)';
                } else {
                    alert('Escreva um comentário antes de enviar.');
                }
                return;
            }

            fetch('/backend/comentar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json;charset=utf-8' },
                body: JSON.stringify({ id: idPost, comentario: texto })
            })
                .then(res => res.json())
                .then(dados => {
                    if (!dados.sucesso) {
                        if (statusComentario) {
                            statusComentario.textContent = dados.mensagem || 'Erro ao enviar comentário.';
                            statusComentario.style.color = 'var(--cor-danger)';
                        } else {
                            alert(dados.mensagem || 'Erro ao enviar comentário.');
                        }
                        return;
                    }

                    // Limpa o campo de texto
                    if (textareaComentario) textareaComentario.value = '';

                    // Mostra mensagem de enviado
                    if (statusComentario) {
                        statusComentario.textContent = 'Comentário enviado!';
                        statusComentario.style.color = 'var(--cor-sucesso)';
                    }

                    // Insere visualmente o novo comentário no topo ou final da lista
                    if (listaComentarios) {
                        const item = document.createElement('div');
                        item.className = 'comentario-item';

                        const cabecalho = document.createElement('div');
                        cabecalho.className = 'comentario-cabecalho';

                        const nome = document.createElement('span');
                        nome.className = 'comentario-nome';
                        nome.textContent = dados.nome || 'Usuário';

                        const data = document.createElement('span');
                        data.className = 'comentario-data';
                        data.textContent = dados.data || '';

                        cabecalho.appendChild(nome);
                        cabecalho.appendChild(data);

                        const textoEl = document.createElement('div');
                        textoEl.className = 'comentario-texto';
                        textoEl.textContent = dados.comentario || texto;

                        item.appendChild(cabecalho);
                        item.appendChild(textoEl);

                        // Adiciona no início da lista
                        listaComentarios.prepend(item);
                    }
                })
                .catch(() => {
                    if (statusComentario) {
                        statusComentario.textContent = 'Erro de comunicação com o servidor.';
                        statusComentario.style.color = 'var(--cor-danger)';
                    } else {
                        alert('Erro de comunicação com o servidor.');
                    }
                });
        });
    }

    // -------------------------------------
    // 5) Compartilhar artigo
    //    Elementos esperados:
    //    - botão com classe .botao-compartilhar e data-titulo / data-url
    // -------------------------------------
    const botaoCompartilhar = document.querySelector('.botao-compartilhar');
    if (botaoCompartilhar) {
        botaoCompartilhar.addEventListener('click', function () {
            const titulo = this.getAttribute('data-titulo') || document.title;
            const url = this.getAttribute('data-url') || window.location.href;

            // Se API de compartilhamento nativa estiver disponível (mobile)
            if (navigator.share) {
                navigator.share({
                    title: titulo,
                    text: 'Veja este artigo no Blog-Acadêmico:',
                    url: url
                }).catch(() => {
                    // usuário pode cancelar, isso é normal
                });
            } else {
                // Fallback: copiar link para área de transferência
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url)
                        .then(() => alert('Link copiado para a área de transferência!'))
                        .catch(() => alert('Não foi possível copiar o link. Copie diretamente da barra do navegador.'));
                } else {
                    alert('Compartilhamento não suportado diretamente. Copie o link da barra do navegador.');
                }
            }
        });
    }

});
