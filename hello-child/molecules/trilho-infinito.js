/**
 * Molecule: Trilho Infinito (trilho-infinito) JS
 *
 * Comportamento compartilhado para todos os trilhos horizontais com scroll infinito.
 * Inicializa em qualquer elemento .js-trilho na página.
 *
 * Funcionalidades:
 * - Scroll infinito via clonagem de slides (sem JS visível ao usuário)
 * - Drag-to-scroll premium no desktop (mousedown/move/up)
 * - Setas prev/next com scrollBy 75% da largura visível
 * - Suporte a múltiplos trilhos independentes na mesma página
 *
 * Classes esperadas no markup:
 *   .js-trilho          → container/wrapper da seção
 *   .js-trilho__track   → div rolável horizontal
 *   .js-trilho__slide   → cada slide individual (item do trilho)
 *   .js-trilho__prev    → botão seta anterior
 *   .js-trilho__next    → botão seta próxima
 */

document.addEventListener('DOMContentLoaded', () => {
    const trilhos = document.querySelectorAll('.js-trilho');

    trilhos.forEach((trilho) => {
        const track    = trilho.querySelector('.js-trilho__track');
        const prevBtn  = trilho.querySelector('.js-trilho__prev');
        const nextBtn  = trilho.querySelector('.js-trilho__next');

        if (!track) return;

        const realSlides = Array.from(track.querySelectorAll('.js-trilho__slide'));
        if (realSlides.length === 0) return;

        // ── 1. INFINITE LOOP: Clonagem dos slides ─────────────────────────────
        const makeClones = () => realSlides.map((slide) => {
            const clone = slide.cloneNode(true);
            clone.setAttribute('aria-hidden', 'true');
            clone.classList.add('js-trilho__clone');
            return clone;
        });

        // Prepend: cópia completa antes do primeiro slide real
        makeClones().forEach((clone) => track.insertBefore(clone, realSlides[0]));

        // Append: cópia completa após o último slide real
        makeClones().forEach((clone) => track.appendChild(clone));

        // ── 2. POSIÇÃO INICIAL ────────────────────────────────────────────────
        track.style.scrollBehavior = 'auto';
        track.scrollLeft = realSlides[0].offsetLeft;

        // Getter dinâmico: recalcula a cada acesso (suporta resize)
        const getSingleSetWidth = () => realSlides[0].offsetLeft;

        requestAnimationFrame(() => {
            track.style.scrollBehavior = 'smooth';
        });

        // ── 3. TELEPORTE INVISÍVEL (loop infinito) ────────────────────────────
        let isTeleporting = false;

        const handleInfiniteScroll = () => {
            if (isTeleporting) return;

            const sl  = track.scrollLeft;
            const ssw = getSingleSetWidth();

            if (sl < ssw) {
                isTeleporting = true;
                track.style.scrollBehavior = 'auto';
                track.style.scrollSnapType = 'none';
                track.scrollLeft = Math.round(sl + ssw);
                // Double RAF: 1º frame pinta a nova posição, 2º reativa snap sem race condition
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        track.style.scrollSnapType = 'x mandatory';
                        track.style.scrollBehavior = 'smooth';
                        isTeleporting = false;
                    });
                });

            } else if (sl >= ssw * 2) {
                isTeleporting = true;
                track.style.scrollBehavior = 'auto';
                track.style.scrollSnapType = 'none';
                track.scrollLeft = Math.round(sl - ssw);
                // Double RAF: 1º frame pinta a nova posição, 2º reativa snap sem race condition
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        track.style.scrollSnapType = 'x mandatory';
                        track.style.scrollBehavior = 'smooth';
                        isTeleporting = false;
                    });
                });
            }
        };

        track.addEventListener('scroll', handleInfiniteScroll, { passive: true });

        // ── 4. SETAS DE NAVEGAÇÃO ─────────────────────────────────────────────
        const getScrollStep = () => track.clientWidth * 0.75;

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                track.scrollBy({ left: -getScrollStep(), behavior: 'smooth' });
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                track.scrollBy({ left: getScrollStep(), behavior: 'smooth' });
            });
        }

        // ── 5. DRAG-TO-SCROLL (Desktop) ───────────────────────────────────────
        let isDown     = false;
        let isDragging = false;
        let startX;
        let scrollLeftVal;
        const dragThreshold = 8;

        track.addEventListener('mousedown', (e) => {
            if (e.button !== 0) return;
            isDown        = true;
            isDragging    = false;
            track.style.scrollSnapType = 'none';
            track.style.scrollBehavior = 'auto';
            startX        = e.pageX - track.offsetLeft;
            scrollLeftVal = track.scrollLeft;
        });

        track.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            const x    = e.pageX - track.offsetLeft;
            const walk = (x - startX) * 1.5;
            if (Math.abs(x - startX) > dragThreshold) {
                isDragging = true;
                track.classList.add('is-dragging');
            }
            track.scrollLeft = scrollLeftVal - walk;
        });

        const stopDragging = () => {
            if (!isDown) return;
            isDown = false;
            setTimeout(() => {
                track.classList.remove('is-dragging');
                isDragging = false;
            }, 80);
            track.style.scrollSnapType = 'x mandatory';
            track.style.scrollBehavior = 'smooth';
        };

        track.addEventListener('mouseleave', stopDragging);
        track.addEventListener('mouseup', stopDragging);

        // Bloqueia cliques em links/imagens se o usuário estava arrastando
        track.addEventListener('click', (e) => {
            if (isDragging) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);
    });
});
