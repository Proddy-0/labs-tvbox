<?php
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
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proddyt Labs</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/style.css">
</head>

<body>
    <header class="top-header">
        <!-- Lado Esquerdo: Logo e Títulos -->
        <div class="header-left">
            <div class="logo-box">&lt;.&gt;</div>
            <div class="header-titles">
                <h1>Proddyt Labs</h1>
                <span>Forjando código, testando limites e quebrando a web em ambiente controlado.</span>
            </div>
        </div>

        <!-- Lado Direito: Widget de Hora e Temp -->
        <div class="header-widget">
            <span id="clock">00:00</span>
            <span class="separator">|</span>
            <span id="weather">--°C</span>
        </div>
    </header>

    <main class="container">
        <section class="section-header">
            <span class="section-tag">— PAGES & PROJETOS</span>
            <h2 class="section-title">Catálogo</h2>
            <p class="section-desc">Hub pessoal de projetos, ideias e estudos, com tarefas organizadas no GitHub Project Proddyt Labs.</p>
        </section>

        <section class="grid-catalogo">
            <?php if ($json_error !== null): ?>
                <p class="card-desc"><?= htmlspecialchars($json_error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php else: ?>
                <?php foreach ($projetos as $proj): ?>
                    <a href="<?= $proj['url'] ?>" class="craft-card">
                        <div class="card-top">
                            <div class="card-category">
                                <span class="cat-id"><?= $proj['id'] ?></span>
                                <span class="cat-name"><?= $proj['categoria'] ?></span>
                            </div>
                            <span class="card-status"><?= $proj['status'] ?></span>
                        </div>

                        <h3 class="card-title"><?= $proj['titulo'] ?></h3>
                        <p class="card-desc"><?= $proj['descricao'] ?></p>

                        <div class="card-tags">
                            <?php foreach ($proj['tags'] as $tag): ?>
                                <span class="tag"><?= $tag ?></span>
                            <?php endforeach; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <script>
        // Relógio
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('pt-BR', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Puxa a temperatura atual (Atualiza a cada 30 minutos)
        function fetchWeather() {
            fetch('https://api.open-meteo.com/v1/forecast?latitude=-22.3145&longitude=-49.0587&current_weather=true')
                .then(res => res.json())
                .then(data => {
                    document.getElementById('weather').innerText = Math.round(data.current_weather.temperature) + '°C';
                })
                .catch(err => console.error("Erro ao puxar o clima", err));
        }
        fetchWeather();
        setInterval(fetchWeather, 180000);
    </script>
</body>

</html>
