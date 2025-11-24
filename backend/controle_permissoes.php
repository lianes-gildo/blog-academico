<?php
/**
 * Sistema de Controle de Permissões
 * Define o que cada papel pode fazer no sistema
 */

// Inicializar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica se usuário tem permissão para uma ação específica
 * @param string $acao - Ação a ser verificada
 * @return bool
 */
function temPermissao($acao) {
    if (!isset($_SESSION['papel'])) {
        return false;
    }
    
    $papel = $_SESSION['papel'];
    
    // Definir permissões por papel
    $permissoes = [
        'usuario' => [
            'ler_posts',
            'comentar',
            'curtir',
            'editar_proprio_perfil',
            'apagar_propria_conta'
        ],
        'moderador' => [
            'ler_posts',
            'comentar',
            'curtir',
            'editar_proprio_perfil',
            'apagar_propria_conta',
            'moderar_comentarios', // Pode aprovar/rejeitar comentários
            'ver_relatorios_basicos' // Pode ver estatísticas básicas
        ],
        'editor' => [
            'ler_posts',
            'comentar',
            'curtir',
            'editar_proprio_perfil',
            'apagar_propria_conta',
            'criar_posts', // Pode criar novos posts
            'editar_proprios_posts', // Pode editar seus próprios posts APENAS
            'ver_painel_posts' // Pode ver painel dos seus posts
            // NÃO pode apagar posts (nem os próprios)
            // NÃO pode gerenciar usuários
        ],
        'admin' => [
            'ler_posts',
            'comentar',
            'curtir',
            'editar_proprio_perfil',
            'apagar_propria_conta',
            'moderar_comentarios',
            'ver_relatorios_basicos',
            'criar_posts',
            'editar_proprios_posts',
            'apagar_proprios_posts',
            'editar_qualquer_post', // Pode editar posts de outros
            'apagar_qualquer_post', // Pode apagar posts de outros
            'gerenciar_usuarios', // Pode gerenciar todos os usuários
            'mudar_papeis', // Pode alterar papéis de usuários
            'suspender_usuarios', // Pode suspender contas
            'ver_dashboard_admin', // Acesso ao painel administrativo completo
            'ver_relatorios_completos' // Acesso a todas as estatísticas
        ]
    ];
    
    // Admin tem todas as permissões
    if ($papel === 'admin') {
        return true;
    }
    
    // Verificar se o papel tem a permissão específica
    if (isset($permissoes[$papel]) && in_array($acao, $permissoes[$papel])) {
        return true;
    }
    
    return false;
}

/**
 * Redireciona usuário sem permissão
 * @param string $mensagem - Mensagem de erro (opcional)
 */
function negarAcesso($mensagem = "Você não tem permissão para acessar esta página.") {
    $_SESSION['erro_permissao'] = $mensagem;
    header('Location: ../index.php');
    exit;
}

/**
 * Verifica se usuário pode editar um post específico
 * @param int $postId - ID do post
 * @param string $autorPost - Nome do autor do post
 * @return bool
 */
function podeEditarPost($postId, $autorPost) {
    if (!isset($_SESSION['papel'])) {
        return false;
    }
    
    $papel = $_SESSION['papel'];
    $nomeUsuario = $_SESSION['nome'] ?? '';
    
    // Admin pode editar qualquer post
    if ($papel === 'admin') {
        return true;
    }
    
    // Editor pode editar APENAS seus próprios posts
    if ($papel === 'editor' && $autorPost === $nomeUsuario) {
        return true;
    }
    
    return false;
}

/**
 * Verifica se usuário pode apagar um post específico
 * @param int $postId - ID do post
 * @param string $autorPost - Nome do autor do post
 * @return bool
 */
function podeApagarPost($postId, $autorPost) {
    if (!isset($_SESSION['papel'])) {
        return false;
    }
    
    $papel = $_SESSION['papel'];
    
    // APENAS Admin pode apagar posts
    if ($papel === 'admin') {
        return true;
    }
    
    // Editor NÃO pode apagar posts (nem os próprios)
    return false;
}

/**
 * Verifica se pode criar novos posts
 * @return bool
 */
function podeAdicionarPost() {
    if (!isset($_SESSION['papel'])) {
        return false;
    }
    
    $papel = $_SESSION['papel'];
    
    // APENAS Admin pode adicionar posts
    // Editor NÃO pode adicionar
    return $papel === 'admin';
}

/**
 * Retorna nome amigável do papel
 * @param string $papel
 * @return string
 */
function getNomePapel($papel) {
    $nomes = [
        'usuario' => 'Usuário',
        'moderador' => 'Moderador',
        'editor' => 'Editor',
        'admin' => 'Administrador'
    ];
    
    return $nomes[$papel] ?? 'Desconhecido';
}

/**
 * Retorna descrição do papel
 * @param string $papel
 * @return string
 */
function getDescricaoPapel($papel) {
    $descricoes = [
        'usuario' => 'Pode ler, comentar e curtir posts',
        'moderador' => 'Pode moderar comentários e ver relatórios básicos',
        'editor' => 'Pode criar, editar e apagar seus próprios posts',
        'admin' => 'Acesso total ao sistema'
    ];
    
    return $descricoes[$papel] ?? '';
}

/**
 * Retorna ícone do papel
 * @param string $papel
 * @return string
 */
function getIconePapel($papel) {
    $icones = [
        'usuario' => '👤',
        'moderador' => '🛡️',
        'editor' => '✍️',
        'admin' => '👑'
    ];
    
    return $icones[$papel] ?? '❓';
}
?>