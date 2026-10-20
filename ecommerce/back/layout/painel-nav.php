<?php
    // Submenu do painel admin. Defina $ativo antes do include: estoque | usuarios | produtos | cursos | entradas | mensagens | perfil
    $ativo = $ativo ?? '';
    $itensPainel = [
        'estoque' => ['../painel/estoque.php', 'fa-boxes-stacked', 'Estoque e encomendas'],
        'usuarios' => ['../usuarios/usuarios.php', 'fa-users', 'Usuários'],
        'produtos' => ['../produtos/listaprodutos.php', 'fa-layer-group', 'Produtos'],
        'cursos' => ['../cursos/cursos.php', 'fa-graduation-cap', 'Cursos'],
        'entradas' => ['../entrada/entradas.php', 'fa-truck-ramp-box', 'Entradas'],
        'mensagens' => ['../contato/mensagens.php', 'fa-envelope', 'Mensagens'],
        // admin também compra: perfil e minhas compras ficam na mesma tela do cliente
        'perfil' => ['../../front/perfil.html', 'fa-user', 'Meu perfil e compras'],
    ];
?>
        <nav class="panel-nav" aria-label="Painel administrativo">
<?php foreach ($itensPainel as $chave => $item) { ?>
            <a href="<?= $item[0] ?>"<?= $chave == $ativo ? ' class="active" aria-current="page"' : '' ?>><i class="fa-solid <?= $item[1] ?>"></i><?= $item[2] ?></a>
<?php } ?>
        </nav>
