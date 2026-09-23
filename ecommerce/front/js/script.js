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
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

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

    button.classList.add('is-added');
    setTimeout(() => button.classList.remove('is-added'), 1200);
    showToast(`${product.name} adicionada ao carrinho`);
}

function removeFromCart(id) {
    saveCart(getCart().filter(item => item.id !== id));
    renderCartPage();
}

function changeQty(id, delta) {
    const cart = getCart();
    const item = cart.find(i => i.id === id);
    if (!item) return;

    item.qty += delta;
    if (item.qty <= 0) {
        removeFromCart(id);
        return;
    }
    saveCart(cart);
    renderCartPage();
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
                <button type="button" onclick="changeQty('${item.id}', -1)" aria-label="Diminuir quantidade">-</button>
                <span>${item.qty}</span>
                <button type="button" onclick="changeQty('${item.id}', 1)" aria-label="Aumentar quantidade">+</button>
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
                <button type="button" class="btn btn-primary btn-block" onclick="alert('Checkout ainda não implementado — funcionalidade futura!')">
                    Finalizar compra
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

// ---- Login ----
function initLogin() {
    const form = document.getElementById('loginForm');
    if (!form) return;

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        // Integração futura com o back-end (PHP)
        alert('Login enviado! (integração com back-end ainda pendente)');
    });
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

// ---- Cadastro ----
function handleCadastro(event) {
    event.preventDefault();

    const nome = document.getElementById('cad-nome');
    const senha = document.getElementById('cad-senha');
    const confirmar = document.getElementById('cad-confirmar');
    const erro = document.getElementById('cad-erro');

    if (senha.value.length < 6) {
        erro.textContent = 'A senha precisa ter pelo menos 6 caracteres.';
        erro.classList.add('show');
        return false;
    }

    if (senha.value !== confirmar.value) {
        erro.textContent = 'As senhas não coincidem.';
        erro.classList.add('show');
        return false;
    }

    erro.classList.remove('show');

    // Sem backend ainda: só mostra a confirmação visual
    document.getElementById('cadastro-form-wrap').innerHTML = `
        <div class="auth-success">
            <i class="fa-solid fa-circle-check"></i>
            <h1>Cadastro realizado!</h1>
            <p>Bem-vindo(a), ${escapeHtml(nome.value)}! Agora você já pode fazer login.</p>
            <a href="login.html" class="btn btn-primary">
                <i class="fa-solid fa-arrow-right"></i> Ir para o login
            </a>
        </div>
    `;

    return false;
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
    initLogin();
    initPasswordToggles();
    initSimpleForms();
    updateCartBadge();
    renderCartPage();
});
