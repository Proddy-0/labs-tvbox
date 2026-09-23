<?php
    // Submenu do painel admin. Defina $ativo antes do include: estoque | usuarios | produtos | entradas
    $ativo = $ativo ?? '';
    $itensPainel = [
        'estoque' => ['../painel/estoque.php', 'fa-boxes-stacked', 'Estoque e encomendas'],
        'usuarios' => ['../usuarios/usuarios.php', 'fa-users', 'Usuários'],
        'produtos' => ['../produtos/listaprodutos.php', 'fa-layer-group', 'Produtos'],
        'entradas' => ['../entrada/entradas.php', 'fa-truck-ramp-box', 'Entradas'],
    ];
?>
        <nav class="panel-nav" aria-label="Painel administrativo">
<?php foreach ($itensPainel as $chave => $item) { ?>
            <a href="<?= $item[0] ?>"<?= $chave == $ativo ? ' class="active" aria-current="page"' : '' ?>><i class="fa-solid <?= $item[1] ?>"></i><?= $item[2] ?></a>
<?php } ?>
        </nav>
