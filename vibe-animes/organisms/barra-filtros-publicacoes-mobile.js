/**
 * Organism: Barra de Filtros Mobile de Publicações (barra-filtros-publicacoes-mobile)
 * JS complementar para interações de filtros específicos de publicações.
 * Nota: Abertura/fechamento do modal é gerido por barra-filtros-mobile.js via [data-filtros-toggle].
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		const triggerBtn = document.querySelector('[data-filtros-toggle="mobile-filters-panel"]');
		const panel = document.getElementById('mobile-filters-panel');
		const overlay = document.getElementById('mobile-filters-panel-overlay');
		const closeBtns = document.querySelectorAll('.barra-filtros-mobile__panel-close, .barra-filtros-mobile__overlay');

		if (triggerBtn && panel && overlay) {
			// Função para fechar
			function closePanel() {
				panel.classList.remove('is-open');
				overlay.classList.remove('is-open');
				panel.setAttribute('aria-hidden', 'true');
				overlay.setAttribute('aria-hidden', 'true');
				triggerBtn.setAttribute('aria-expanded', 'false');
				document.body.style.overflow = '';
			}

			// Função para abrir
			function openPanel() {
				panel.classList.add('is-open');
				overlay.classList.add('is-open');
				panel.setAttribute('aria-hidden', 'false');
				overlay.setAttribute('aria-hidden', 'false');
				triggerBtn.setAttribute('aria-expanded', 'true');
				document.body.style.overflow = 'hidden';
			}

			// Toggle clique
			triggerBtn.addEventListener('click', function () {
				if (panel.classList.contains('is-open')) {
					closePanel();
				} else {
					openPanel();
				}
			});

			// Fechar ao clicar no escuro ou no X
			closeBtns.forEach(btn => btn.addEventListener('click', closePanel));

			// Fechar com ESC
			document.addEventListener('keydown', function (e) {
				if (e.key === 'Escape' && panel.classList.contains('is-open')) {
					closePanel();
				}
			});
		}

		// Para publicações, garantimos que os `select` também sejam zerados caso o usuário clique em Limpar.
		const resetBtn = document.querySelector('.barra-filtros-mobile__reset');
		if (resetBtn) {
			resetBtn.addEventListener('click', function (e) {
				const form = document.getElementById('form-filtros-pub-mobile');
				if (form) {
					const selects = form.querySelectorAll('select');
					selects.forEach(select => {
						select.value = '';
					});
				}
			});
		}

		// Remover filtro ativo via badge (X)
		const ativosContainer = document.getElementById('mobile-filtros-ativos');
		if (ativosContainer) {
			ativosContainer.addEventListener('click', function (e) {
				const removeBtn = e.target.closest('[data-remove-select]');
				if (!removeBtn) return;

				const selectName = removeBtn.getAttribute('data-remove-select');
				const form = document.getElementById('form-filtros-pub-mobile');
				if (form) {
					const select = form.querySelector(`select[name="${selectName}"]`);
					if (select) {
						// Se for ordem, talvez o padrão seja 'recentes', senão vazio
						select.value = (selectName === 'ordem') ? 'recentes' : '';
					}
					
					// Remove o badge visualmente antes do refresh para feedback imediato
					removeBtn.closest('.barra-filtros-mobile__ativo-chip')?.remove();
					
					// Re-submete a busca automaticamente sem esse filtro
					form.submit();
				}
			});
		}

	});

})();
