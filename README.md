# 🚀 Proddyt Hub - TV Box Server

Este repositório/servidor é o meu laboratório pessoal e hub de projetos estáticos e dinâmicos, rodando 24/7 de forma super econômica.

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
