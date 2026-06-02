/**
 * Molecule: Trilho Infinito (trilho-infinito) JS
 *
 * Comportamento compartilhado para todos os trilhos horizontais com scroll infinito.
 * Inicializa em qualquer elemento .js-trilho na página.
 *
 * Otimizações de Engenharia (Tech Lead Design):
 * - Evita Layout Thrashing: Removemos a destruição/reconstrução inline de 'scroll-snap-type' e 'scroll-behavior' no evento scroll.
 * - Separação de Comportamento: O comportamento nativo é 'scroll-behavior: auto' (instantâneo para teleportes e drag),
 *   e a navegação via setas usa chamadas explícitas com '{ behavior: "smooth" }'.
 * - Mapeamento Geométrico Exato: Coleta o 'gap' real do CSS Flexbox via ComputedStyle para zerar qualquer drift (desvio) sub-pixel no teleporte.
 * - Proteção de Ações: Trava cliques acidentais em links durante o arrasto no desktop.
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

        // Mede a largura real de uma repetição completa do conjunto de slides,
        // incluindo o gap flexbox dinâmico para eliminar qualquer drift milimétrico.
        const computeRealSetWidth = () => {
            const first = realSlides[0];
            const last = realSlides[realSlides.length - 1];
            if (!first || !last) return 0;

            // Mede em coordenadas de alta precisão (sub-pixel) para evitar arredondamento
            const firstRect = first.getBoundingClientRect();
            const lastRect = last.getBoundingClientRect();

            const left = firstRect.left;
            const right = lastRect.right;
            
            // Captura o gap dinâmico em ponto flutuante
            const style = window.getComputedStyle(track);
            const gap = parseFloat(style.columnGap || style.gap) || 0;

            const totalWidth = right - left + gap;
            return totalWidth > 0 ? totalWidth : 0;
        };

        // ── 1. INFINITE LOOP: Clonagem dos slides ─────────────────────────────
        const makeClones = () => realSlides.map((slide) => {
            const clone = slide.cloneNode(true);
            clone.setAttribute('aria-hidden', 'true');
            clone.classList.add('js-trilho__clone');
            return clone;
        });

        // Insere clones antes (Prepend) e depois (Append) do bloco real
        makeClones().forEach((clone) => track.insertBefore(clone, realSlides[0]));
        makeClones().forEach((clone) => track.appendChild(clone));

        // ── 2. POSIÇÃO INICIAL E MEDIÇÃO ──────────────────────────────────────
        let setWidth = 0;
        const refreshSetWidth = () => {
            const w = computeRealSetWidth();
            if (w > 0) setWidth = w;
        };

        // Posiciona a esteira no conjunto central (slides originais) após a renderização
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                refreshSetWidth();
                if (!setWidth) return;
                track.scrollLeft = setWidth;
            });
        });

        window.addEventListener('resize', () => {
            refreshSetWidth();
            if (!setWidth) return;
            const sl = track.scrollLeft;
            // Se o resize empurrar o usuário para fora do miolo, reposiciona instantaneamente
            if (sl < setWidth * 0.25) {
                track.scrollLeft = sl + setWidth;
            } else if (sl > setWidth * 1.75) {
                track.scrollLeft = sl - setWidth;
            }
        }, { passive: true });

        window.addEventListener('load', () => {
            refreshSetWidth();
        }, { once: true });

        // ── 3. TELEPORTE INVISÍVEL INSTANTÂNEO (Com Gestão de Touch/Snap) ──────
        let isTeleporting = false;
        let rafQueued = false;
        let isTouching = false;

        // Monitora estados de touch no mobile para pausar teleporte sob o dedo do usuário
        track.addEventListener('touchstart', () => {
            isTouching = true;
        }, { passive: true });

        track.addEventListener('touchend', () => {
            isTouching = false;
            handleInfiniteScroll(); // Valida posição após soltar o dedo
        }, { passive: true });

        track.addEventListener('touchcancel', () => {
            isTouching = false;
            handleInfiniteScroll();
        }, { passive: true });

        const handleInfiniteScroll = () => {
            const sl = track.scrollLeft;
            if (!setWidth) {
                refreshSetWidth();
            }
            const ssw = setWidth;
            if (!ssw) return;

            // Se estiver ativamente arrastando com o dedo (isTouching) ou travado em teleporte, aborta
            if (isTeleporting || rafQueued || isTouching) return;
            rafQueued = true;

            requestAnimationFrame(() => {
                rafQueued = false;
                if (isTeleporting || isTouching) return;

                const currentSl = track.scrollLeft;
                const leftThreshold  = ssw * 0.25;
                const rightThreshold = ssw * 1.75;

                if (currentSl <= leftThreshold || currentSl >= rightThreshold) {
                    isTeleporting = true;

                    // Desabilita snap temporariamente para interromper a luta do compositor
                    track.style.scrollBehavior = 'auto';
                    track.style.scrollSnapType = 'none';

                    // Força reflow síncrono para garantir que os estilos acima foram aplicados antes do teleporte
                    track.offsetHeight;

                    // Executa o pulo instantâneo perfeitamente alinhado por ssw
                    const targetSl = currentSl <= leftThreshold ? currentSl + ssw : currentSl - ssw;
                    track.scrollLeft = targetSl;

                    // Restaura no próximo ciclo de pintura (frame seguinte)
                    requestAnimationFrame(() => {
                        track.style.scrollBehavior = '';
                        track.style.scrollSnapType = '';

                        // Força novo reflow para restabelecer os snaps na nova coordenada
                        track.offsetHeight;

                        // Mantém a trava ativa por um intervalo mínimo para filtrar eventos fantasmas
                        setTimeout(() => {
                            isTeleporting = false;
                        }, 80);
                    });
                }
            });
        };

        track.addEventListener('scroll', handleInfiniteScroll, { passive: true });

        // ── 4. NAVEGAÇÃO POR SETAS (Comportamento Suave) ─────────────────────
        const getScrollStep = () => {
            const firstCard = realSlides[0];
            if (!firstCard) return track.clientWidth * 0.75; // Fallback

            const cardWidth = firstCard.offsetWidth;
            const style = window.getComputedStyle(track);
            const gap = parseFloat(style.columnGap || style.gap) || 0;

            // Deslocamento correspondente a 1 card e meio + os respectivos gaps
            return Math.round(1.5 * (cardWidth + gap));
        };

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

        // ── 5. DRAG-TO-SCROLL PREMIUM (Desktop Mouse) ───────────────────────
        let isDown     = false;
        let isDragging = false;
        let startX;
        let scrollLeftVal;
        const dragThreshold = 8;

        track.addEventListener('mousedown', (e) => {
            if (e.button !== 0) return; // Apenas clique esquerdo
            isDown     = true;
            isDragging = false;
            
            // Desabilita snap temporariamente para suavizar o arrasto manual
            track.style.scrollSnapType = 'none';
            startX        = e.pageX - track.offsetLeft;
            scrollLeftVal = track.scrollLeft;
        });

        track.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            const x = e.pageX - track.offsetLeft;
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

            // Reativa o scroll-snap para alinhar o slide final
            track.style.scrollSnapType = 'x mandatory';
        };

        track.addEventListener('mouseleave', stopDragging);
        track.addEventListener('mouseup', stopDragging);

        // Previne cliques acidentais em cards ou links se o usuário estava no meio de um arrasto
        track.addEventListener('click', (e) => {
            if (isDragging) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);
    });
});
