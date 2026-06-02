/**
 * Molecule: Navegação Alfabética JS (nav-alfabetica)
 *
 * Scroll automático até a letra ativa ao carregar a página no mobile.
 * Garante que a letra selecionada seja visível na barra horizontal.
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		const navs = document.querySelectorAll('.nav-alfabetica');

		navs.forEach(function (nav) {
			const lista  = nav.querySelector('.nav-alfabetica__lista');
			const ativo  = nav.querySelector('.nav-alfabetica__link--ativo');

			if (!lista || !ativo) return;

			// Centraliza o item ativo no scroll horizontal (mobile)
			const itemOffset  = ativo.closest('.nav-alfabetica__item')?.offsetLeft || ativo.offsetLeft;
			const itemWidth   = ativo.offsetWidth;
			const listaWidth  = lista.offsetWidth;
			const scrollLeft  = itemOffset - (listaWidth / 2) + (itemWidth / 2);

			lista.scrollTo({ left: scrollLeft, behavior: 'instant' });
		});
	});
})();
