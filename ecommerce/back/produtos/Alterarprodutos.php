<html>

<body>

    <?php
    include "../util.php";
    $conn = conecta();
    $id = $_GET['id'];
    $varSQL = "
        SELECT *
        FROM produto
        WHERE id_produto = :id";
    $select = $conn->prepare($varSQL);
    $select->bindParam(
        ':id',$id);
    $select->execute();
    $linha = $select->fetch(PDO::FETCH_ASSOC);
    if (!$linha) {
        echo "Produto não encontrado!";
        exit;
    }
    $nome = $linha['nome'];
    $descricao = $linha['descricao'];
    $valor = $linha['valor_unitario'];
    ?>
    <form
        action="Updateprodutos.php"
        method="post"
        enctype="multipart/form-data"
    >
        <input
            type="hidden"
            name="id_produto"
            value="<?= $id ?>"
        >
        Nome<br>
        <input
            type="text"
            name="nome"
            value="<?= htmlspecialchars($nome) ?>"
            required
        >
        <br><br>
        Descrição<br>
        <input
            type="text"
            name="descricao"
            value="<?= htmlspecialchars($descricao) ?>"
            required
        >
        <br><br>
        Valor unitário<br>
        <input
            type="number"
            name="valor_unitario"
            value="<?= $valor ?>"
            step="0.01"
            min="0"
            required
        >
        <br><br>
        <?php
        $imagem = "";
        $extensoes = ['jpg','jpeg','png','gif'];
        foreach ($extensoes as $ext) {
            $arquivo = "imagens/produtos/$id.$ext";
            if (file_exists($arquivo)) {
                $imagem = $arquivo;
                break;
            }
        }
        if ($imagem != "") {
            echo "
                <img src='$imagem'height='80'>
                <br><br>";
        }
        ?>
        Nova imagem<br>
        <input
            type="file"
            name="arquivo"
            accept="image/*"
        >
        <br><br>
        <input
            type="submit"
            value="Salvar"
        >
    </form>
</body>
</html>