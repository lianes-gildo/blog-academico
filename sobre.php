<?php
// ================================
// PÁGINA SOBRE
// ================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$paginaAtual = 'sobre';

require __DIR__ . '/includes/header.php';
?>

<!-- ===== CSS IN-PAGE ===== -->
<style>
    body {
        background-color: white;
        color: black;
        font-family: Arial, sans-serif;
    }

    h1, h2 {
        color: #002f6c; /* azul escuro */
    }

    p {
        color: black;
    }

    .conteudo {
        padding: 20px;
    }

    .cartao-estatico {
        background: #ffffff;
        padding: 20px;
        border-radius: 5px;
    }
</style>
<!-- ======================= -->

<main class="conteudo conteudo-limitado">
    <section class="secao-pagina-estatica">
        <header class="cabecalho-secao">
            <h1>Sobre o Blog-Acadêmico</h1>
            <p class="texto-suave mt-1">
                Um espaço pensado para apoiar estudantes, professores e pesquisadores
                na organização da vida acadêmica.
            </p>
        </header>

        <div class="cartao-estatico">
            <h2>Nosso objetivo</h2>
            <p>
                O <strong>Blog-Acadêmico</strong> foi criado com a missão de facilitar o acesso
                a conteúdos acadêmicos de qualidade, com foco em estudantes de Moçambique
                e de outros países de língua portuguesa.
            </p>

            <p class="mt-1">
                Aqui você encontra dicas de estudo, orientações sobre pesquisa científica,
                sugestões de ferramentas digitais, orientações para elaboração de trabalhos,
                apresentações e muito mais.
            </p>

            <h2 class="mt-3">Como o blog funciona</h2>
            <ul class="lista-simples mt-1">
                <li><strong>Postagens acadêmicas:</strong> Publicadas por administradores, com foco em temas relevantes para a vida universitária.</li>
                <li><strong>Interação:</strong> Usuários podem reagir com gostos, deixar comentários e compartilhar artigos.</li>
                <li><strong>Acesso livre:</strong> Quem não estiver logado pode ler e compartilhar todos os posts, mas não pode comentar nem curtir.</li>
            </ul>

            <h2 class="mt-3">Participação dos usuários</h2>
            <p>
                Ao criar sua conta, você pode:
            </p>
            <ul class="lista-simples mt-1">
                <li>Comentar em artigos;</li>
                <li>Curtir postagens que achar mais úteis;</li>
                <li>Atualizar seu perfil com nome e foto;</li>
                <li>Acompanhar o crescimento da comunidade acadêmica.</li>
            </ul>

            <h2 class="mt-3">Foco em Moçambique</h2>
            <p>
                O blog considera a realidade de estudantes em Moçambique, inclusive
                horários no fuso <strong>África/Maputo</strong>, exemplos práticos e referências
                adaptadas ao contexto local sempre que possível.
            </p>
        </div>
    </section>
</main>

<?php
require __DIR__ . '/includes/footer.php';
?>
