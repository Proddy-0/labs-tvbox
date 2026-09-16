// ==========================================================================
// LOGICDECK - SCRIPTS GERAIS DO SITE
// ==========================================================================

// ---- Carrinho ----
function addToCart(btn) {
    const counter = document.getElementById('cart-count');
    if (counter) {
        counter.textContent = parseInt(counter.textContent) + 1;
    }
}

// ---- Carrossel do Banner ----
function initCarousel() {
    const container = document.querySelector('.carousel-container');
    if (!container) return; // Página sem carrossel, não faz nada

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

    function nextSlide() {
        showSlide(currentSlide + 1);
    }

    function prevSlide() {
        showSlide(currentSlide - 1);
    }

    function resetAutoPlay() {
        clearInterval(autoPlayTimer);
        autoPlayTimer = setInterval(nextSlide, 6000);
    }

    if (btnNext) {
        btnNext.addEventListener('click', () => {
            nextSlide();
            resetAutoPlay();
        });
    }

    if (btnPrev) {
        btnPrev.addEventListener('click', () => {
            prevSlide();
            resetAutoPlay();
        });
    }

    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => {
            showSlide(i);
            resetAutoPlay();
        });
    });

    resetAutoPlay();
}

document.addEventListener('DOMContentLoaded', () => {
    initCarousel();
});