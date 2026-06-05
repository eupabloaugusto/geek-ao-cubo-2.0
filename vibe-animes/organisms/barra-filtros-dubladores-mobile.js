/**
 * Organism: Barra de Filtros Mobile JS (barra-filtros-mobile)
 *
 * Gerencia o comportamento do bottom sheet de filtros:
 * - Abrir / fechar o sheet via toggle, overlay e botÃ£o fechar
 * - Atualizar o badge de filtros ativos em tempo real
 * - Limpar todos os checkboxes/radios do formulÃ¡rio
 * - RemoÃ§Ã£o individual de chips de filtros ativos
 * - Bloqueio de scroll do body quando o sheet estÃ¡ aberto
 * - Suporte a ESC para fechar e focus trap acessÃ­vel
 */

(function () {
	'use strict';

	// â”€â”€ UtilitÃ¡rios â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

	function openSheet(sheet, overlay, toggleBtn) {
		sheet.classList.add('is-open');
		overlay.classList.add('is-open');
		sheet.setAttribute('aria-hidden', 'false');
		overlay.setAttribute('aria-hidden', 'false');
		if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
		document.body.style.overflow = 'hidden';

		// Focus no primeiro elemento interativo do sheet
		const firstFocusable = sheet.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
		if (firstFocusable) firstFocusable.focus();
	}

	function closeSheet(sheet, overlay, toggleBtn) {
		sheet.classList.remove('is-open');
		overlay.classList.remove('is-open');
		sheet.setAttribute('aria-hidden', 'true');
		overlay.setAttribute('aria-hidden', 'true');
		if (toggleBtn) {
			toggleBtn.setAttribute('aria-expanded', 'false');
			toggleBtn.focus();
		}
		document.body.style.overflow = '';
	}

	/**
	 * Conta os chips selecionados no sheet (exclui busca textual).
	 * @param {HTMLFormElement} form
	 * @returns {number}
	 */
	function countActiveFilters(form) {
		const checked = form.querySelectorAll(
			'.barra-filtros-mobile__sheet input[type="checkbox"]:checked, .barra-filtros-mobile__sheet input[type="radio"]:checked'
		);
		return checked.length;
	}

	/**
	 * Atualiza o badge no btn-filtros-toggle com a contagem atual.
	 * @param {HTMLElement} toggleBtn
	 * @param {number} count
	 */
	function updateBadge(toggleBtn, count) {
		let badge = toggleBtn.querySelector('.btn-filtros-toggle__badge');

		if (count > 0) {
			if (!badge) {
				badge = document.createElement('span');
				badge.className = 'btn-filtros-toggle__badge';
				badge.setAttribute('aria-hidden', 'true');
				toggleBtn.appendChild(badge);
			}
			badge.textContent = count;
			toggleBtn.classList.add('btn-filtros-toggle--ativo');
			toggleBtn.setAttribute('aria-label',
				toggleBtn.dataset.filtrosToggle
					? `Abrir filtros â€” ${count} ativos`
					: `Filtros â€” ${count} ativos`
			);
		} else {
			if (badge) badge.remove();
			toggleBtn.classList.remove('btn-filtros-toggle--ativo');
			toggleBtn.setAttribute('aria-label', 'Abrir filtros');
		}
	}

	// â”€â”€ InicializaÃ§Ã£o â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

	document.addEventListener('DOMContentLoaded', function () {
		const forms = document.querySelectorAll('.barra-filtros-mobile__form');

		forms.forEach(function (form) {
			const barraWrapper = form.closest('.barra-filtros-mobile');
			if (!barraWrapper) return;

			const toggleBtn   = form.querySelector('[data-filtros-toggle]');
			if (!toggleBtn) return;

			const sheetId = toggleBtn.dataset.filtrosToggle;
			const sheet   = document.getElementById(sheetId);
			const overlay = document.getElementById(sheetId + '-overlay');
			if (!sheet || !overlay) return;

			// â”€â”€ Abrir sheet â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
			toggleBtn.addEventListener('click', function () {
				const isOpen = sheet.classList.contains('is-open');
				if (isOpen) {
					closeSheet(sheet, overlay, toggleBtn);
				} else {
					openSheet(sheet, overlay, toggleBtn);
				}
			});

			// â”€â”€ Fechar via overlay â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
			overlay.addEventListener('click', function () {
				closeSheet(sheet, overlay, toggleBtn);
			});

			// â”€â”€ Fechar via botÃ£o Ã— â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
			sheet.querySelectorAll('[data-filtros-close]').forEach(function (btn) {
				btn.addEventListener('click', function () {
					closeSheet(sheet, overlay, toggleBtn);
				});
			});

			// â”€â”€ Fechar via ESC â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
			document.addEventListener('keydown', function (e) {
				if (e.key === 'Escape' && sheet.classList.contains('is-open')) {
					closeSheet(sheet, overlay, toggleBtn);
				}
			});

			// â”€â”€ Limpar todos os filtros â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
			const btnLimpar = form.querySelector('.barra-filtros-mobile__btn-limpar');
			if (btnLimpar) {
				btnLimpar.addEventListener('click', function () {
					// Desmarca todos os inputs do sheet
					sheet.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach(function (input) {
						input.checked = false;
						// Remove classe --ativo do chip pai
						const chip = input.closest('.filtro-chip');
						if (chip) chip.classList.remove('filtro-chip--ativo');
					});
					// Limpa busca
					const searchInput = form.querySelector('.barra-filtros-mobile__search-input');
					if (searchInput) searchInput.value = '';

					updateBadge(toggleBtn, 0);
				});
			}

			// â”€â”€ Atualizar badge ao mudar chips â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
			sheet.addEventListener('change', function (e) {
				if (e.target.matches('.filtro-chip__input')) {
					const count = countActiveFilters(form);
					updateBadge(toggleBtn, count);
				}
			});

			// â”€â”€ RemoÃ§Ã£o de chip ativo na barra â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
			const ativosContainer = barraWrapper.querySelector('.barra-filtros-mobile__ativos');
			if (ativosContainer) {
				ativosContainer.addEventListener('click', function (e) {
					const removeBtn = e.target.closest('[data-filtros-remove]');
					if (!removeBtn) return;

					const filterName  = removeBtn.dataset.filtrosRemove;
					const filterValue = removeBtn.dataset.filtrosValue;

					// Desmarca o input correspondente no sheet
					const selector = `input[name="${filterName}[]"][value="${filterValue}"], input[name="${filterName}"][value="${filterValue}"]`;
					const input = form.querySelector(selector);
					if (input) {
						input.checked = false;
						const chip = input.closest('.filtro-chip');
						if (chip) chip.classList.remove('filtro-chip--ativo');
					}

					// Remove o chip ativo da barra e re-submete se nÃ£o houver mais nenhum
					removeBtn.closest('.barra-filtros-mobile__ativo-chip')?.remove();
					updateBadge(toggleBtn, countActiveFilters(form));

					// Re-submete o formulÃ¡rio para atualizar resultados
					form.submit();
				});
			}

			// â”€â”€ Badge inicial (carregado com filtros ativos) â”€â”€â”€â”€â”€â”€â”€â”€â”€
			const initialCount = countActiveFilters(form);
			if (initialCount > 0) {
				updateBadge(toggleBtn, initialCount);
			}
		});
	});
})();

