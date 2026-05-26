/**
 * Molecule JS: Card de Review (review-card)
 *
 * Gerencia o expand/collapse in-page do texto da review.
 * Atualiza aria-expanded e o label do botão a cada toggle.
 *
 * @package hello-elementor-child
 */

( function () {
	'use strict';

	function initReviewCards() {
		var toggleButtons = document.querySelectorAll(
			'.review-card[data-expandable="true"] .review-card__toggle'
		);

		toggleButtons.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var card       = btn.closest( '.review-card' );
				var isExpanded = card.classList.contains( 'review-card--expanded' );
				var labelMore  = btn.dataset.labelMore || 'Ler mais';
				var labelLess  = btn.dataset.labelLess || 'Ler menos';
				var labelEl    = btn.querySelector( '.review-card__toggle-label' );

				if ( isExpanded ) {
					card.classList.remove( 'review-card--expanded' );
					btn.setAttribute( 'aria-expanded', 'false' );
					if ( labelEl ) {
						labelEl.textContent = labelMore;
					}
				} else {
					card.classList.add( 'review-card--expanded' );
					btn.setAttribute( 'aria-expanded', 'true' );
					if ( labelEl ) {
						labelEl.textContent = labelLess;
					}
				}
			} );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initReviewCards );
	} else {
		initReviewCards();
	}
} )();
