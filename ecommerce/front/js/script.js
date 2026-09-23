// ==========================================================================
// DECK LOGIC - SCRIPTS DO SITE
// Um único arquivo para todas as páginas: cada init* verifica se os
// elementos existem antes de agir.
// ==========================================================================

const CART_KEY = 'decklogic_cart';

// ---- Utilitários ----
function formatPrice(value) {
    return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function escapeHtml(text) {
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// Caminhos: páginas do front ficam em front/, as do painel em back/<pasta>/.
// O <body data-front="..."> diz onde está o front a partir da página atual.
const FRONT_BASE = document.body ? (document.body.dataset.front || '') : '';
const BACK_BASE = FRONT_BASE + '../back/';

function showToast(message) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `<i class="fa-solid fa-circle-check"></i><span>${escapeHtml(message)}</span>`;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 2800);
}

// ---- Menu hambúrguer (gaveta lateral no celular) ----
function initNav() {
    const toggle = document.querySelector('.nav-toggle');
    const menu = document.getElementById('menu-principal');
    const backdrop = document.getElementById('nav-backdrop');
    const closeBtn = document.querySelector('.nav-close');
    if (!toggle || !menu) return;

    function setOpen(open) {
        menu.classList.toggle('is-open', open);
        if (backdrop) backdrop.classList.toggle('is-visible', open);
        document.body.classList.toggle('nav-open', open);
        toggle.setAttribute('aria-expanded', open);
        if (open && closeBtn) closeBtn.focus();
        if (!open) toggle.focus();
    }

    toggle.addEventListener('click', () => setOpen(true));
    if (closeBtn) closeBtn.addEventListener('click', () => setOpen(false));
    if (backdrop) backdrop.addEventListener('click', () => setOpen(false));

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && menu.classList.contains('is-open')) setOpen(false);
    });
}

// ---- Carrinho ----
function getCart() {
    try {
        return JSON.parse(localStorage.getItem(CART_KEY)) || [];
    } catch (e) {
        return [];
    }
}

function saveCart(cart) {
    try {
        localStorage.setItem(CART_KEY, JSON.stringify(cart));
    } catch (e) {
        // navegador sem localStorage: carrinho fica só na sessão atual
    }
    updateCartBadge();
}

function updateCartBadge() {
    const totalQty = getCart().reduce((sum, item) => sum + item.qty, 0);
    const badge = document.getElementById('cart-count');
    if (badge) badge.textContent = totalQty;
}

// Chamado pelos botões "Adicionar": os dados do produto ficam nos data-* do botão
function addToCart(button) {
    const product = {
        id: button.dataset.id,
        name: button.dataset.name,
        course: button.dataset.course,
        courseClass: button.dataset.courseClass,
        img: button.dataset.img,
        price: Number(button.dataset.price)
    };

    const cart = getCart();
    const existing = cart.find(item => item.id === product.id);
    if (existing) {
        existing.qty += 1;
    } else {
        cart.push({ ...product, qty: 1 });
    }
    saveCart(cart);
    syncProductSteppers();
    showToast(`${product.name} adicionada ao carrinho`);
}

// Tira do carrinho perguntando antes (usado pela lixeira e pelo "-" quando a quantidade é 1)
function removeFromCart(id) {
    const item = getCart().find(i => i.id === id);
    if (item && !confirm(`"${item.name}" vai sair do carrinho. Deseja remover?`)) return;
    saveCart(getCart().filter(i => i.id !== id));
    if (item) showToast(`${item.name} removida do carrinho`);
    renderCartPage();
    syncProductSteppers();
}

function changeQty(id, delta) {
    const cart = getCart();
    const item = cart.find(i => i.id === id);
    if (!item) return;

    if (item.qty + delta <= 0) {
        removeFromCart(id);
        return;
    }
    item.qty += delta;
    saveCart(cart);
    renderCartPage();
    syncProductSteppers();
}

