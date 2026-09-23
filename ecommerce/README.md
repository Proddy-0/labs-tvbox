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

Copie um `<article class="product-card ...">` em `index.html`/`produtos.html` e ajuste a classe do curso, `data-category`, a imagem e os `data-*` do botão **Adicionar** (id, nome, curso, preço, imagem) — o carrinho lê esses atributos.
