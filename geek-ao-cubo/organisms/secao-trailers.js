/**
 * Organism: Seção de Trailers (secao-trailers) JS
 *
 * Slider scroll-snap de trailers e PVs sem autoplay.
 * Suporta múltiplas instâncias independentes na mesma página.
 * Integra gestos swipe (mobile) e navegação por teclado (WCAG).
 */

document.addEventListener('DOMContentLoaded', () => {
    const containers = document.querySelectorAll('.js-trailers-container');

    containers.forEach((container) => {
        const track    = container.querySelector('.js-trailers-track');
        const prevBtn  = container.querySelector('.js-trailers-prev');
        const nextBtn  = container.querySelector('.js-trailers-next');
        const labels   = container.querySelectorAll('.secao-trailers__label');
        const slides   = container.querySelectorAll('.js-trailers-slide');

        if (!track || slides.length === 0) return;

        let activeIndex  = 0;
        const totalSlides = slides.length;

        // 1. Atualiza o label ativo
        const updateLabels = (newIndex) => {
            activeIndex = newIndex;
            labels.forEach((label, idx) => {
                const isActive = idx === activeIndex;
                label.classList.toggle('is-active', isActive);
                label.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
        };

        // 2. Rola suavemente para o slide alvo
        const scrollToSlide = (index) => {
            if (index < 0 || index >= totalSlides) return;
            const slideWidth = track.clientWidth;
            track.scrollTo({ left: index * slideWidth, behavior: 'smooth' });
            updateLabels(index);
        };

        // 3. Seta anterior
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                const nextIdx = (activeIndex - 1 + totalSlides) % totalSlides;
                scrollToSlide(nextIdx);
            });
        }

        // 4. Seta próxima
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                const nextIdx = (activeIndex + 1) % totalSlides;
                scrollToSlide(nextIdx);
            });
        }

        // 5. Clique nos labels (pills de texto)
        labels.forEach((label) => {
            label.addEventListener('click', () => {
                const targetIdx = parseInt(label.getAttribute('data-slide'), 10);
                scrollToSlide(targetIdx);
            });
        });

        // 6. Scroll-spy: sincroniza label ativo após swipe/scroll manual
        let scrollTimeout;
        track.addEventListener('scroll', () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                const slideWidth = track.clientWidth;
                if (slideWidth === 0) return;
                const computedIndex = Math.round(track.scrollLeft / slideWidth);
                if (computedIndex !== activeIndex && computedIndex >= 0 && computedIndex < totalSlides) {
                    updateLabels(computedIndex);
                }
            }, 100);
        });

        // 7. Navegação por teclado nas setas (acessibilidade WCAG)
        container.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                const nextIdx = (activeIndex - 1 + totalSlides) % totalSlides;
                scrollToSlide(nextIdx);
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                const nextIdx = (activeIndex + 1) % totalSlides;
                scrollToSlide(nextIdx);
            }
        });
    });
});
