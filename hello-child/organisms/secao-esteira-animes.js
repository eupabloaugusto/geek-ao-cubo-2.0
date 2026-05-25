/**
 * Organism: Seção de Esteira de Animes (secao-esteira-animes) JS
 *
 * Lógica de scroll infinito via clonagem de slides:
 * - Clona todos os cards antes e depois do set original
 * - Inicializa o scroll no início do set original
 * - Ao cruzar os limites, teleporta para a posição equivalente no set real (invisível)
 * - Suporta múltiplas esteiras independentes na mesma página
 * - Drag-to-scroll premium no desktop
 */

document.addEventListener('DOMContentLoaded', () => {
    const esteiras = document.querySelectorAll('.js-esteira-container');

    esteiras.forEach((esteira) => {
        const track = esteira.querySelector('.js-esteira-track');
        const prevArrow = esteira.querySelector('.js-esteira-prev');
        const nextArrow = esteira.querySelector('.js-esteira-next');

        if (!track) return;

        const realSlides = Array.from(track.querySelectorAll('.js-esteira-slide'));
        if (realSlides.length === 0) return;

        // ── 1. INFINITE LOOP: Clonagem dos slides ────────────────────────────
        // Cria um clone completo do set para prepend (antes) e append (depois)
        const makeClones = () => realSlides.map((slide) => {
            const clone = slide.cloneNode(true);
            clone.setAttribute('aria-hidden', 'true');
            clone.classList.add('js-esteira-clone');
            return clone;
        });

        // Prepend: insere cópia do set inteiro antes do primeiro slide real
        makeClones().forEach((clone) => track.insertBefore(clone, realSlides[0]));

        // Append: insere cópia do set inteiro após o último slide real
        makeClones().forEach((clone) => track.appendChild(clone));

        // ── 2. POSIÇÃO INICIAL ───────────────────────────────────────────────
        // Forçar layout para que offsetLeft reflita os clones prepended
        // offsetLeft do primeiro slide real = largura total de um set completo (singleSetWidth)
        track.style.scrollBehavior = 'auto';
        track.scrollLeft = realSlides[0].offsetLeft;

        // Getter dinâmico: recalcula a cada acesso para suportar resize
        const getSingleSetWidth = () => realSlides[0].offsetLeft;

        // Reativa scroll suave após posicionamento inicial
        requestAnimationFrame(() => {
            track.style.scrollBehavior = 'smooth';
        });

        // ── 3. TELEPORTE INVISÍVEL (scroll infinito) ─────────────────────────
        let isTeleporting = false;

        const handleInfiniteScroll = () => {
            if (isTeleporting) return;

            const sl   = track.scrollLeft;
            const ssw  = getSingleSetWidth();

            if (sl < ssw) {
                // Entrou na zona de clones-antes → teleporta para posição equivalente no set real
                isTeleporting = true;
                track.style.scrollBehavior = 'auto';
                track.style.scrollSnapType = 'none';
                track.scrollLeft = sl + ssw;
                setTimeout(() => {
                    track.style.scrollBehavior = 'smooth';
                    track.style.scrollSnapType = 'x mandatory';
                    isTeleporting = false;
                }, 0);

            } else if (sl >= ssw * 2) {
                // Entrou na zona de clones-depois → teleporta para posição equivalente no set real
                isTeleporting = true;
                track.style.scrollBehavior = 'auto';
                track.style.scrollSnapType = 'none';
                track.scrollLeft = sl - ssw;
                setTimeout(() => {
                    track.style.scrollBehavior = 'smooth';
                    track.style.scrollSnapType = 'x mandatory';
                    isTeleporting = false;
                }, 0);
            }
        };

        track.addEventListener('scroll', handleInfiniteScroll, { passive: true });

        // ── 4. SETAS DE NAVEGAÇÃO ────────────────────────────────────────────
        const getScrollStep = () => track.clientWidth * 0.75;

        if (prevArrow) {
            prevArrow.addEventListener('click', () => {
                track.scrollBy({ left: -getScrollStep(), behavior: 'smooth' });
            });
        }

        if (nextArrow) {
            nextArrow.addEventListener('click', () => {
                track.scrollBy({ left: getScrollStep(), behavior: 'smooth' });
            });
        }

        // ── 5. DRAG-TO-SCROLL (Desktop) ──────────────────────────────────────
        let isDown      = false;
        let isDragging  = false;
        let startX;
        let scrollLeftVal;
        const dragThreshold = 8; // px

        track.addEventListener('mousedown', (e) => {
            if (e.button !== 0) return;
            isDown       = true;
            isDragging   = false;
            track.style.scrollSnapType  = 'none';
            track.style.scrollBehavior  = 'auto';
            startX       = e.pageX - track.offsetLeft;
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
