/**
 * Organism: Seção de Carrossel de Destaques (secao-carrossel-destaque) JS
 *
 * Lógica vanilla JS reativa de alta performance.
 * Suporta múltiplos carrosséis independentes na mesma página.
 * Integra gestos swipe de celulares e cliques do desktop com sincronismo perfeito.
 */

document.addEventListener('DOMContentLoaded', () => {
    const carousels = document.querySelectorAll('.js-carousel-container');

    carousels.forEach((carousel) => {
        const track = carousel.querySelector('.js-carousel-track');
        const prevArrow = carousel.querySelector('.js-carousel-prev');
        const nextArrow = carousel.querySelector('.js-carousel-next');
        const dots = carousel.querySelectorAll('.carousel-dot');
        const slides = carousel.querySelectorAll('.js-carousel-slide');

        if (!track || slides.length === 0) return;

        let activeIndex = 0;
        let autoplayTimer = null;
        const totalSlides = slides.length;
        const AUTOPLAY_INTERVAL = 5000; // Rotaciona a cada 5 segundos

        // 1. Função de Atualização dos Dots Indicadores
        const updateDots = (newActiveIndex) => {
            activeIndex = newActiveIndex;
            dots.forEach((dot, idx) => {
                if (idx === activeIndex) {
                    dot.classList.add('is-active');
                    dot.setAttribute('aria-current', 'true');
                } else {
                    dot.classList.remove('is-active');
                    dot.setAttribute('aria-current', 'false');
                }
            });
        };

        // 2. Rolagem Suave para Slide Específico
        const scrollToSlide = (index) => {
            if (index < 0 || index >= totalSlides) return;
            const slideWidth = track.clientWidth;
            track.scrollTo({
                left: index * slideWidth,
                behavior: 'smooth'
            });
            updateDots(index);
        };

        // 3. Clique nas Setas (Navegação)
        if (prevArrow) {
            prevArrow.addEventListener('click', () => {
                let nextIdx = activeIndex - 1;
                if (nextIdx < 0) nextIdx = totalSlides - 1; // Rotaciona para o último slide
                scrollToSlide(nextIdx);
                resetAutoplay();
            });
        }

        if (nextArrow) {
            nextArrow.addEventListener('click', () => {
                let nextIdx = activeIndex + 1;
                if (nextIdx >= totalSlides) nextIdx = 0; // Rotaciona para o primeiro slide
                scrollToSlide(nextIdx);
                resetAutoplay();
            });
        }

        // 4. Clique nas Bolinhas Indicadoras (Dots)
        dots.forEach((dot) => {
            dot.addEventListener('click', () => {
                const targetIdx = parseInt(dot.getAttribute('data-slide'), 10);
                scrollToSlide(targetIdx);
                resetAutoplay();
            });
        });

        // 5. Scroll-Spy Avançado de Alto Desempenho (Sincronismo para Swipe Mobile)
        let scrollTimeout;
        track.addEventListener('scroll', () => {
            // Debounce sutil para evitar processamento excessivo por pixel de rolagem
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                const slideWidth = track.clientWidth;
                if (slideWidth === 0) return;
                
                // Calcula o slide mais próximo com base na posição da rolagem horizontal
                const currentScroll = track.scrollLeft;
                const computedIndex = Math.round(currentScroll / slideWidth);
                
                if (computedIndex !== activeIndex && computedIndex >= 0 && computedIndex < totalSlides) {
                    updateDots(computedIndex);
                }
            }, 100);
        });

        // 6. Temporizador Inteligente (Autoplay)
        const startAutoplay = () => {
            if (autoplayTimer) return;
            autoplayTimer = setInterval(() => {
                let nextIdx = activeIndex + 1;
                if (nextIdx >= totalSlides) nextIdx = 0;
                scrollToSlide(nextIdx);
            }, AUTOPLAY_INTERVAL);
        };

        const stopAutoplay = () => {
            if (autoplayTimer) {
                clearInterval(autoplayTimer);
                autoplayTimer = null;
            }
        };

        const resetAutoplay = () => {
            stopAutoplay();
            startAutoplay();
        };

        // Inicia o Autoplay
        startAutoplay();

        // 7. Pausa de Acessibilidade no Hover e Foco de Teclado (WCAG)
        carousel.addEventListener('mouseenter', stopAutoplay);
        carousel.addEventListener('mouseleave', startAutoplay);
        
        // Pausa quando o usuário navega com o Tab do teclado por razões de acessibilidade
        carousel.addEventListener('focusin', stopAutoplay);
        carousel.addEventListener('focusout', startAutoplay);
    });
});
