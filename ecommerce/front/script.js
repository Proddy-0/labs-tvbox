// ==========================================================================
// LOGICDECK - SCRIPTS DO SITE
// ==========================================================================

const CART_KEY = 'logicdeck_cart';

// ---- Funções do Carrinho ----
function getCart() {
    try {
        return JSON.parse(localStorage.getItem(CART_KEY)) || [];
    } catch (e) {
        return [];
    }
}

function saveCart(cart) {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    updateCartBadge();
}

function updateCartBadge() {
    const cart = getCart();
    const totalQty = cart.reduce((sum, item) => sum + item.qty, 0);
    const badge = document.getElementById('cart-count');
    if (badge) badge.textContent = totalQty;
}

function addToCart(product) {
    const cart = getCart();
    const existing = cart.find(item => item.id === product.id);

    if (existing) {
        existing.qty += 1;
    } else {
        cart.push({ ...product, qty: 1 });
    }

    saveCart(cart);
    renderCartPage();
}

function removeFromCart(id) {
    let cart = getCart();
    cart = cart.filter(item => item.id !== id);
    saveCart(cart);
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

function formatPrice(value) {
    return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

// ---- Renderiza a Página do Carrinho ----
function renderCartPage() {
    const container = document.getElementById('cart-page-container');
    if (!container) return; // não estamos na página do carrinho

    const cart = getCart();

    if (cart.length === 0) {
        container.innerHTML = `
            <div class="empty-cart">
                <i class="fa-solid fa-cart-shopping"></i>
                <h3>Seu carrinho está vazio</h3>
                <p>Adicione um baralho de Informática, Mecânica ou Eletrônica para começar.</p>
                <a href="produtos.html" class="btn-hero" style="display:inline-flex;">
                    <i class="fa-solid fa-arrow-right"></i> Ver Produtos
                </a>
            </div>
        `;
        return;
    }

    const itemsHtml = cart.map(item => `
        <div class="cart-item">
            <div class="cart-item-img">
                <i class="fa-solid fa-layer-group" style="font-size:1.5rem; color:var(--primary); opacity:0.6;"></i>
            </div>
            <div class="cart-item-info">
                <span class="cart-item-course" style="color:${item.courseColor};">${item.course}</span>
                <h4>${item.name}</h4>
            </div>
            <div class="cart-qty">
                <button onclick="changeQty('${item.id}', -1)">-</button>
                <span>${item.qty}</span>
                <button onclick="changeQty('${item.id}', 1)">+</button>
            </div>
            <div class="cart-item-price">${formatPrice(item.price * item.qty)}</div>
            <button class="cart-remove" onclick="removeFromCart('${item.id}')" title="Remover">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    `).join('');

    const subtotal = cart.reduce((sum, item) => sum + item.price * item.qty, 0);

    container.innerHTML = `
        <div class="cart-layout">
            <div class="cart-items">${itemsHtml}</div>
            <div class="cart-summary">
                <h3>Resumo do Pedido</h3>
                <div class="cart-summary-row">
                    <span>Subtotal</span>
                    <span>${formatPrice(subtotal)}</span>
                </div>
                <div class="cart-summary-row">
                    <span>Frete</span>
                    <span>Grátis</span>
                </div>
                <div class="cart-summary-total">
                    <span>Total</span>
                    <span>${formatPrice(subtotal)}</span>
                </div>
                <button class="btn-checkout" onclick="alert('Checkout ainda não implementado — funcionalidade futura!')">
                    Finalizar Compra
                </button>
            </div>
        </div>
    `;
}

// ---- Carrossel do Banner ----
function initCarousel() {
    const container = document.querySelector('.carousel-container');
    if (!container) return;

    const slides = container.querySelectorAll('.carousel-slide');
    const dots = container.querySelectorAll('.indicator-dot');
    const btnPrev = container.querySelector('.carousel-btn.prev');
    const btnNext = container.querySelector('.carousel-btn.next');

    let currentSlide = 0;
    let autoPlayTimer = null;

    function showSlide(index) {
        slides.forEach(s => s.classList.remove('active'));
        dots.forEach(d => d.classList.remove('active'));
        currentSlide = (index + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
        if (dots[currentSlide]) dots[currentSlide].classList.add('active');
    }

    function nextSlide() { showSlide(currentSlide + 1); }
    function prevSlide() { showSlide(currentSlide - 1); }

    function resetAutoPlay() {
        clearInterval(autoPlayTimer);
        autoPlayTimer = setInterval(nextSlide, 6000);
    }

    if (btnNext) btnNext.addEventListener('click', () => { nextSlide(); resetAutoPlay(); });
    if (btnPrev) btnPrev.addEventListener('click', () => { prevSlide(); resetAutoPlay(); });
    dots.forEach((dot, i) => dot.addEventListener('click', () => { showSlide(i); resetAutoPlay(); }));

    resetAutoPlay();
}
// ---- Cadastro ----
function handleCadastro(event) {
    event.preventDefault();

    const nome = document.getElementById('cad-nome');
    const email = document.getElementById('cad-email');
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
            <h2>Cadastro realizado!</h2>
            <p style="color:var(--text-muted); margin: 0.75rem 0 1.5rem;">
                Bem-vindo(a), ${nome.value}! Agora você já pode fazer login.
            </p>
            <a href="login.html" class="btn-hero" style="display:inline-flex;">
                <i class="fa-solid fa-arrow-right"></i> Ir para o Login
            </a>
        </div>
    `;

    return false;
}
document.addEventListener('DOMContentLoaded', () => {
    initCarousel();
    updateCartBadge();
    renderCartPage();
});