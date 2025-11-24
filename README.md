# 📚 Blog Acadêmico Moçambique

Sistema moderno de blog acadêmico com painel administrativo completo, gestão de usuários e controle de papéis.

## ✨ Funcionalidades

### 🎯 Para Todos os Usuários
- ✅ Visualização de artigos acadêmicos
- ✅ Compartilhamento de posts
- ✅ Design responsivo (mobile, tablet, desktop)
- ✅ Interface moderna com Bootstrap 5

### 👤 Para Usuários Registrados
- ✅ Sistema de login e registro
- ✅ Curtir artigos
- ✅ Comentar em posts
- ✅ Editar perfil e foto
- ✅ Gerenciar conta

### ✏️ Para Editores
- ✅ Criar novos posts
- ✅ Editar seus próprios posts
- ✅ Apagar seus posts
- ✅ Painel estatístico personalizado
- ✅ Visualizar métricas de engajamento

### 👑 Para Administradores
- ✅ Controle total sobre posts
- ✅ Gestão de usuários
- ✅ Atribuir papéis (Usuário, Editor, Admin)
- ✅ Suspender usuários (1 dia a 1 ano)
- ✅ Dashboard com estatísticas completas
- ✅ Visualizar total de posts, usuários, visualizações e likes

## 🎨 Design e Cores

- **Cor Primária:** Laranja (#FF6B35)
- **Cor Secundária:** Azul Escuro (#003B5C)
- **Framework:** Bootstrap 5.3.2
- **Ícones:** Bootstrap Icons
- **Fontes:** Poppins (Google Fonts)

## 📱 Responsividade

O sistema é totalmente responsivo com:
- Menu hamburguer no mobile
- Dropdown de usuário no menu hamburguer (mobile)
- Cards adaptáveis
- Tabelas responsivas
- Formulários otimizados para touch

## 🔐 Sistema de Papéis

### Usuário (Padrão)
- Visualizar posts
- Curtir e comentar
- Editar perfil

### Editor
- Tudo do usuário +
- Criar posts
- Editar/apagar apenas seus posts
- Painel de editor

### Administrador
- Controle total do sistema
- Gerenciar todos os posts
- Gerenciar usuários
- Atribuir papéis
- Suspender contas

## 📁 Estrutura de Arquivos

```
blog-academico/
│
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── script.js
│   └── img/
│       ├── posts/
│       └── users/
│
├── backend/
│   ├── adicionar_post.php
│   ├── apagar_post.php
│   ├── comentar.php
│   ├── curtir.php
│   ├── editar_post.php
│   ├── gerir_usuarios.php
│   ├── login.php
│   ├── logout.php
│   ├── painelAdmin.php
│   ├── painelEditor.php
│   ├── perfil.php
│   └── registrar.php
│
├── data/
│   ├── comentarios.json
│   ├── estatisticas.json
│   ├── posts.json
│   └── usuarios.json
│
├── includes/
│   ├── header.php
│   └── footer.php
│
├── acesso_negado.php
├── artigo.php
├── index.php
└── sobre.php
```

## 🚀 Instalação

### Requisitos
- PHP 7.4 ou superior
- Servidor web (Apache/Nginx)
- Permissões de escrita nas pastas `data/` e `assets/img/`

### Passo a Passo

1. **Clone ou baixe o projeto**
```bash
git clone [seu-repositorio]
cd blog-academico
```

2. **Configure as permissões**
```bash
chmod 777 data/
chmod 777 assets/img/posts/
chmod 777 assets/img/users/
```

3. **Crie os arquivos JSON iniciais** (se não existirem)

`data/usuarios.json`:
```json
[
    {
        "id": 1,
        "nome": "Admin",
        "email": "admin@blog.com",
        "senha": "$2y$10$...", 
        "papel": "admin",
        "imagem": "assets/img/users/default.jpg"
    }
]
```

Senha padrão: `admin123`

`data/posts.json`:
```json
[]
```

`data/comentarios.json`:
```json
[]
```

`data/estatisticas.json`:
```json
{
    "visitas": {},
    "gostos": {}
}
```

4. **Acesse o sistema**
- URL: `http://localhost/blog-academico`
- Login admin: `admin@blog.com` / `admin123`

## 🔧 Configuração

### Criar Senha Hash para Admin
```php
<?php
echo password_hash('sua_senha', PASSWORD_DEFAULT);
?>
```

### Timezone
O sistema usa `Africa/Maputo`. Para alterar, edite `includes/header.php`:
```php
date_default_timezone_set('Africa/Maputo');
```

## 🛡️ Segurança

- ✅ Senhas hasheadas com `password_hash()`
- ✅ Validação de sessões
- ✅ Proteção de rotas
- ✅ Sanitização de inputs com `htmlspecialchars()`
- ✅ Verificação de permissões por papel
- ✅ Verificação de autor para editores

## 📊 Funcionalidades de Gestão

### Suspender Usuários
Períodos disponíveis:
- 1 dia
- 1 semana
- 1 mês
- 3 meses
- 6 meses
- 9 meses
- 1 ano

### Estatísticas
- Total de posts publicados
- Total de usuários registrados
- Total de visualizações
- Total de likes

## 🎨 Personalização

### Cores
Edite as variáveis CSS em `includes/header.php`:
```css
:root {
    --primary-orange: #FF6B35;
    --primary-blue: #003B5C;
    --secondary-orange: #FF8C42;
    --secondary-blue: #005B8C;
}
```

### Logo
Altere em `includes/header.php`:
```php
<a class="navbar-brand" href="...">
    📚 Seu Nome do Blog
</a>
```

## 📞 Suporte

Para dúvidas ou problemas:
- Email: seu-email@exemplo.com
- Issues: [GitHub Issues]

## 👨‍💻 Desenvolvido por

**Lianes Gildo Nhacula**
- 🇲🇿 Moçambique
- 📧 Email: [seu-email]
- 🌐 Website: [seu-site]

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo LICENSE para mais detalhes.

---

⭐ **Dica:** Faça backup regular dos arquivos JSON em `data/` para não perder informações!

🚀 **Bom uso e sucesso com seu blog acadêmico!**