// Nos cards de produto: depois do 1º "Adicionar", o botão vira [- qtd +].
// Com 1 unidade o "-" vira lixeira.
function initProductSteppers() {
    document.querySelectorAll('.btn-add-cart[data-id]').forEach(btn => {
        const stepper = document.createElement('div');
        stepper.className = 'qty-stepper';
        stepper.dataset.id = btn.dataset.id;
        stepper.hidden = true;
        stepper.innerHTML = `
            <button type="button" class="qty-dec"></button>
            <span class="qty-value" aria-live="polite">0</span>
            <button type="button" class="qty-inc" aria-label="Adicionar mais uma"><i class="fa-solid fa-plus"></i></button>
        `;
        stepper.querySelector('.qty-dec').addEventListener('click', () => changeQty(btn.dataset.id, -1));
        stepper.querySelector('.qty-inc').addEventListener('click', () => addToCart(btn));
        btn.after(stepper);
    });
    syncProductSteppers();
}

function syncProductSteppers() {
    const cart = getCart();
    document.querySelectorAll('.qty-stepper').forEach(stepper => {
        const btn = stepper.previousElementSibling;
        const item = cart.find(i => i.id === stepper.dataset.id);
        const qty = item ? item.qty : 0;
        btn.hidden = qty > 0;
        stepper.hidden = qty === 0;
        stepper.querySelector('.qty-value').textContent = qty;
        const dec = stepper.querySelector('.qty-dec');
        const remover = qty === 1;
        dec.classList.toggle('is-remove', remover);
        dec.innerHTML = `<i class="fa-solid ${remover ? 'fa-trash' : 'fa-minus'}"></i>`;
        dec.setAttribute('aria-label', remover ? 'Remover do carrinho' : 'Diminuir quantidade');
    });
}

function renderCartPage() {
    const container = document.getElementById('cart-page-container');
    if (!container) return;

    const cart = getCart();

    if (cart.length === 0) {
        container.innerHTML = `
            <div class="empty-cart">
                <i class="fa-solid fa-cart-shopping"></i>
                <h2>Seu carrinho está vazio</h2>
                <p>Adicione uma carta de Informática, Mecânica ou Eletrônica para começar.</p>
                <a href="produtos.html" class="btn btn-primary">
                    <i class="fa-solid fa-arrow-right"></i> Ver produtos
                </a>
            </div>
        `;
        return;
    }

    const itemsHtml = cart.map(item => `
        <article class="cart-item ${item.courseClass || ''}">
            <div class="cart-item-img">
                ${item.img ? `<img src="${item.img}" alt="" width="47" height="80">` : ''}
            </div>
            <div class="cart-item-info">
                <span class="cart-item-course">${escapeHtml(item.course)}</span>
                <h3>${escapeHtml(item.name)}</h3>
            </div>
            <div class="cart-qty">
                ${item.qty === 1
                    ? `<button type="button" class="is-remove" onclick="changeQty('${item.id}', -1)" aria-label="Remover do carrinho"><i class="fa-solid fa-trash"></i></button>`
                    : `<button type="button" onclick="changeQty('${item.id}', -1)" aria-label="Diminuir quantidade"><i class="fa-solid fa-minus"></i></button>`}
                <span>${item.qty}</span>
                <button type="button" onclick="changeQty('${item.id}', 1)" aria-label="Aumentar quantidade"><i class="fa-solid fa-plus"></i></button>
            </div>
            <div class="cart-item-price">${formatPrice(item.price * item.qty)}</div>
            <button type="button" class="cart-remove" onclick="removeFromCart('${item.id}')" aria-label="Remover ${escapeHtml(item.name)}">
                <i class="fa-solid fa-trash"></i>
            </button>
        </article>
    `).join('');

    const subtotal = cart.reduce((sum, item) => sum + item.price * item.qty, 0);

    container.innerHTML = `
        <div class="cart-layout">
            <div class="cart-items">${itemsHtml}</div>
            <aside class="cart-summary">
                <h2>Resumo do pedido</h2>
                <div class="cart-summary-row">
                    <span>Subtotal</span>
                    <span>${formatPrice(subtotal)}</span>
                </div>
                <div class="cart-summary-row">
                    <span>Entrega</span>
                    <span>Retirada no CTI</span>
                </div>
                <div class="cart-summary-total">
                    <span>Total</span>
                    <span>${formatPrice(subtotal)}</span>
                </div>
                <button type="button" class="btn btn-primary btn-block" onclick="finalizarEncomenda()">
                    Finalizar encomenda
                </button>
            </aside>
        </div>
    `;
}

