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
function addToCart(button, quantidade = 1) {
    const product = {
        id: button.dataset.id,
        name: button.dataset.name,
        course: button.dataset.course,
        courseClass: button.dataset.courseClass,
        courseStyle: button.dataset.courseStyle || '',
        img: button.dataset.img,
        price: Number(button.dataset.price),
        stock: button.dataset.stock ? Number(button.dataset.stock) : null   // null = sem limite conhecido
    };

    const cart = getCart();
    const existing = cart.find(item => item.id === product.id);
    const limite = product.stock === null ? Infinity : product.stock;
    const noCarrinho = existing ? existing.qty : 0;
    const adicionar = Math.min(quantidade, limite - noCarrinho);
    if (adicionar <= 0) {
        showToast(limite <= 0
            ? `${product.name} está esgotada`
            : `Só há ${limite} unidade${limite > 1 ? 's' : ''} de ${product.name}, e já estão no carrinho`);
        return;
    }
    if (existing) {
        existing.qty += adicionar;
        existing.stock = product.stock;
    } else {
        cart.push({ ...product, qty: adicionar });
    }
    saveCart(cart);
    syncProductSteppers();
    if (adicionar < quantidade) {
        showToast(`Só havia estoque pra ${adicionar}: ${adicionar} ${adicionar > 1 ? 'adicionadas' : 'adicionada'} ao carrinho`);
    } else {
        showToast(adicionar > 1 ? `${adicionar}× ${product.name} adicionadas ao carrinho` : `${product.name} adicionada ao carrinho`);
    }
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
    if (delta > 0 && item.stock != null && item.qty + delta > item.stock) {
        showToast(`Só há ${item.stock} unidade${item.stock > 1 ? 's' : ''} de ${item.name}`);
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
        <article class="cart-item ${item.courseClass || ''}"${item.courseStyle ? ` style="${item.courseStyle}"` : ''}>
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

// ---- Perfil do usuário logado (front/perfil.html) ----
function renderPerfilPage() {
    const container = document.getElementById('perfil-page-container');
    if (!container) return;

    fetch(BACK_BASE + 'usuarios/dadosUsuario.php', { credentials: 'same-origin' })
        .then(r => {
            if (r.status === 401) {
                window.location.href = 'login.html';
                return null;
            }
            return r.json();
        })
        .then(usuario => {
            if (!usuario) return;
            container.innerHTML = `
                <form class="form-card" id="perfilForm">
                    <div class="form-group">
                        <label for="perfil-nome">Nome</label>
                        <input type="text" id="perfil-nome" name="nome" class="form-control" value="${escapeHtml(usuario.nome)}" required>
                    </div>
                    <div class="form-group">
                        <label for="perfil-email">E-mail</label>
                        <input type="email" id="perfil-email" name="email" class="form-control" value="${escapeHtml(usuario.email)}" required>
                    </div>
                    <div class="form-group">
                        <label for="perfil-telefone">Telefone <span class="req" aria-hidden="true">*</span></label>
                        <input type="tel" id="perfil-telefone" name="telefone" class="form-control" value="${escapeHtml(usuario.telefone || '')}" placeholder="(14) 99999-9999" maxlength="15" inputmode="tel" required>
                    </div>
                    ${usuario.curso ? `
                    <div class="form-group">
                        <label>Curso</label>
                        <input type="text" class="form-control" value="${escapeHtml(usuario.curso)}" disabled>
                    </div>` : ''}
                    <div class="form-group">
                        <label>Perfil</label>
                        <input type="text" class="form-control" value="${usuario.admin ? 'Administrador' : 'Cliente'}" disabled>
                    </div>
                    <p class="form-error" id="perfil-msg"></p>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa-solid fa-floppy-disk"></i> Salvar alterações
                    </button>
                </form>
            `;

            mascaraTelefone(document.getElementById('perfil-telefone'));

            document.getElementById('perfilForm').addEventListener('submit', (e) => {
                e.preventDefault();
                const msg = document.getElementById('perfil-msg');
                msg.classList.remove('show');

                const dados = new URLSearchParams(new FormData(e.target));

                fetch(BACK_BASE + 'usuarios/atualizarPerfil.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: dados
                })
                    .then(r => r.json().then(data => ({ status: r.status, data })))
                    .then(({ status, data }) => {
                        if (status !== 200) {
                            msg.textContent = data.erro || 'Não foi possível salvar.';
                            msg.classList.add('show');
                            return;
                        }
                        showToast('Perfil atualizado com sucesso!');
                    })
                    .catch(() => {
                        msg.textContent = 'Erro ao conectar com o servidor.';
                        msg.classList.add('show');
                    });
            });
        })
        .catch(() => {
            container.innerHTML = `<p class="form-alert form-alert--error">Não foi possível carregar seu perfil.</p>`;
        });
    renderMinhasCompras();
}

// ---- Minhas compras (perfil): encomendas do usuário com o preço pago em cada item ----
const STATUS_COMPRA = {
    reservado: 'Reservado · retire no CTI',
    entregue: 'Entregue',
    cancelado: 'Cancelado'
};

function renderMinhasCompras() {
    const container = document.getElementById('perfil-compras');
    if (!container) return;
    if (new URLSearchParams(window.location.search).has('cancelada')) showToast('Encomenda cancelada.');

    fetch(BACK_BASE + 'compras/minhasCompras.php', { credentials: 'same-origin' })
        .then(r => (r.ok ? r.json() : Promise.reject(r.status)))
        .then(compras => {
            if (!compras.length) {
                container.innerHTML = `
                    <div class="empty-cart">
                        <i class="fa-solid fa-box-open"></i>
                        <h2>Nenhuma compra ainda</h2>
                        <p>Escolha suas cartas e finalize pelo carrinho.</p>
                        <a href="produtos.html" class="btn btn-primary"><i class="fa-solid fa-arrow-right"></i> Ver produtos</a>
                    </div>`;
                return;
            }
            container.innerHTML = compras.map(c => `
                <article class="order-card">
                    <div class="order-head">
                        <div>
                            <strong>Encomenda #${c.id}</strong>
                            <span> · ${escapeHtml(c.data)}</span>
                        </div>
                        <span class="status-pill status-${escapeHtml(c.status)}">${escapeHtml(STATUS_COMPRA[c.status] || c.status)}</span>
                    </div>
                    <ul class="order-items">
                        ${c.itens.map(i => `
                            <li>
                                <span>${i.quantidade}× ${escapeHtml(i.nome)} <small class="order-unit">(${formatPrice(i.valor_unitario)} cada)</small></span>
                                <span>${formatPrice(i.subtotal)}</span>
                            </li>`).join('')}
                        ${c.acrescimo ? `
                            <li>
                                <span>${c.acrescimo < 0 ? 'Desconto' : 'Acréscimo'}</span>
                                <span>${formatPrice(c.acrescimo)}</span>
                            </li>` : ''}
                    </ul>
                    <div class="order-total">
                        <span>Total</span>
                        <span>${formatPrice(c.total)}</span>
                    </div>
                    ${c.status === 'reservado' ? `
                    <form class="order-actions" action="${BACK_BASE}compras/cancelar.php" method="post"
                        onsubmit="return confirm('Cancelar a encomenda #${c.id}?')">
                        <input type="hidden" name="id_compra" value="${c.id}">
                        <input type="hidden" name="voltar" value="perfil">
                        <button type="submit" class="btn btn-danger-outline btn-sm"><i class="fa-solid fa-xmark"></i> Cancelar encomenda</button>
                    </form>` : ''}
                </article>
            `).join('');
        })
        .catch(() => {
            container.innerHTML = `<p class="form-alert form-alert--error">Não foi possível carregar suas compras.</p>`;
        });
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

// ---- Catálogo: cards montados a partir do banco (back/produtos/catalogo.php) ----
// Curso do produto (tabela curso, cadastrada no painel) -> nome e cor do card.
// Curso com "classe" usa o estilo pronto do CSS (is-info/is-mec/is-eletro); sem classe, usa a cor cadastrada.
function cursoDoProduto(cursos, idCurso) {
    const c = cursos.find(c => c.id === idCurso);
    if (!c) return { id: '', nome: '', classe: '', estilo: '' };
    const estilo = c.classe ? '' :
        `--course:${c.cor};--course-dark:${c.cor};--course-ink:${c.cor};--course-soft:color-mix(in srgb, ${c.cor} 14%, transparent)`;
    return { id: String(c.id), nome: c.nome, classe: c.classe, estilo };
}

// Uma requisição por página, compartilhada por vitrine, destaques e carrinho
let catalogoPromise = null;
function carregarCatalogo() {
    if (!catalogoPromise) {
        catalogoPromise = fetch(BACK_BASE + 'produtos/catalogo.php', { credentials: 'same-origin' })
            .then(r => {
                const json = (r.headers.get('content-type') || '').includes('application/json');
                if (!r.ok || !json) throw new Error('catálogo indisponível');
                return r.json();
            });
    }
    return catalogoPromise;
}

// detalhes vêm do PDF quebrados linha a linha ("microcontrola-|dores"): vira um parágrafo só
function textoDaCarta(linhas) {
    return linhas.reduce((txt, linha) => {
        if (!txt) return linha;
        return txt.endsWith('-') ? txt.slice(0, -1) + linha : `${txt} ${linha}`;
    }, '');
}

function linkProduto(id) {
    return `produto.html?id=${encodeURIComponent(id)}`;
}

// opcoes.badge = selo no canto da imagem
function cardProdutoHtml(p, curso, opcoes = {}) {
    const esgotado = p.estoque <= 0;
    const nome = escapeHtml(p.nome);
    const img = escapeHtml(p.imagem);
    const alt = escapeHtml(curso.nome ? `Carta do curso de ${curso.nome}` : p.nome);
    return `
        <article class="product-card ${curso.classe}${esgotado ? ' is-out' : ''}"${curso.estilo ? ` style="${curso.estilo}"` : ''} data-category="${curso.id}"
            data-detalhes="${escapeHtml(p.detalhes.join('|'))}">
            <a class="product-image-wrap" href="${linkProduto(p.id)}" aria-label="Ver detalhes de ${nome}">
                ${esgotado ? `<span class="product-badge product-badge--esgotado">Esgotado</span>`
                    : (opcoes.badge ? `<span class="product-badge">${escapeHtml(opcoes.badge)}</span>` : '')}
                ${p.imagem ? `<img src="${img}" alt="${alt}" loading="lazy">` : ''}
            </a>
            <div class="product-details">
                ${curso.nome ? `<span class="product-course-tag">${escapeHtml(curso.nome)}</span>` : ''}
                <h3><a href="${linkProduto(p.id)}">${nome}</a></h3>
                <p class="product-desc">${escapeHtml(p.descricao)}</p>
                <a class="btn-details" href="${linkProduto(p.id)}">Ver detalhes</a>
                <div class="product-bottom">
                    <span class="price-current">${formatPrice(p.valor)}</span>
                    <button type="button" class="btn-add-cart" onclick="addToCart(this)"
                        data-id="${p.id}" data-name="${nome}" data-course="${escapeHtml(curso.nome)}"
                        data-course-class="${curso.classe}" data-course-style="${curso.estilo}"
                        data-price="${p.valor}" data-img="${img}" data-stock="${p.estoque}"${esgotado ? ' disabled' : ''}>
                        <i class="fa-solid fa-cart-plus"></i> ${esgotado ? 'Esgotado' : 'Adicionar'}
                    </button>
                </div>
            </div>
        </article>
    `;
}

function initCatalogo() {
    const vitrine = document.querySelector('[data-products]');
    const destaque = document.querySelector('[data-products-destaque]');
    const statCartas = document.querySelector('[data-stat="cartas"]');
    if (statCartas) {
        carregarCatalogo().then(({ produtos }) => { statCartas.textContent = produtos.length; }).catch(() => {});
    }
    if (!vitrine && !destaque) return Promise.resolve();

    return carregarCatalogo()
        .then(({ cursos, produtos }) => {
            const card = (p, opcoes) => cardProdutoHtml(p, cursoDoProduto(cursos, p.curso), opcoes);
            if (vitrine) {
                vitrine.innerHTML = produtos.map(p => card(p)).join('');
                // um filtro por curso que tem produto à venda
                const filtros = document.querySelector('.category-filters');
                if (filtros) {
                    filtros.querySelectorAll('.filter-chip:not([data-filter="todos"])').forEach(c => c.remove());
                    cursos.filter(c => produtos.some(p => p.curso === c.id)).forEach(c => {
                        const info = cursoDoProduto(cursos, c.id);
                        filtros.insertAdjacentHTML('beforeend',
                            `<button type="button" class="filter-chip ${info.classe}"${info.estilo ? ` style="${info.estilo}"` : ''} data-filter="${info.id}" aria-pressed="false"><span class="dot"></span>${escapeHtml(info.nome)}</button>`);
                    });
                }
            }
            if (destaque) {
                // destaques = uma carta por curso (a mais recente de cada); a mais nova das três ganha o selo "Novo"
                const porCurso = cursos
                    .map(c => produtos.filter(p => p.curso === c.id).pop())
                    .filter(Boolean);
                const maisNova = Math.max(...porCurso.map(p => p.id));
                destaque.innerHTML = porCurso
                    .map(p => card(p, { badge: p.id === maisNova ? 'Novo' : '' }))
                    .join('');
            }
        })
        .catch(() => sessaoPromise.then(() => {
            const msg = `<p class="products-loading">Não foi possível carregar as cartas. ${semBack() ? AVISO_SEM_PHP : 'Tente novamente mais tarde.'}</p>`;
            if (vitrine) vitrine.innerHTML = msg;
            if (destaque) destaque.innerHTML = msg;
        }));
}

// ---- Página de detalhe do produto (produto.html?id=) ----
function initProdutoDetalhe() {
    const alvo = document.getElementById('produto-detalhe');
    if (!alvo) return Promise.resolve();
    const id = Number(new URLSearchParams(window.location.search).get('id'));

    return carregarCatalogo()
        .then(({ cursos, produtos }) => {
            const p = produtos.find(x => x.id === id);
            if (!p) {
                alvo.innerHTML = `
                    <div class="empty-cart">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <h2>Carta não encontrada</h2>
                        <p>Ela pode ter saído do catálogo.</p>
                        <a href="produtos.html" class="btn btn-primary"><i class="fa-solid fa-arrow-right"></i> Ver catálogo</a>
                    </div>`;
                return;
            }
            const curso = cursoDoProduto(cursos, p.curso);
            const nome = escapeHtml(p.nome);
            const esgotado = p.estoque <= 0;
            const maxQtd = Math.max(1, Math.min(99, p.estoque));
            document.title = `${p.nome} | Deck Logic`;
            document.getElementById('pd-breadcrumb').textContent = p.nome;

            alvo.innerHTML = `
                <article class="product-detail-card ${curso.classe}"${curso.estilo ? ` style="${curso.estilo}"` : ''}>
                    <div class="pd-media">
                        ${p.imagem ? `<img src="${escapeHtml(p.imagem)}" alt="${escapeHtml(curso.nome ? `Carta do curso de ${curso.nome}` : p.nome)}">` : ''}
                    </div>
                    <div class="pd-info">
                        ${curso.nome ? `<span class="product-course-tag">${escapeHtml(curso.nome)}</span>` : ''}
                        <h1>${nome}</h1>
                        <p class="pd-price">${formatPrice(p.valor)} <small>por unidade</small></p>
                        <p class="pd-stock ${esgotado ? 'is-out' : ''}">
                            <i class="fa-solid ${esgotado ? 'fa-circle-xmark' : 'fa-box'}" aria-hidden="true"></i>
                            ${esgotado ? 'Esgotado no momento' : 'Em estoque'}
                        </p>
                        ${p.detalhes.length ? `
                        <section class="pd-about" aria-labelledby="pd-about-titulo">
                            <span class="pd-about-label">O que está na carta</span>
                            <h2 class="pd-about-title" id="pd-about-titulo">${escapeHtml(p.descricao)}</h2>
                            <p class="pd-about-text">${escapeHtml(textoDaCarta(p.detalhes))}</p>
                        </section>` : `<p class="pd-desc">${escapeHtml(p.descricao)}</p>`}

                        <div class="pd-buy">
                            ${esgotado ? '' : `<label for="pd-qtd">Quantidade</label>`}
                            <div class="pd-qty"${esgotado ? ' hidden' : ''}>
                                <button type="button" class="pd-qty-btn" data-delta="-1" aria-label="Diminuir quantidade"><i class="fa-solid fa-minus"></i></button>
                                <input type="number" id="pd-qtd" value="1" min="1" max="${maxQtd}" inputmode="numeric"${esgotado ? ' disabled' : ''}>
                                <button type="button" class="pd-qty-btn" data-delta="1" aria-label="Aumentar quantidade"><i class="fa-solid fa-plus"></i></button>
                            </div>
                            <p class="pd-total"${esgotado ? ' hidden' : ''}>Total: <strong id="pd-total">${formatPrice(p.valor)}</strong></p>
                            <div class="pd-actions">
                                <button type="button" class="btn btn-primary" id="pd-add"
                                    data-id="${p.id}" data-name="${nome}" data-course="${escapeHtml(curso.nome)}"
                                    data-course-class="${curso.classe}" data-course-style="${curso.estilo}"
                                    data-price="${p.valor}" data-img="${escapeHtml(p.imagem)}" data-stock="${p.estoque}"${esgotado ? ' disabled' : ''}>
                                    <i class="fa-solid fa-cart-plus"></i> ${esgotado ? 'Esgotado' : 'Adicionar ao carrinho'}
                                </button>
                                <button type="button" class="btn btn-accent" id="pd-comprar"${esgotado ? ' disabled' : ''}><i class="fa-solid fa-bolt"></i> Comprar agora</button>
                            </div>
                            <p class="pd-note"><i class="fa-solid fa-school" aria-hidden="true"></i> Retirada no CTI · pagamento no Pix ou dinheiro</p>
                        </div>
                    </div>
                </article>`;

            const qtd = document.getElementById('pd-qtd');
            const total = document.getElementById('pd-total');
            const lerQtd = () => Math.min(maxQtd, Math.max(1, parseInt(qtd.value, 10) || 1));
            const atualizar = () => { qtd.value = lerQtd(); total.textContent = formatPrice(p.valor * lerQtd()); };
            alvo.querySelectorAll('.pd-qty-btn').forEach(b => b.addEventListener('click', () => {
                qtd.value = lerQtd() + Number(b.dataset.delta);
                atualizar();
            }));
            qtd.addEventListener('change', atualizar);
            const botao = document.getElementById('pd-add');
            botao.addEventListener('click', () => { addToCart(botao, lerQtd()); });
            document.getElementById('pd-comprar').addEventListener('click', () => {
                addToCart(botao, lerQtd());
                window.location.href = 'carrinho.html';
            });

            // outras cartas (mesmo curso primeiro)
            const outros = produtos.filter(x => x.id !== p.id)
                .sort((a, b) => (b.curso === p.curso) - (a.curso === p.curso))
                .slice(0, 3);
            const rel = document.querySelector('[data-products-relacionados]');
            if (rel && outros.length) {
                rel.innerHTML = outros.map(x => cardProdutoHtml(x, cursoDoProduto(cursos, x.curso))).join('');
                document.getElementById('relacionados').hidden = false;
            }
        })
        .catch(() => sessaoPromise.then(() => {
            alvo.innerHTML = `<p class="products-loading">Não foi possível carregar a carta. ${semBack() ? AVISO_SEM_PHP : 'Tente novamente mais tarde.'}</p>`;
        }));
}

// Servidor recusou a reserva por falta de estoque (back/compras/finalizar.php?erro=estoque)
function initErroEstoque() {
    const container = document.getElementById('cart-page-container');
    const params = new URLSearchParams(window.location.search);
    if (!container || params.get('erro') !== 'estoque') return;

    let falta = [];
    try { falta = JSON.parse(params.get('falta') || '[]'); } catch (e) { /* mostra aviso genérico */ }
    const aviso = document.createElement('p');
    aviso.className = 'form-alert form-alert--error';
    aviso.setAttribute('role', 'alert');
    aviso.innerHTML = 'Não deu pra reservar: ' + (falta.length
        ? falta.map(([nome, restam]) => `<strong>${escapeHtml(nome)}</strong> ${restam > 0 ? `(só restam ${Number(restam)})` : '(esgotada)'}`).join(', ')
        : 'falta estoque de algum item') + '. Ajustamos o carrinho, confira e tente de novo.';
    container.before(aviso);
}

// Carrinho guardado no navegador pode ter preço/nome antigo: alinha com o banco
// e tira o que foi excluído do catálogo (o back/compras/finalizar.php já cobra o preço do banco).
function sincronizarCarrinho() {
    if (!document.getElementById('cart-page-container') || getCart().length === 0) return;
    carregarCatalogo()
        .then(({ cursos, produtos }) => {
            const porId = {};
            produtos.forEach(p => { porId[String(p.id)] = p; });
            const antes = JSON.stringify(getCart());
            const removidos = [];
            const ajustados = [];
            const cart = getCart().filter(item => {
                const p = porId[String(item.id)];
                if (!p) { removidos.push(item.name); return false; }
                if (p.estoque <= 0) { removidos.push(`${item.name} (esgotada)`); return false; }
                item.stock = p.estoque;
                if (item.qty > p.estoque) { ajustados.push(`${item.name}: ${p.estoque}`); item.qty = p.estoque; }
                const curso = cursoDoProduto(cursos, p.curso);
                item.name = p.nome;
                item.price = p.valor;
                item.img = p.imagem;
                item.course = curso.nome;
                item.courseClass = curso.classe;
                item.courseStyle = curso.estilo;
                return true;
            });
            if (JSON.stringify(cart) === antes) return;
            saveCart(cart);
            renderCartPage();
            if (removidos.length) showToast(`Removida do carrinho: ${removidos.join(', ')}`);
            if (ajustados.length) showToast(`Quantidade ajustada ao estoque: ${ajustados.join(', ')}`);
        })
        .catch(() => { /* sem back: mantém o carrinho como está */ });
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
    const avisoTel = document.getElementById('cad-telefone-erro');
    if (avisoTel && params.get('erro') === 'telefone') avisoTel.hidden = false;

    mascaraTelefone(document.getElementById('cad-telefone'));
}

// Máscara (14) 99999-9999 (cadastro e perfil)
function mascaraTelefone(tel) {
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

        contas.forEach(a => {
            a.href = sessao.admin ? (BACK_BASE + 'painel/estoque.php') : (FRONT_BASE + 'perfil.html');
            a.setAttribute('aria-label', sessao.admin ? 'Painel administrativo' : 'Meu perfil');
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

// ---- Contato / Newsletter: gravam no banco (back/contato/) ----
function enviarFormSimples(form, url, msgOk) {
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        if (semBack()) {
            mostrarAvisoSemBack(form);
            return;
        }
        const botao = form.querySelector('[type="submit"]');
        if (botao) botao.disabled = true;
        fetch(BACK_BASE + url, {
            method: 'POST',
            credentials: 'same-origin',
            body: new URLSearchParams(new FormData(form))
        })
            .then(r => r.json().catch(() => ({})).then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                if (!ok) {
                    alert(data.erro || 'Não foi possível enviar agora. Tente novamente mais tarde.');
                    return;
                }
                showToast(msgOk);
                form.reset();
            })
            .catch(() => alert('Não foi possível enviar agora. Tente novamente mais tarde.'))
            .finally(() => { if (botao) botao.disabled = false; });
    });
}

function initSimpleForms() {
    const contato = document.getElementById('contatoForm');
    if (contato) enviarFormSimples(contato, 'contato/enviar.php', 'Mensagem enviada! Responderemos em breve.');

    const newsletter = document.getElementById('newsletterForm');
    if (newsletter) enviarFormSimples(newsletter, 'contato/newsletter.php', 'Inscrição feita! Você vai receber os lançamentos.');
}

document.addEventListener('DOMContentLoaded', () => {
    initNav();
    initCarousel();
    Promise.all([initCatalogo(), initProdutoDetalhe()]).then(() => {
        initProductFilters();
        initProductSteppers();
    });
    initLogin();
    initPasswordToggles();
    initCadastro();
    initSimpleForms();
    initSessao();
    initGuardaBack();
    initTema();
    updateCartBadge();
    initErroEstoque();
    renderCartPage();
    sincronizarCarrinho();
    renderPerfilPage();
    initEncomendaOk();

    // carrinho alterado em outra aba: atualiza contador, cards e página do carrinho
    window.addEventListener('storage', (e) => {
        if (e.key !== CART_KEY) return;
        updateCartBadge();
        syncProductSteppers();
        renderCartPage();
    });
});