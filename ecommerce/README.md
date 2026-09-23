# E-Commerce — Deck Logic

### Link para a page main
https://ra.projetoscti.com.br/2457100/ecomerce/index.html

## Estrutura

```
front/                  site estático (HTML + CSS + JS)
├── *.html              uma página por arquivo (header/footer iguais em todas)
├── css/style.css       estilo único, mobile first (breakpoints 480 / 768 / 1024px)
├── css/wireframe.css   estilo só do wireframe.html (protótipo)
├── js/script.js        menu hambúrguer, carrossel, carrinho (localStorage), filtros/busca, formulários
├── img/brand/          favicon e logo em PNG (a logo do site é SVG inline no HTML)
├── img/cards/          cartas à venda (informatica, mecanica, eletronica, colecao)
└── docs/               enunciado do trabalho
back/                   PHP (CRUD de entradas, produtos, usuários)
db/                     diagramas do banco
```

## Cores

| Uso | Cor |
|---|---|
| Informática | `#1D51C1` |
| Eletrônica | `#EB9F1C` |
| Mecânica | `#BA1313` |
| Marca (logo, textos) | `#14537D` |

Para aplicar a cor de um curso em qualquer componente, adicione a classe `is-info`, `is-eletro` ou `is-mec`.

## Adicionar um produto

Copie um `<article class="product-card ...">` em `index.html`/`produtos.html` e ajuste a classe do curso, `data-category`, a imagem e os `data-*` do botão **Adicionar** (nome, curso, preço, imagem) — o carrinho lê esses atributos. O `data-id` precisa ser o `id_produto` da tabela `produto` (hoje: 5 Informática, 6 Mecânica, 7 Eletrônica), senão a encomenda ignora o item.

## Rodar localmente (com login, cadastro e encomendas)

Login, cadastro, encomendas e painel admin são PHP. Abrindo o site pelo **Live Server** (ou qualquer servidor só de HTML) o envio dos formulários dá `HTTP ERROR 405` — o site mostra um aviso nesse caso.

1. Instale o PHP 8 (`winget install PHP.PHP.8.3`) e no `php.ini` descomente `extension=pdo_pgsql`.
2. Crie o `.env` na raiz (modelo em `.env.example`) com os dados do banco.
3. Na raiz do projeto: `php -S localhost:8000`
4. Abra `http://localhost:8000/front/index.html`

Usuários de teste: `admin@decklogic.com` / `admin123` (painel) e `local@decklogic.com` / `local123` (cliente).

| Área | Endereço |
|---|---|
| Minhas encomendas (cliente) | `back/compras/encomendas.php` |
| Estoque e encomendas (admin) | `back/painel/estoque.php` |
| Usuários (admin) | `back/usuarios/usuarios.php` |