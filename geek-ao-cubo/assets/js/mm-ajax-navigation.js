/**
 * Geek ao Cubo - Universal AJAX Navigation
 * Intercepta navegações (filtros e paginações) dentro de containers marcados,
 * buscando e trocando o conteúdo via AJAX sem recarregar a página.
 *
 * Contratos HTML:
 * .js-ajax-container    -> Container pai que delimita o escopo da navegação.
 * .js-ajax-replace      -> Elementos internos cujo conteúdo deve ser atualizado.
 * .js-ajax-link         -> Classes em tags <a> para interceptação customizada (além de .page-numbers).
 * .js-ajax-scroll-target-> (Opcional) Elemento para onde a tela rola ao trocar de página.
 */

document.addEventListener('DOMContentLoaded', () => {
    let activeFetchController = null;

    document.body.addEventListener('click', (e) => {
        const link = e.target.closest('a.js-ajax-link, .js-ajax-container a.page-numbers');
        if (!link) return;

        const container = link.closest('.js-ajax-container');
        if (!container) return;

        if (e.ctrlKey || e.metaKey || e.shiftKey) return;

        e.preventDefault();
        const url = link.href;

        if (activeFetchController) {
            activeFetchController.abort();
        }
        activeFetchController = new AbortController();

        const replaceTargets = container.querySelectorAll('.js-ajax-replace');
        replaceTargets.forEach(target => target.classList.add('is-loading'));

        window.history.pushState({ path: url }, '', url);

        fetch(url, { signal: activeFetchController.signal })
            .then(response => {
                if (!response.ok) throw new Error('Erro na rede: ' + response.statusText);
                return response.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                let newContainer = null;
                if (container.id) {
                    newContainer = doc.getElementById(container.id);
                }
                if (!newContainer) {
                    newContainer = doc.querySelector('.js-ajax-container');
                }

                if (newContainer) {
                    const newTargets = newContainer.querySelectorAll('.js-ajax-replace');

                    if (replaceTargets.length === newTargets.length) {
                        for (let i = 0; i < replaceTargets.length; i++) {
                            replaceTargets[i].innerHTML = newTargets[i].innerHTML;
                            replaceTargets[i].classList.remove('is-loading');
                        }
                    } else {
                        window.location.href = url;
                        return;
                    }

                    if (link.classList.contains('page-numbers')) {
                        const scrollToEl = container.querySelector('.js-ajax-scroll-target') || container;
                        if (scrollToEl) {
                            scrollToEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    }
                } else {
                    window.location.href = url;
                }
            })
            .catch(error => {
                if (error.name === 'AbortError') {
                    return;
                }
                console.error('Falha no AJAX Navigation:', error);
                replaceTargets.forEach(target => target.classList.remove('is-loading'));
                window.location.href = url;
            });
    });

    window.addEventListener('popstate', () => {
        window.location.reload();
    });
});
