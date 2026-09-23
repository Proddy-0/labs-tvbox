<?php // gerado a partir do header do site — mantenha igual ao front ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo ?? "Painel") ?> | Deck Logic</title>
    <meta name="description" content="Área do cliente e painel administrativo da Deck Logic.">
    <meta name="theme-color" content="#14537D">
    <meta name="color-scheme" content="light dark">
    <script>
        // aplica o tema escolhido no botão antes de desenhar a página (evita piscar)
        try { var tema = localStorage.getItem('decklogic_tema'); if (tema) document.documentElement.dataset.theme = tema; } catch (e) {}
    </script>
    <link rel="icon" type="image/png" href="../../front/img/brand/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../front/css/style.css">
</head>

<body id="top" data-front="../../front/">
    <!-- Logo Deck Logic (SVG inline, reutilizado no header e no footer via <use>) -->
    <svg class="svg-sprite" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <linearGradient id="grad-cursos" gradientUnits="userSpaceOnUse" x1="0" y1="0" x2="600" y2="0">
                <stop offset="0" stop-color="#EB9F1C" />
                <stop offset="0.5" stop-color="#1D51C1" />
                <stop offset="1" stop-color="#BA1313" />
            </linearGradient>
            <symbol id="logo-mark" viewBox="0 0 600 521">
                <polygon points="155,12 445,12 591,260 445,509 155,509 9,260" fill="none" stroke="currentColor" stroke-width="18" stroke-linejoin="round" />
                <polygon points="163,27 437,27 574,260 437,494 163,494 26,260" fill="none" stroke="url(#grad-cursos)" stroke-width="6" stroke-dasharray="3 3" />
                <line x1="108" y1="262" x2="510" y2="262" stroke="url(#grad-cursos)" stroke-width="14" stroke-dasharray="2 2" />
                <circle cx="108" cy="262" r="38" fill="#EB9F1C" />
                <circle cx="310" cy="262" r="38" fill="#1D51C1" />
                <circle cx="510" cy="262" r="38" fill="#BA1313" />
            </symbol>
            <symbol id="logo-full" viewBox="0 0 1212 521">
                <use href="#logo-mark" width="600" height="521" />
                <text x="640" y="195" fill="currentColor" font-family="'JetBrains Mono', ui-monospace, monospace" font-size="185" font-weight="500">Deck</text>
                <text x="640" y="420" fill="currentColor" font-family="'JetBrains Mono', ui-monospace, monospace" font-size="185" font-weight="500">Logic</text>
            </symbol>
        </defs>
    </svg>

    <a class="skip-link" href="#conteudo">Pular para o conteúdo</a>

    <header class="site-header">
        <div class="brand-bar" aria-hidden="true"></div>
        <div class="container header-inner">
            <a href="../../front/index.html" class="logo" aria-label="Deck Logic — página inicial">
                <svg class="logo-svg" aria-hidden="true"><use href="#logo-full" /></svg>
            </a>

            <form class="search-form" action="produtos.html" method="get" role="search">
                <label for="busca" class="sr-only">Buscar cartas</label>
                <input type="search" id="busca" name="q" placeholder="Buscar cartas de Informática, Mecânica ou Eletrônica">
                <button type="submit" aria-label="Buscar"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>

            <nav class="user-nav" aria-label="Conta e carrinho">
                <button type="button" class="user-link theme-toggle" id="theme-toggle" aria-label="Ativar tema escuro"><i class="fa-solid fa-moon"></i></button>
                <a href="../../front/login.html" class="user-link" data-auth="conta" aria-label="Entrar"><i class="fa-regular fa-user"></i><span>Entrar</span></a>
                <a href="../../front/cadastro.html" class="user-link user-link--signup" data-auth="sair"><span>Cadastre-se</span></a>
                <a href="../../front/carrinho.html" class="cart-btn" aria-label="Carrinho">
                    <i class="fa-solid fa-cart-shopping"></i><span class="cart-label">Carrinho</span>
                    <span class="cart-badge" id="cart-count">0</span>
                </a>
            </nav>

            <button class="nav-toggle" type="button" aria-controls="menu-principal" aria-expanded="false" aria-label="Abrir menu">
                <span class="nav-toggle-bar"></span>
                <span class="nav-toggle-bar"></span>
                <span class="nav-toggle-bar"></span>
            </button>
        </div>

        <div class="nav-backdrop" id="nav-backdrop"></div>

        <nav class="main-nav" id="menu-principal" aria-label="Menu principal">
            <div class="main-nav-head">
                <span class="main-nav-title">Menu</span>
                <button class="nav-close" type="button" aria-label="Fechar menu"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="container">
                <ul>
                    <li><a href="../../front/index.html"><i class="fa-solid fa-house"></i>Home</a></li>
                    <li><a href="../../front/produtos.html"><i class="fa-solid fa-layer-group"></i>Produtos</a></li>
                    <li><a href="../../front/sobre.html"><i class="fa-solid fa-circle-info"></i>Sobre Nós</a></li>
                    <li><a href="../../front/contato.html"><i class="fa-solid fa-envelope"></i>Contato</a></li>
                    <li><a href="../../front/desenvolvedores.html"><i class="fa-solid fa-code"></i>Devs</a></li>
                    <li class="main-nav-extra main-nav-extra--first"><a href="../../front/login.html" data-auth="conta"><i class="fa-regular fa-user"></i>Entrar</a></li>
                    <li class="main-nav-extra"><a href="../../front/cadastro.html" data-auth="sair"><i class="fa-solid fa-user-plus"></i>Cadastre-se</a></li>
                </ul>
            </div>
        </nav>
    </header>