// ---- Carrossel do banner ----
function initCarousel() {
    const carousel = document.querySelector('.carousel');
    if (!carousel) return;

    const slides = carousel.querySelectorAll('.carousel-slide');
    const dots = carousel.querySelectorAll('.indicator-dot');
    const btnPrev = carousel.querySelector('.carousel-btn.prev');
    const btnNext = carousel.querySelector('.carousel-btn.next');

    let current = 0;
    let timer = null;

    function show(index) {
        current = (index + slides.length) % slides.length;
        slides.forEach((s, i) => {
            s.classList.toggle('active', i === current);
            s.setAttribute('aria-hidden', i !== current);
        });
        dots.forEach((d, i) => d.classList.toggle('active', i === current));
    }

    function restart() {
        clearInterval(timer);
        timer = setInterval(() => show(current + 1), 6000);
    }

    if (btnNext) btnNext.addEventListener('click', () => { show(current + 1); restart(); });
    if (btnPrev) btnPrev.addEventListener('click', () => { show(current - 1); restart(); });
    dots.forEach((dot, i) => dot.addEventListener('click', () => { show(i); restart(); }));

    // Arrastar pro lado no celular (as setas ficam escondidas em telas pequenas)
    let touchStartX = null;
    carousel.addEventListener('touchstart', (e) => { touchStartX = e.touches[0].clientX; }, { passive: true });
    carousel.addEventListener('touchend', (e) => {
        if (touchStartX === null) return;
        const dx = e.changedTouches[0].clientX - touchStartX;
        if (Math.abs(dx) > 40) {
            show(current + (dx < 0 ? 1 : -1));
            restart();
        }
        touchStartX = null;
    });

    show(0);
    restart();
}

// ---- Vitrine: filtro por curso + busca (?q=) ----
function normalize(text) {
    return text.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
}

function initProductFilters() {
    const grid = document.querySelector('[data-products]');
    if (!grid) return;

    const cards = grid.querySelectorAll('.product-card');
    const chips = document.querySelectorAll('.filter-chip[data-filter]');
    const empty = document.getElementById('products-empty');
    const info = document.getElementById('search-result-info');

    const query = new URLSearchParams(window.location.search).get('q') || '';
    let category = 'todos';

    const searchInput = document.getElementById('busca');
    if (searchInput && query) searchInput.value = query;

    if (info && query) {
        info.innerHTML = `Resultados para <strong>“${escapeHtml(query)}”</strong> · <a href="produtos.html">limpar busca</a>`;
        info.hidden = false;
    }

    function apply() {
        const q = normalize(query);
        let visible = 0;
        cards.forEach(card => {
            const matchCategory = category === 'todos' || card.dataset.category === category;
            const matchQuery = !q || normalize(card.textContent).includes(q);
            const show = matchCategory && matchQuery;
            card.hidden = !show;
            if (show) visible++;
        });
        if (empty) empty.hidden = visible > 0;
    }

    chips.forEach(chip => {
        chip.addEventListener('click', () => {
            chips.forEach(c => {
                c.classList.remove('active');
                c.setAttribute('aria-pressed', 'false');
            });
            chip.classList.add('active');
            chip.setAttribute('aria-pressed', 'true');
            category = chip.dataset.filter;
            apply();
        });
    });

    apply();
}

// ---- Login (o form faz POST direto pro back/usuarios/login.php) ----
function initLogin() {
    const form = document.getElementById('loginForm');
    if (!form) return;

    const params = new URLSearchParams(window.location.search);
    const mostrar = (id) => { const el = document.getElementById(id); if (el) el.hidden = false; };
    if (params.has('erro')) mostrar('login-erro');
    if (params.has('cadastro')) mostrar('login-cadastro');
    if (params.get('next') === 'carrinho') mostrar('login-precisa');

    const next = document.getElementById('login-next');
    if (next && params.get('next')) next.value = params.get('next');
}

