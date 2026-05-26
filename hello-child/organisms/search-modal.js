/**
 * Organism: Search Modal JS
 *
 * Gerencia a abertura, fechamento e acessibilidade (foco e acessibilidade por teclado)
 * do Modal de Busca.
 */

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('search-modal');
    if (!modal) return;

    // Busca o input real de pesquisa (genérico com a classe .input-field)
    const searchInput = modal.querySelector('.input-field');
    let previouslyFocusedElement = null;

    /**
     * Abre o Modal de Busca
     */
    function openSearchModal(e) {
        if (e) e.preventDefault();
        
        previouslyFocusedElement = document.activeElement;

        // Fecha o drawer de navegação se ele estiver aberto
        const drawer = document.getElementById('nav-drawer');
        if (drawer && drawer.classList.contains('navigation-drawer--open')) {
            const drawerCloseBtn = document.getElementById('nav-drawer-close-btn');
            if (drawerCloseBtn) {
                drawerCloseBtn.click();
            }
        }
        
        modal.classList.add('search-modal--open');
        modal.setAttribute('aria-hidden', 'false');
        
        // Impede scroll do body
        document.body.style.overflow = 'hidden';

        // Atualiza atributos aria nos botões de gatilho de abertura presentes na tela
        document.querySelectorAll('.js-open-search-modal').forEach(btn => {
            btn.setAttribute('aria-expanded', 'true');
        });

        // Foca automaticamente no input de pesquisa
        if (searchInput) {
            setTimeout(() => {
                searchInput.focus();
            }, 100);
        }
    }

    /**
     * Fecha o Modal de Busca
     */
    function closeSearchModal(e) {
        if (e) e.preventDefault();

        modal.classList.remove('search-modal--open');
        modal.setAttribute('aria-hidden', 'true');
        
        // Restaura scroll do body
        document.body.style.overflow = '';

        // Atualiza atributos aria nos botões de gatilho
        document.querySelectorAll('.js-open-search-modal').forEach(btn => {
            btn.setAttribute('aria-expanded', 'false');
        });

        // Retorna o foco para o elemento original
        if (previouslyFocusedElement && typeof previouslyFocusedElement.focus === 'function') {
            previouslyFocusedElement.focus();
        }
    }

    // Delegação de Evento global para abertura do modal (robusto para carregamento tardio do Elementor)
    document.addEventListener('click', (e) => {
        const openBtn = e.target.closest('.js-open-search-modal');
        if (openBtn) {
            openSearchModal(e);
        }
    });

    // Delegação de Evento dentro do modal para fechar (botão X e overlay de fundo)
    document.addEventListener('click', (e) => {
        const closeBtn = e.target.closest('.js-close-search-modal');
        if (closeBtn) {
            closeSearchModal(e);
        }
    });

    // Fechar ao pressionar a tecla Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('search-modal--open')) {
            closeSearchModal(e);
        }
    });

    // Trava de foco (Acessibilidade WCAG)
    modal.addEventListener('keydown', (e) => {
        if (e.key === 'Tab') {
            const focusableElements = modal.querySelectorAll('input, button, a');
            if (focusableElements.length === 0) return;
            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];

            if (e.shiftKey) { // Shift + Tab
                if (document.activeElement === firstElement) {
                    lastElement.focus();
                    e.preventDefault();
                }
            } else { // Tab
                if (document.activeElement === lastElement) {
                    firstElement.focus();
                    e.preventDefault();
                }
            }
        }
    });
});
