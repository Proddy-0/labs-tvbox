<html>
<body>
    <?php
    include "../util.php";
    $conn = conecta();
    $id = $_GET['id'];
    $varSQL = "
        SELECT *
        FROM entrada
        WHERE id_entrada = :id
    ";
    $select = $conn->prepare($varSQL);
    $select->bindParam(':id',$id);
    $select->execute();
    $linha = $select->fetch(PDO::FETCH_ASSOC);
    if (!$linha) {
        echo "Entrada não encontrada!";
        exit;
    }
    $fk_produto = $linha['fk_produto'];
    $quantidade = $linha['quantidade'];
    $custo = $linha['custo_unitario'];
    $obs = $linha['obs'];
    ?>
    <h2>Alterar entrada</h2>
    <form
        action="updateEntradas.php"
        method="post"
    >
        <input
            type="hidden"
            name="id_entrada"
            value="<?= $id ?>"
        >
        Produto<br>
        <select
            name="fk_produto"
            required
        >
            <?php
            $varSQL = "
                SELECT
                    id_produto,
                    descricao
                FROM produto
                WHERE excluido = false
                ORDER BY descricao";
            $selectProdutos = $conn->query($varSQL);
            while ($produto =$selectProdutos->fetch(PDO::FETCH_ASSOC)) 
            {
                $idProduto =$produto['id_produto'];
                $descricao =htmlspecialchars($produto['descricao']);
                $selecionado =($idProduto == $fk_produto)? "selected": "";
                echo "
                    <option value='$idProduto'$selecionado>
                        $descricao
                    </option>
                ";
            }
            ?>
        </select>
        <br><br>
        Quantidade<br>
        <input
            type="number"
            name="quantidade"
            value="<?= $quantidade ?>"
            min="1"
            required
        >
        <br><br>
        Custo unitário<br>
        <input
            type="number"
            name="custo_unitario"
            value="<?= $custo ?>"
            step="0.01"
            min="0"
            required
        >
        <br><br>
        Observação<br>
        <textarea
            name="obs"
            maxlength="255"
        ><?= htmlspecialchars($obs ?? '') ?></textarea>
        <br><br>
        <input
            type="submit"
            value="Salvar"
            >
    </form>
</body>
</html>