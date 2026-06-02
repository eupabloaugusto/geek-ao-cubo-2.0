/**
 * Organism: Responsive Header JS
 *
 * Gerencia comportamentos interativos do header:
 * 1. Shrink on Scroll: adiciona .header--scrolled quando o usuário rola a página
 * 2. Integração com o Navigation Drawer para sincronizar o estado do hamburger
 */

document.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('.js-header');
    if (!header) return;

    // ============================
    // 1. SHRINK ON SCROLL (com throttle para performance 60fps)
    // ============================
    let ticking = false;
    const SCROLL_THRESHOLD = 20; // px antes de ativar o encolhimento

    function onScroll() {
        if (!ticking) {
            window.requestAnimationFrame(() => {
                if (window.scrollY > SCROLL_THRESHOLD) {
                    header.classList.add('header--scrolled');
                } else {
                    header.classList.remove('header--scrolled');
                }
                ticking = false;
            });
            ticking = true;
        }
    }

    // Verifica o estado inicial (caso a página seja recarregada com scroll)
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    // ============================
    // 2. INTEGRAÇÃO COM O NAVIGATION DRAWER
    // O hamburger já é capturado como .js-open-drawer pelo navigation-drawer.js.
    // Aqui apenas garantimos que, ao abrir o drawer, o hamburger do header
    // entre no estado ativo visualmente.
    // ============================
    const hamburger = document.getElementById('header-hamburger-trigger');
    const drawer    = document.getElementById('nav-drawer');

    if (hamburger && drawer) {
        // Observa mutações de classe no drawer para espelhar no hamburger do header
        const observer = new MutationObserver(() => {
            const isOpen = drawer.classList.contains('navigation-drawer--open');
            hamburger.classList.toggle('btn-hamburger--active', isOpen);
            hamburger.setAttribute('aria-expanded', String(isOpen));
            hamburger.setAttribute(
                'aria-label',
                isOpen ? 'Fechar menu de navegação' : 'Abrir menu de navegação'
            );
        });

        observer.observe(drawer, { attributes: true, attributeFilter: ['class'] });
    }
});
