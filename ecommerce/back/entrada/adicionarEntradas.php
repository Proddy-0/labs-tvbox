<html>
<body>
    <?php
        include "../util.php";
        $conn = conecta();
        ?>
        <h2>Adicionar entrada</h2>
        <form action="insertEntradas.php"method="post">
            Produto<br>
            <select name="fk_produto"required>
                <option value="">
                    Selecione um produto
                </option>
                <?php
                $varSQL = "
                    SELECT
                    id_produto,
                    descricao
                    FROM produto
                    WHERE excluido = false
                    ORDER BY descricao";
                $select = $conn->query($varSQL);
                while ($linha =$select->fetch(PDO::FETCH_ASSOC)) 
                {
                    $idProduto =
                        $linha['id_produto'];
                    $descricao =
                        htmlspecialchars(
                            $linha['descricao']
                        );
                    echo "
                        <option value='$idProduto'>
                            $descricao
                        </option>";
                }
                ?>
        </select>
        <br><br>
        Quantidade<br>
        <input type="number"name="quantidade"min="1"required>
        <br><br>
        Custo unitário<br>
        <input
            type="number"
            name="custo_unitario"
            step="0.01"
            min="0"
            required
        >
        <br><br>
        Observação<br>
        <textarea name="obs"maxlength="255"
        ></textarea>
        <br><br>
        <input
            type="submit"
            value="Salvar"
        >
    </form>
</body>
</html>