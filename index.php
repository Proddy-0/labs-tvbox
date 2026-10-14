<?php
function h($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

// Aceita apenas URLs relativas ao site ou http(s); qualquer outro esquema vira "#".
function safe_url($v): string
{
    $u = trim((string) $v);
    if ($u !== '' && ($u[0] === '/' || preg_match('#^https?://#i', $u))) {
        return $u;
    }
    return '#';
}

function status_class(string $s): string
{
    $s = mb_strtolower($s, 'UTF-8');
    if ($s === 'finalizado' || $s === 'online') {
        return 'p-chip p-chip-ok';
    }
    if ($s === 'dev') {
        return 'p-chip p-chip-dev';
    }
    return 'p-chip';
}

$json_path = __DIR__ . '/projetos.json';
$json_data = @file_get_contents($json_path);
$projetos = [];
$json_error = null;

if ($json_data === false) {
    $json_error = 'Erro ao carregar o catálogo: não foi possível ler projetos.json.';
} else {
    $decoded = json_decode($json_data, true);

    if (!is_array($decoded)) {
        $json_error = 'Erro ao carregar o catálogo: projetos.json inválido (' . json_last_error_msg() . ').';
    } else {
        $projetos = $decoded;
    }
}

$categorias = [];
foreach ($projetos as $p) {
    $c = (string) ($p['categoria'] ?? '');
    if ($c !== '' && !in_array($c, $categorias, true)) {
        $categorias[] = $c;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-brand="labs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Proddyt Labs ~/tvbox</title>
    <meta name="description" content="Hub do Proddyt Labs rodando 24/7 numa TV box Android reaproveitada: Armbian, nginx e Cloudflare Tunnel.">
    <link rel="icon" href="https://cdn.jsdelivr.net/gh/Proddyt-Labs/brand@1/marks/labs-mark.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/Proddyt-Labs/brand@1/tokens.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/Proddyt-Labs/brand@1/base.css">
    <script src="https://cdn.jsdelivr.net/gh/Proddyt-Labs/brand@1/theme.js"></script>
    <link rel="stylesheet" href="/style.css">
</head>

<body>
    <header class="p-header">
        <div class="p-container">
            <a class="p-logo" href="/"><img class="p-mark" src="https://cdn.jsdelivr.net/gh/Proddyt-Labs/brand@1/marks/labs-mark.svg" alt=""> proddyt <span class="p-logo-suffix">~/tvbox</span></a>
            <nav class="p-nav" aria-label="Seções">
                <a href="#catalogo">Catálogo</a>
                <a href="https://github.com/Proddy-0/labs-tvbox">GitHub</a>
            </nav>
            <div class="hub-widget" role="group" aria-label="Hora e clima local">
                <span id="clock">--:--</span>
                <span class="p-muted" aria-hidden="true">|</span>
                <span id="weather">--°C</span>
            </div>
            <button class="p-icon-btn" type="button" data-theme-toggle></button>
        </div>
    </header>

    <main>
        <section class="p-container p-hero">
            <span class="p-eyebrow">Proddyt Labs · hub</span>
            <h1>tvbox</h1>
            <p>O hub do Proddyt Labs, rodando 24/7 numa TV box Android reaproveitada: Armbian, nginx e Cloudflare Tunnel.</p>
            <div class="p-actions">
                <a class="p-btn" href="#catalogo">Ver catálogo</a>
                <a class="p-btn p-btn-ghost" href="https://github.com/Proddy-0/labs-tvbox">Repositório</a>
            </div>
        </section>

        <section class="p-section" id="catalogo">
            <div class="p-container hub-stack">
                <div class="hub-head">
                    <span class="p-eyebrow">Pages &amp; projetos</span>
                    <h2>Catálogo</h2>
                    <p class="p-muted">Hub pessoal de projetos, ideias e estudos, com tarefas organizadas no GitHub Project Proddyt Labs.</p>
                </div>

                <?php if ($json_error !== null): ?>
                    <p class="p-card" role="alert"><?= h($json_error) ?></p>
                <?php else: ?>
                    <?php if (count($categorias) > 1): ?>
                        <div class="hub-filters" id="filters" role="group" aria-label="Filtrar por categoria" hidden>
                            <button type="button" class="p-chip" data-cat="" aria-pressed="true">Todos</button>
                            <?php foreach ($categorias as $c): ?>
                                <button type="button" class="p-chip" data-cat="<?= h($c) ?>" aria-pressed="false"><?= h($c) ?></button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="p-grid" id="grid">
                        <?php foreach ($projetos as $proj): ?>
                            <?php $status = (string) ($proj['status'] ?? ''); ?>
                            <a href="<?= h(safe_url($proj['url'] ?? '')) ?>" class="p-card" data-cat="<?= h($proj['categoria'] ?? '') ?>">
                                <div class="hub-card-top">
                                    <span class="p-eyebrow"><span class="hub-id"><?= h($proj['id'] ?? '') ?></span> <?= h($proj['categoria'] ?? '') ?></span>
                                    <?php if ($status !== ''): ?>
                                        <span class="<?= h(status_class($status)) ?>"><?= h($status) ?></span>
                                    <?php endif; ?>
                                </div>
                                <h3><?= h($proj['titulo'] ?? '') ?></h3>
                                <p class="p-muted"><?= h($proj['descricao'] ?? '') ?></p>
                                <?php if (!empty($proj['tags']) && is_array($proj['tags'])): ?>
                                    <ul class="hub-tags">
                                        <?php foreach ($proj['tags'] as $tag): ?>
                                            <li class="p-chip"><?= h($tag) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <p class="p-muted" id="empty" hidden>Nenhum projeto nesta categoria.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer class="p-footer">
        <div class="p-container">
            <span>Proddyt Labs</span>
            <nav class="hub-links" aria-label="Rodapé">
                <a href="https://home.proddyt.com">home</a>
                <a href="https://craft.proddyt.com">craft</a>
                <a href="https://tools.proddyt.com">tools</a>
                <a href="https://switch.proddyt.com">Proddyt Studio</a>
                <a href="https://github.com/Proddy-0">GitHub</a>
            </nav>
        </div>
    </footer>

    <script>
        // Relógio
        function updateClock() {
            document.getElementById('clock').textContent = new Date().toLocaleTimeString('pt-BR', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Temperatura atual (atualiza a cada 30 minutos)
        function fetchWeather() {
            fetch('https://api.open-meteo.com/v1/forecast?latitude=-22.3145&longitude=-49.0587&current_weather=true')
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    document.getElementById('weather').textContent = Math.round(data.current_weather.temperature) + '°C';
                })
                .catch(function (err) { console.error('Erro ao puxar o clima', err); });
        }
        fetchWeather();
        setInterval(fetchWeather, 1800000);

        // Filtro por categoria (sem JS, mostra tudo)
        (function () {
            var bar = document.getElementById('filters');
            if (!bar) return;
            var cards = document.querySelectorAll('#grid .p-card');
            var empty = document.getElementById('empty');
            bar.hidden = false;
            bar.addEventListener('click', function (e) {
                var btn = e.target.closest('button[data-cat]');
                if (!btn) return;
                var cat = btn.getAttribute('data-cat');
                bar.querySelectorAll('button').forEach(function (b) {
                    b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
                });
                var shown = 0;
                cards.forEach(function (c) {
                    var ok = cat === '' || c.getAttribute('data-cat') === cat;
                    c.hidden = !ok;
                    if (ok) shown++;
                });
                empty.hidden = shown !== 0;
            });
        })();
    </script>
</body>

</html>
