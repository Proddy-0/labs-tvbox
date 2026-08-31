# 🚀 Proddyt Labs Hub (labs-tvbox)

Este repositório/servidor é o hub principal do ecossistema **Proddyt Labs** e também o ambiente pessoal de projetos estáticos e dinâmicos, rodando 24/7 de forma econômica.

## 🧭 Sobre o projeto
O `labs-tvbox` funciona como ponto central para navegação dos repositórios `labs-*`, catálogo de experimentos e operação contínua do ambiente web.

## 📈 Status
- **Hub (`labs-tvbox`):** Ativo
- **Ecossistema `labs-*`:** Em organização e padronização de documentação

## 🔗 Ecossistema Labs (repositórios)
- [labs-tvbox](https://github.com/Proddy-0/labs-tvbox) (hub atual)
- [labs-book](https://github.com/Proddy-0/labs-book)
- [labs-craft](https://github.com/Proddy-0/labs-craft)
- [labs-tools](https://github.com/Proddy-0/labs-tools)
- [labs-nexo](https://github.com/Proddy-0/labs-nexo)
- [labs-wire](https://github.com/Proddy-0/labs-wire)
- [labs-punch](https://github.com/Proddy-0/labs-punch)
- [labs-vector](https://github.com/Proddy-0/labs-vector)

## ✅ Gestão de tarefas (GitHub Project: Proddyt Labs)
Use o Project **Proddyt Labs** como quadro operacional único do ecossistema:

- **Concluído (Done):**
  - Estrutura inicial do hub em `labs-tvbox`
  - Catálogo dinâmico via `projetos.json`
  - Publicação contínua com Nginx + PHP-FPM + Cloudflare Tunnel
- **Em aberto (To do / Missing):**
  - Padronizar README dos repositórios `labs-*`
  - Revisar status de cada repositório e registrar tarefas faltantes
  - Manter backlog priorizado com próximas entregas do ecossistema

> Dica de operação do Project: manter colunas **Backlog**, **Doing** e **Done**, e usar labels por repositório (ex.: `repo:labs-book`, `repo:labs-tools`) para visão rápida do que já foi entregue vs. pendente.

## 📡 Acessos e Links
* **Domínio Público:** [https://tvbox.proddyt.com](https://tvbox.proddyt.com)
* **IP Local (Rede Interna):** `http://192.168.1.121`
* **Usuário SSH:** `proddyt`

## ⚙️ Hardware e Infraestrutura
* **Dispositivo:** TV Box Genérica (Chip RK322x)
* **Sistema Operacional:** Armbian Buster (32-bit, armv7l)
* **Servidor Web:** Nginx + PHP-FPM (Processando a engine da página inicial)
* **Túnel Web:** Cloudflare Tunnel (Zero Trust) - Expõe o servidor para a web de forma segura com HTTPS sem necessidade de abrir portas no roteador local.

## 🛠️ Workflow de Desenvolvimento
O código é editado ao vivo usando a seguinte arquitetura:

1. **Editor:** Visual Studio Code (no Windows).
2. **Sincronização:** Extensão `ftp-simple`. A cada `Ctrl+S`, a extensão envia o arquivo instantaneamente via SFTP para a TV Box.
3. **Diretório Raiz (Web):** `/var/www/html`
4. **Cache Control:** O Cloudflare possui uma *Cache Rule* configurada (Bypass) para o subdomínio `tvbox`. Isso garante que as atualizações de CSS/PHP subam em tempo real, eliminando dores de cabeça com cache antigo.

## 📂 Estrutura de Arquivos Principal
* `index.php` -> Motor principal que renderiza a página do Proddyt Craft.
* `projetos.json` -> O "banco de dados" do painel. Basta adicionar um novo objeto JSON aqui para que um novo projeto apareça automaticamente na home page.
* `style.css` -> Identidade visual dark/neon (Cyber-craft).

## ➕ Como Adicionar Novos Projetos (O Fluxo)
O processo para subir um novo laboratório ou página no Hub é automático e não requer edição na página principal.

1. **Crie a Página:** No seu VS Code, crie a subpasta e os arquivos do novo projeto (ex: `/var/www/html/novo-projeto/index.php`). Dê `Ctrl+S` para enviar à TV Box.
2. **Registre no JSON:** Abra o arquivo `projetos.json` que está na raiz.
3. **Adicione o Bloco:** Insira um novo objeto no final da lista seguindo o padrão:
   ```json
   {
       "id": "3",
       "categoria": "BACKEND",
       "titulo": "Nome do Projeto",
       "descricao": "O que esse projeto faz de forma resumida.",
       "status": "Dev",
       "tags": ["PHP", "API"],
       "url": "/novo-projeto/index.php"
   }
   ```
4. **Salve o arquivo:** após salvar, o novo projeto passa a aparecer automaticamente na página inicial.
