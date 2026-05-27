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

        // Mede a largura do conjunto REAL (sem depender de scrollWidth/3).
        // Usamos geometria dos slides reais para evitar drift por arredondamento:
        // (último.right - primeiro.left) em coordenadas de scroll.
        const computeRealSetWidth = () => {
            const first = realSlides[0];
            const last = realSlides[realSlides.length - 1];
            if (!first || !last) return 0;
            const left = first.offsetLeft;
            const right = last.offsetLeft + last.offsetWidth;
            const w = Math.round(right - left);
            return w > 0 ? w : 0;
        };

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
        // Largura do set real (atualiza em resize/load).
        let setWidth = 0;
        const refreshSetWidth = () => {
            const w = computeRealSetWidth();
            if (w > 0) setWidth = w;
        };

        // Importante: depois de clonar, o layout pode “assentar” em frames seguintes
        // (imagens lazy, fontes, etc.). Ajustamos a posição inicial após 2 RAFs.
        track.style.scrollBehavior = 'auto';
        track.style.scrollSnapType = 'none';
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                refreshSetWidth();
                if (!setWidth) return;
                track.scrollLeft = setWidth;
                track.style.scrollSnapType = 'x mandatory';
                track.style.scrollBehavior = 'smooth';
            });
        });

        window.addEventListener('resize', () => {
            // Recalibra largura e mantém o usuário no miolo.
            refreshSetWidth();
            if (!setWidth) return;
            const sl = track.scrollLeft;
            if (sl < setWidth * 0.25) track.scrollLeft = sl + setWidth;
            else if (sl > setWidth * 1.75) track.scrollLeft = sl - setWidth;
        }, { passive: true });

        window.addEventListener('load', () => {
            // Após carregamento de imagens/fontes, mede novamente para evitar drift.
            refreshSetWidth();
        }, { once: true });

        // ── 3. TELEPORTE INVISÍVEL (loop infinito) ────────────────────────────
        let isTeleporting = false;
        let rafQueued = false;

        const handleInfiniteScroll = () => {
            if (isTeleporting || rafQueued) return;
            rafQueued = true;
            requestAnimationFrame(() => {
                rafQueued = false;
                if (isTeleporting) return;

                const sl  = track.scrollLeft;
                if (!setWidth) {
                    refreshSetWidth();
                }
                const ssw = setWidth;
                if (!ssw) return;

                // Usamos thresholds mais folgados para evitar "ping-pong" perto das bordas.
                // Track é: [clones][reais][clones]. Queremos manter o usuário no miolo.
                const leftThreshold  = ssw * 0.25;
                const rightThreshold = ssw * 1.75;

                if (sl <= leftThreshold) {
                    isTeleporting = true;
                    track.style.scrollBehavior = 'auto';
                    track.style.scrollSnapType = 'none';
                    track.scrollLeft = Math.round(sl + ssw);
                    // Double RAF: evita “trepidação” (scroll-snap brigando com scrollLeft)
                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            track.style.scrollSnapType = 'x mandatory';
                            track.style.scrollBehavior = 'smooth';
                            isTeleporting = false;
                        });
                    });
                    return;
                }

                if (sl >= rightThreshold) {
                    isTeleporting = true;
                    track.style.scrollBehavior = 'auto';
                    track.style.scrollSnapType = 'none';
                    track.scrollLeft = Math.round(sl - ssw);
                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            track.style.scrollSnapType = 'x mandatory';
                            track.style.scrollBehavior = 'smooth';
                            isTeleporting = false;
                        });
                    });
                }
            });
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
