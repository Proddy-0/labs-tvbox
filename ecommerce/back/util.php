<?php

// Carrega as variaveis do .env, se ainda nao tiverem sido carregadas
function carregaEnv($paramCaminho)
{
    if (!file_exists($paramCaminho)) {
        return;
    }

    foreach (file($paramCaminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linha) {
        $linha = trim($linha);

        // pula comentarios
        if ($linha === "" || $linha[0] === "#") {
            continue;
        }

        [$nome, $valor] = explode("=", $linha, 2);
        $nome  = trim($nome);
        $valor = trim($valor);

        putenv("$nome=$valor");
        $_ENV[$nome] = $valor;
    }
}

// util.php esta em back/, e o .env esta na raiz do projeto (um nivel acima)
carregaEnv(dirname(__DIR__) . "/.env");

function conecta($paramString = "")
{
    // nao mandou nada, monta a string a partir das variaveis do .env
    if ($paramString == "") {
        $host     = $_ENV['DB_HOST'];
        $port     = $_ENV['DB_PORT'];
        $database = $_ENV['DB_DATABASE'];
        $user     = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASSWORD'];

        $string_conexao = "pgsql:host=$host;port=$port;dbname=$database;user=$user;password=$password";
    } else {
        // assume a string recebida
        $string_conexao = $paramString;
    }

    try { //tente
        $c = new PDO($string_conexao);
    } catch (PDOException $e) { // se der erro ...
        echo "Serviço indisponivel no momento,
                tente mais tarde !<br>" . $e->getMessage();
        exit;
    }
    return $c;
}

// Cursos ativos (tabela curso), usados nos selects do painel
function listaCursos($conn)
{
    return $conn->query("
        SELECT id_curso, nome, cor, classe
        FROM curso
        WHERE excluido = false
        ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
}

// Filtro "curso" das listagens do painel (?curso=ID): id válido ou null (= todos)
function cursoDoFiltro($conn)
{
    $id = $_GET['curso'] ?? '';
    return $id === '' ? null : cursoValido($conn, $id);
}

// <select> do filtro de curso; com $enviaAoMudar o form já envia ao trocar a opção
function seletorCurso($cursos, $atual, $enviaAoMudar = true)
{
    $html = '<label for="filtro-curso" class="sr-only">Curso</label>'
        . '<select id="filtro-curso" name="curso" class="form-control"' . ($enviaAoMudar ? ' onchange="this.form.submit()"' : '') . '>'
        . '<option value="">Todos os cursos</option>';
    foreach ($cursos as $c) {
        $sel = ((int) $c['id_curso'] === $atual) ? ' selected' : '';
        $html .= '<option value="' . (int) $c['id_curso'] . '"' . $sel . '>' . htmlspecialchars($c['nome']) . '</option>';
    }
    return $html . '</select>';
}

// Devolve o id se o curso existir e não estiver excluído; senão null
function cursoValido($conn, $idCurso)
{
    $select = $conn->prepare("SELECT id_curso FROM curso WHERE id_curso = :id AND excluido = false");
    $select->execute([':id' => (int) $idCurso]);
    $id = $select->fetchColumn();
    return $id === false ? null : (int) $id;
}

// Telefone obrigatório: precisa ter DDD + número (10 ou 11 dígitos)
function telefoneValido($telefone)
{
    $digitos = preg_replace('/\D/', '', $telefone ?? '');
    return strlen($digitos) == 10 || strlen($digitos) == 11;
}

// Estoque por produto (só não excluídos); passe $idProduto pra pegar um só.
//   entrou     = soma das entradas
//   entregue   = itens de encomendas ENTREGUES (só a entrega tira do estoque físico)
//   reservado  = itens de encomendas RESERVADAS (separadas pro cliente, ainda no estoque)
//   emEstoque  = entrou - entregue            -> o que existe de verdade
//   disponivel = emEstoque - reservado        -> o que ainda pode ser reservado/vendido
function resumoEstoque($conn, $idProduto = null)
{
    $select = $conn->prepare("
        SELECT
            produto.id_produto,
            produto.nome,
            produto.fk_curso,
            COALESCE((SELECT SUM(quantidade) FROM entrada
                      WHERE entrada.fk_produto = produto.id_produto), 0) AS entrou,
            COALESCE((SELECT SUM(compra_produto.quantidade)
                      FROM compra_produto
                      INNER JOIN compra ON compra.id_compra = compra_produto.fk_compra
                      WHERE compra_produto.fk_produto = produto.id_produto
                        AND compra.status = 'entregue'), 0) AS entregue,
            COALESCE((SELECT SUM(compra_produto.quantidade)
                      FROM compra_produto
                      INNER JOIN compra ON compra.id_compra = compra_produto.fk_compra
                      WHERE compra_produto.fk_produto = produto.id_produto
                        AND compra.status = 'reservado'), 0) AS reservado
        FROM produto
        WHERE produto.excluido = false"
        . ($idProduto === null ? "" : " AND produto.id_produto = :id") . "
        ORDER BY produto.nome");
    $select->execute($idProduto === null ? [] : [':id' => (int) $idProduto]);
    $linhas = $select->fetchAll(PDO::FETCH_ASSOC);
    foreach ($linhas as &$l) {
        $l['entrou'] = (int) $l['entrou'];
        $l['entregue'] = (int) $l['entregue'];
        $l['reservado'] = (int) $l['reservado'];
        $l['emEstoque'] = $l['entrou'] - $l['entregue'];
        $l['disponivel'] = $l['emEstoque'] - $l['reservado'];
    }
    unset($l);
    return $linhas;
}

// Uma mudança "cria negativo" se deixa o saldo abaixo de zero E pior do que estava
// (assim dá pra corrigir um saldo que já estava negativo sem ficar travado).
function pioraSaldo($antes, $depois)
{
    return ($depois['emEstoque'] < 0 && $depois['emEstoque'] < $antes['emEstoque'])
        || ($depois['disponivel'] < 0 && $depois['disponivel'] < $antes['disponivel']);
}

// Detalhes digitados um por linha no painel viram "item|item|item" no banco
function detalhesParaBanco($texto)
{
    $linhas = array_filter(array_map('trim', preg_split('/\r\n|\r|\n|\|/', $texto)));
    return $linhas ? mb_substr(implode('|', $linhas), 0, 500) : null;
}

function salvaUpload($id, $paramCaminho, $paramFiles, $paramCampo)
{
    if (isset($paramFiles[$paramCampo])) {
        // obtem a extensão do arquivo
        $ext = strtolower(pathinfo(
            $paramFiles[$paramCampo]['name'],
            PATHINFO_EXTENSION
        ));
        // só aceita imagem: sem isso daria pra subir um .php e executar no servidor
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            echo "Formato de imagem não permitido.";
            return;
        }
        if (!is_dir($paramCaminho)) {
            mkdir($paramCaminho, 0755, true);
        }
        $arquivoImagem = "$paramCaminho/$id.$ext";
        // apaga imagem antiga do mesmo id com outra extensão (senão a antiga continua sendo achada)
        foreach (glob("$paramCaminho/$id.*") ?: [] as $antiga) {
            if ($antiga != $arquivoImagem) {
                unlink($antiga);
            }
        }
        if (move_uploaded_file($paramFiles[$paramCampo]['tmp_name'], $arquivoImagem)) {
            return $arquivoImagem;
        }
    }
    return null;
}

// produto.imagem guarda o caminho a partir da RAIZ do site (mesma convenção do SGE, que grava
// "<pasta>/produto<id>.<ext>" e mostra em eq.projetoscti.com.br/<equipe>/<imagem>).
// $prefixo = caminho da página atual até a raiz (front/ -> "../", back/<pasta>/ -> "../../").
function imagemDoProduto($linha, $prefixo)
{
    $imagem = trim($linha['imagem'] ?? '');
    if ($imagem == '') {
        return '';
    }
    if (preg_match('#^https?://#i', $imagem)) {
        return $imagem;
    }
    return $prefixo . ltrim($imagem, '/');
}
