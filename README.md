# Proddyt Labs ~/tvbox

Hub do **Proddyt Labs**, servido 24/7 por uma TV box Android reaproveitada (RK322x, Armbian) com nginx + PHP-FPM. A mesma caixa é a borda de internet do lab: um Cloudflare Tunnel publica os serviços self-hosted que rodam no servidor principal (acessado via Tailscale).

- URL: <https://tvbox.proddyt.com>
- Marca: Proddyt, frente **Labs** (fonte: [Proddyt-Labs/brand](https://github.com/Proddyt-Labs/brand) `@1`)

> English summary: static/PHP hub of my homelab on a repurposed Android TV box (Armbian, nginx, Cloudflare Tunnel). Live at tvbox.proddyt.com.

## Estrutura

| Caminho | O que é |
|---|---|
| `index.php` | Página do hub. Lê `projetos.json` e renderiza o catálogo no servidor (tudo escapado com `htmlspecialchars`). Filtro por categoria, relógio e clima no cabeçalho. |
| `style.css` | Só o que a marca não cobre; usa apenas tokens (`--bg`, `--surface`, `--brand-text`...). |
| `projetos.json` | "Banco de dados" do catálogo. |
| `painel/` | Dashboard pessoal (Kanban/Pomodoro). Hoje `index.html` e `script.js` estão vazios; só o `style.css` tem conteúdo. Não faz parte da UI do hub. |
| `ecommerce/` | Projeto escolar. **Sincronizado automaticamente de outro repo; não editar aqui.** |
| `loja-jottave7/` | Demo freelance. **Sincronizado automaticamente de outro repo; não editar aqui.** |
| `.github/workflows/deploy.yml` | Deploy automático (ver abaixo). |

## Adicionar um projeto

Acrescente um objeto ao fim de `projetos.json`; ele aparece sozinho no catálogo.

```json
{
  "id": "101",
  "categoria": "BACKEND",
  "titulo": "Nome do Projeto",
  "descricao": "O que o projeto faz, em uma frase.",
  "status": "Dev",
  "tags": ["PHP", "API"],
  "url": "/novo-projeto/index.php"
}
```

- `id`: string; aparece no cartão.
- `categoria`: vira um botão de filtro (valores iguais agrupam).
- `status`: `Finalizado` e `Online` usam o chip verde, `Dev` o chip de marca, qualquer outro valor o chip neutro.
- `tags`: lista de strings.
- `url`: caminho relativo ao site (`/...`) ou `http(s)://`. Outros esquemas viram `#`.

## Preview local

Requer PHP (`php -v`). Se não houver, instale o PHP CLI ou teste direto no servidor.

```
php -S 127.0.0.1:8773
```

Abra <http://127.0.0.1:8773>. Fontes, tokens da marca e tema vêm do CDN (jsDelivr) e do Google Fonts, então é preciso internet.

## Deploy

Push em `main` dispara `.github/workflows/deploy.yml` no runner self-hosted da própria TV box: `git fetch origin main`, `git reset --hard origin/main` e `git clean -fd` em `/var/www/html`. Alterações feitas direto no servidor são descartadas.

O Cloudflare tem uma Cache Rule (Bypass) para o subdomínio `tvbox`, então CSS/PHP novos aparecem sem esperar cache.

## Marca

CSS e tema vêm de `https://cdn.jsdelivr.net/gh/Proddyt-Labs/brand@1/` (`tokens.css`, `base.css`, `theme.js`, `marks/labs-mark.svg`). O `<html>` usa `data-brand="labs"`. Para mudar cores ou componentes, altere o repo da marca, não este.

## Infra

- Hardware: TV box genérica RK322x, Armbian (armv7l).
- Web: nginx + PHP-FPM em `/var/www/html`.
- Borda: Cloudflare Tunnel (sem portas abertas no roteador).
- Rede local: `http://192.168.1.121`; SSH com o usuário `proddyt`.

## Repositórios do ecossistema

[labs-tvbox](https://github.com/Proddy-0/labs-tvbox) (este), [labs-craft](https://github.com/Proddy-0/labs-craft), [labs-tools](https://github.com/Proddy-0/labs-tools). Os demais `labs-*` são privados (Gitea do lab). Tarefas: [GitHub Project Proddyt Labs](https://github.com/users/Proddy-0/projects/3/views/1).
