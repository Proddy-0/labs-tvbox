<?php
$pastaMailer = file_exists(__DIR__ . '/PHPMailer/src/PHPMailer.php')
    ? __DIR__ . '/PHPMailer/src'
    : dirname(__DIR__) . '/PHPMailer/src';
require_once "$pastaMailer/Exception.php";
require_once "$pastaMailer/PHPMailer.php";
require_once "$pastaMailer/SMTP.php";
use PHPMailer\PHPMailer\PHPMailer;
    // Carrega o .env localizado na raiz do projeto
    carregaEnv(dirname(__DIR__) . "/.env");
    $caminhoEnv = dirname(__DIR__) . "/.env";
function carregaEnv($paramCaminho)
{
    if (!file_exists($paramCaminho)) {
        return;
    }

    foreach (file($paramCaminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linha) {
        $linha = trim($linha);

        if ($linha === "" || $linha[0] === "#") {
            continue;
        }

        [$nome, $valor] = explode("=", $linha, 2);
        $nome = trim($nome);
        $valor = trim($valor);

        putenv("$nome=$valor");
        $_ENV[$nome] = $valor;
    }
}



// Conexão com o banco de dados
function conecta($paramString = "")
{
    if ($paramString == "") {
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $database = $_ENV['DB_DATABASE'];
        $user = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASSWORD'];

        $string_conexao = "pgsql:host=$host;port=$port;dbname=$database;user=$user;password=$password";
    } else {
        $string_conexao = $paramString;
    }

    try {
        $c = new PDO($string_conexao);
    } catch (PDOException $e) {
        echo "Serviço indisponível no momento, tente mais tarde!<br>" . $e->getMessage();
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

// Upload de imagens
function salvaUpload($id, $paramCaminho, $paramFiles, $paramCampo)
{
    if (isset($paramFiles[$paramCampo])) {
        $ext = strtolower(pathinfo(
            $paramFiles[$paramCampo]['name'],
            PATHINFO_EXTENSION
        ));

        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            echo "Formato de imagem não permitido.";
            return;
        }

        if (!is_dir($paramCaminho)) {
            mkdir($paramCaminho, 0755, true);
        }

        $arquivoImagem = "$paramCaminho/$id.$ext";
        try {
            if (move_uploaded_file(
                $paramFiles[$paramCampo]['tmp_name'],
                $arquivoImagem
            )) {
                echo "<br>Arquivo $arquivoImagem criado com sucesso.\n";
            }
        } catch (PDOException $e) { // se der erro ...
            echo "Erro, verifique o arquivo se a pasta imagens existe";
        }
    }
}

function EnviaEmail($pEmailDestino, $pAssunto, $pHtml)
{
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->Host       = $_ENV['SMTP_HOST'];
    $mail->Port       = (int) $_ENV['SMTP_PORT'];
    $mail->SMTPAuth   = true;
    $mail->SMTPSecure = 'tls';
    $mail->Username   = $_ENV['SMTP_USER'];
    $mail->Password   = $_ENV['SMTP_PASSWORD'];
    $mail->CharSet    = 'UTF-8';

    // Só use isto se o servidor tiver certificado autoassinado
    $mail->SMTPOptions = ['ssl' => [
        'verify_peer'       => false,
        'verify_peer_name'  => false,
        'allow_self_signed' => true,
    ]];

    $mail->setFrom($_ENV['SMTP_USER'], 'Suporte de senhas');
    $mail->addAddress($pEmailDestino, 'Usuario');
    $mail->isHTML(true);
    $mail->Subject = $pAssunto;
    $mail->Body    = $pHtml;

    $enviado = $mail->send();
    if (!$enviado) {
        error_log('Erro ao enviar e-mail: ' . $mail->ErrorInfo);
    }
    return $enviado;
}
// Token do link "esqueci a senha": HMAC do e-mail + hash da senha atual + expiração.
// Trocar a senha invalida o link, e ninguém sem o APP_SEGREDO consegue forjar um.
function geraTokenRecuperacao($email, $hashSenha, $expira)
{
    $dados = strtolower($email) . '|' . $hashSenha . '|' . (int) $expira;
    return hash_hmac('sha256', $dados, $_ENV['APP_SEGREDO']);
}

// Usado pelo redefinir.php para validar o link
function tokenRecuperacaoValido($email, $hashSenha, $expira, $token)
{
    if ((int) $expira < time()) {
        return false;
    }
    return hash_equals(geraTokenRecuperacao($email, $hashSenha, $expira), (string) $token);
}
// Quantidade em estoque de um produto, usando a função qtd_estoque do banco.
// Em caso de erro devolve null e preenche $paramErro.
function qtd_prod($paramConn, $paramId, &$paramErro)
{
    try {
        $select = $paramConn->prepare("SELECT qtd_estoque(:p)");
        $select->bindValue(':p', (int) $paramId, PDO::PARAM_INT);
        $select->execute();
        return (int) $select->fetchColumn();
    } catch (PDOException $e) {
        $paramErro = $e->getMessage();
        return null;
    }
}