// ---- Mostrar/ocultar senha ----
function initPasswordToggles() {
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.target);
            const icon = btn.querySelector('i');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('fa-eye', !isPassword);
            icon.classList.toggle('fa-eye-slash', isPassword);
            btn.setAttribute('aria-label', isPassword ? 'Ocultar senha' : 'Mostrar senha');
        });
    });
}

// ---- Cadastro (valida no navegador e envia pro back/usuarios/insertUsuario.php) ----
function handleCadastro(event) {
    const senha = document.getElementById('cad-senha');
    const confirmar = document.getElementById('cad-confirmar');
    const erro = document.getElementById('cad-erro');

    let msg = '';
    if (senha.value.length < 6) msg = 'A senha precisa ter pelo menos 6 caracteres.';
    else if (senha.value !== confirmar.value) msg = 'As senhas não coincidem.';

    if (msg) {
        event.preventDefault();
        erro.textContent = msg;
        erro.classList.add('show');
        return false;
    }
    erro.classList.remove('show');
    return true;
}

function initCadastro() {
    const params = new URLSearchParams(window.location.search);
    const aviso = document.getElementById('cad-email-existe');
    if (aviso && params.get('erro') === 'email') aviso.hidden = false;

    // Máscara (14) 99999-9999
    const tel = document.getElementById('cad-telefone');
    if (!tel) return;
    tel.addEventListener('input', () => {
        const d = tel.value.replace(/\D/g, '').slice(0, 11);
        let v = d;
        if (d.length > 2) v = `(${d.slice(0, 2)}) ${d.slice(2)}`;
        if (d.length > 7) v = `(${d.slice(0, 2)}) ${d.slice(2, d.length - 4)}-${d.slice(-4)}`;
        tel.value = v;
    });
}

// ---- Sessão: troca "Entrar/Cadastre-se" por "nome/Sair" quando logado ----
function setLinkText(link, text) {
    const span = link.querySelector('span');
    if (span) {
        span.textContent = text;
    } else {
        const icon = link.querySelector('i');
        link.textContent = text;
        if (icon) link.prepend(icon);
    }
}

// Consulta o back uma vez. null = sem sessão; false = servidor sem PHP (ex.: Live Server, hospedagem estática)
let backStatus;
const sessaoPromise = (window.fetch
    ? fetch(BACK_BASE + 'usuarios/sessao.php', { credentials: 'same-origin' })
        .then(r => {
            const json = (r.headers.get('content-type') || '').includes('application/json');
            return r.ok && json ? r.json() : false;
        })
        .catch(() => false)
    : Promise.resolve(false)
).then(res => { backStatus = res; return res; });

function initSessao() {
    const contas = document.querySelectorAll('[data-auth="conta"]');
    const sairs = document.querySelectorAll('[data-auth="sair"]');
    sessaoPromise.then(sessao => {
        if (!sessao || !sessao.logado) return;
        const primeiroNome = sessao.nome.split(' ')[0];
        const destino = sessao.admin ? 'painel/estoque.php' : 'compras/encomendas.php';
        contas.forEach(a => {
            a.href = BACK_BASE + destino;
            a.setAttribute('aria-label', sessao.admin ? 'Painel administrativo' : 'Minhas encomendas');
            setLinkText(a, sessao.admin ? 'Painel' : primeiroNome);
        });
        sairs.forEach(a => {
            a.href = BACK_BASE + 'usuarios/logout.php';
            setLinkText(a, 'Sair');
        });
    });
}

// Sem PHP no servidor o POST do formulário vira "HTTP ERROR 405": avisa em vez de quebrar
const AVISO_SEM_PHP = 'Login, cadastro e encomendas precisam do servidor com PHP. ' +
    'Abra o site pelo servidor PHP (veja o README) em vez do Live Server.';

function semBack() {
    return backStatus === false;
}

