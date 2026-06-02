/**
 * Organism JS: Seção de Informações Adicionais em Acordeão
 *
 * Adiciona interatividade para abertura e fechamento suave.
 *
 * @package geek-ao-cubo
 */

( function () {
	'use strict';

	function initAccordions() {
		const triggers = document.querySelectorAll( '.secao-info-extra-accordion .js-accordion-trigger' );

		triggers.forEach( trigger => {
			trigger.addEventListener( 'click', function () {
				const item    = this.closest( '.js-accordion-item' );
				const content = item.querySelector( '.js-accordion-content' );
				const isOpen  = item.getAttribute( 'data-state' ) === 'open';

				// Alterna estados de acessibilidade
				this.setAttribute( 'aria-expanded', isOpen ? 'false' : 'true' );
				item.setAttribute( 'data-state', isOpen ? 'closed' : 'open' );

				// Transição suave via JS nativo (utilizando a utilidade global mmUtils)
				if ( isOpen ) {
					mmUtils.slideUp( content, 250 );
				} else {
					mmUtils.slideDown( content, 250 );
				}
			} );
		} );
	}

	function init() {
		initAccordions();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

} )();
