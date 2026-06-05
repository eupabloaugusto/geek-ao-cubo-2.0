/**
 * Organism: Navigation Drawer JS
 *
 * Gerencia a abertura/fechamento do Drawer lateral, controle de acessibilidade (ARIA)
 * e o comportamento de acordeão para os links que possuem sub-links (dropdowns).
 */

document.addEventListener('DOMContentLoaded', () => {
    const drawer = document.getElementById('nav-drawer');
    const overlay = document.getElementById('nav-drawer-overlay');
    const closeBtn = document.getElementById('nav-drawer-close-btn');

    if (!drawer || !overlay) return;

    let previouslyFocusedElement = null;

    /**
     * Abre o Drawer
     */
    function openDrawer() {
        previouslyFocusedElement = document.activeElement;

        drawer.classList.add('navigation-drawer--open');
        overlay.classList.add('drawer-overlay--active');
        drawer.setAttribute('aria-hidden', 'false');
        
        // Impede scroll do body ao abrir o menu (melhor experiência mobile)
        document.body.style.overflow = 'hidden';

        // Foca no primeiro elemento clicável ou botão de fechar para acessibilidade
        if (closeBtn) {
            setTimeout(() => {
                closeBtn.focus();
            }, 100);
            closeBtn.setAttribute('aria-expanded', 'true');
        }

        // Atualiza atributos de todos os botões de hamburger presentes na tela
        document.querySelectorAll('.js-open-drawer').forEach(btn => {
            btn.classList.add('btn-hamburger--active');
            btn.setAttribute('aria-expanded', 'true');
        });
    }

    /**
     * Fecha o Drawer
     */
    function closeDrawer() {
        drawer.classList.remove('navigation-drawer--open');
        overlay.classList.remove('drawer-overlay--active');
        drawer.setAttribute('aria-hidden', 'true');
        
        // Restaura scroll do body
        document.body.style.overflow = '';

        if (closeBtn) {
            closeBtn.setAttribute('aria-expanded', 'false');
        }

        // Se houver gatilhos de cabeçalho, remove o estado ativo (hamburger)
        document.querySelectorAll('.js-open-drawer').forEach(btn => {
            btn.classList.remove('btn-hamburger--active');
            btn.setAttribute('aria-expanded', 'false');
        });

        // Restaura o foco para o elemento original
        if (previouslyFocusedElement && typeof previouslyFocusedElement.focus === 'function') {
            previouslyFocusedElement.focus();
        }
    }

    // Delegação de Evento global para abertura do menu lateral (robusto para carregamento tardio do Elementor)
    document.addEventListener('click', (e) => {
        const openBtn = e.target.closest('.js-open-drawer');
        if (openBtn) {
            e.preventDefault();
            const isOpen = drawer.classList.contains('navigation-drawer--open');
            if (isOpen) {
                closeDrawer();
            } else {
                openDrawer();
            }
        }
    });

    // Ouvinte no botão de fechar dentro do drawer
    if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            closeDrawer();
        });
    }

    // Fechar ao clicar no overlay de fundo
    overlay.addEventListener('click', () => {
        closeDrawer();
    });

    // Fechar ao pressionar a tecla "Escape" (revisão de Acessibilidade / SEO)
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && drawer.classList.contains('navigation-drawer--open')) {
            closeDrawer();
        }
    });

    // Trava de foco (Acessibilidade WCAG AA)
    drawer.addEventListener('keydown', (e) => {
        if (e.key === 'Tab') {
            const focusableElements = drawer.querySelectorAll('button, a, input');
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

    /**
     * Acordeão de Sub-links (Dropdowns)
     */
    const dropdownLinks = drawer.querySelectorAll('.drawer-link');
    
    dropdownLinks.forEach(link => {
        // Apenas links que controlam um sub-menu (tags <button> ou links com has_dropdown)
        if (link.getAttribute('aria-expanded') !== null) {
            link.addEventListener('click', (e) => {
                // Impede navegação se for um botão simulador de dropdown
                e.preventDefault();
                
                const isCurrentlyOpen = link.classList.contains('drawer-link--open');
                
                // Toggle do estado do link atual
                if (isCurrentlyOpen) {
                    link.classList.remove('drawer-link--open');
                    link.setAttribute('aria-expanded', 'false');
                } else {
                    link.classList.add('drawer-link--open');
                    link.setAttribute('aria-expanded', 'true');
                }
            });
        }
    });
});
