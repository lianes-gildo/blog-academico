document.addEventListener('DOMContentLoaded', function () {

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

    const botaoLike = document.querySelector('.botao-like');
    if (botaoLike) {
        botaoLike.addEventListener('click', function () {
            const idPost = this.getAttribute('data-id');
            if (!idPost) return;


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

                    const spanGostos = document.getElementById('contador-gostos');
                    if (spanGostos) {
                        spanGostos.textContent = dados.gostos;
                    }

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

                    if (textareaComentario) textareaComentario.value = '';

                    if (statusComentario) {
                        statusComentario.textContent = 'Comentário enviado!';
                        statusComentario.style.color = 'var(--cor-sucesso)';
                    }

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


    const botaoCompartilhar = document.querySelector('.botao-compartilhar');
    if (botaoCompartilhar) {
        botaoCompartilhar.addEventListener('click', function () {
            const titulo = this.getAttribute('data-titulo') || document.title;
            const url = this.getAttribute('data-url') || window.location.href;

            if (navigator.share) {
                navigator.share({
                    title: titulo,
                    text: 'Veja este artigo no Blog-Acadêmico:',
                    url: url
                }).catch(() => {
                });
            } else {
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