function mostrarAvisoSemBack(form) {
    let aviso = form.parentElement.querySelector('.form-alert--sem-php');
    if (!aviso) {
        aviso = document.createElement('p');
        aviso.className = 'form-alert form-alert--error form-alert--sem-php';
        aviso.textContent = AVISO_SEM_PHP;
        form.parentElement.insertBefore(aviso, form);
    }
}

function initGuardaBack() {
    document.querySelectorAll('form[action*="back/"]').forEach(form => {
        form.addEventListener('submit', (e) => {
            if (semBack()) {
                e.preventDefault();
                mostrarAvisoSemBack(form);
            }
        });
    });
}

// ---- Tema claro/escuro (botão no header; sem escolha = segue o sistema) ----
function temaAtual() {
    const escolhido = document.documentElement.dataset.theme;
    if (escolhido) return escolhido;
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

function initTema() {
    const btn = document.getElementById('theme-toggle');
    if (!btn) return;

    function atualizarBotao() {
        const escuro = temaAtual() === 'dark';
        btn.innerHTML = `<i class="fa-solid ${escuro ? 'fa-sun' : 'fa-moon'}"></i>`;
        btn.setAttribute('aria-label', escuro ? 'Ativar tema claro' : 'Ativar tema escuro');
        btn.title = btn.getAttribute('aria-label');
    }

    btn.addEventListener('click', () => {
        const novo = temaAtual() === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = novo;
        try { localStorage.setItem('decklogic_tema', novo); } catch (e) { /* ignora */ }
        atualizarBotao();
    });

    if (window.matchMedia) {
        const mq = window.matchMedia('(prefers-color-scheme: dark)');
        if (mq.addEventListener) mq.addEventListener('change', atualizarBotao);
    }
    atualizarBotao();
}

// ---- Encomenda: envia o carrinho pro back/compras/finalizar.php ----
function finalizarEncomenda() {
    const itens = getCart().filter(i => /^\d+$/.test(String(i.id)));
    if (!itens.length) return;
    if (semBack()) {
        alert(AVISO_SEM_PHP);
        return;
    }

    const form = document.createElement('form');
    form.method = 'post';
    form.action = BACK_BASE + 'compras/finalizar.php';
    itens.forEach(item => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `itens[${item.id}]`;
        input.value = item.qty;
        form.appendChild(input);
    });
    document.body.appendChild(form);
    form.submit();
}

function initEncomendaOk() {
    const container = document.getElementById('cart-page-container');
    if (!container) return;
    const params = new URLSearchParams(window.location.search);
    if (params.get('encomenda') !== 'ok') return;

    saveCart([]);
    container.innerHTML = `
        <div class="empty-cart">
            <i class="fa-solid fa-circle-check"></i>
            <h2>Encomenda registrada!</h2>
            <p>Retire suas cartas no CTI. Acompanhe o status em Minhas encomendas.</p>
            <a href="${BACK_BASE}compras/encomendas.php" class="btn btn-primary">
                <i class="fa-solid fa-box"></i> Minhas encomendas
            </a>
        </div>
    `;
}

// ---- Contato / Newsletter ----
function initSimpleForms() {
    const contato = document.getElementById('contatoForm');
    if (contato) {
        contato.addEventListener('submit', (e) => {
            e.preventDefault();
            showToast('Mensagem enviada! Responderemos em breve.');
            contato.reset();
        });
    }

    const newsletter = document.getElementById('newsletterForm');
    if (newsletter) {
        newsletter.addEventListener('submit', (e) => {
            e.preventDefault();
            showToast('Inscrição feita! Você vai receber os lançamentos.');
            newsletter.reset();
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initNav();
    initCarousel();
    initProductFilters();
    initProductSteppers();
    initLogin();
    initPasswordToggles();
    initCadastro();
    initSimpleForms();
    initSessao();
    initGuardaBack();
    initTema();
    updateCartBadge();
    renderCartPage();
    initEncomendaOk();

    // carrinho alterado em outra aba: atualiza contador, cards e página do carrinho
    window.addEventListener('storage', (e) => {
        if (e.key !== CART_KEY) return;
        updateCartBadge();
        syncProductSteppers();
        renderCartPage();
    });
});
