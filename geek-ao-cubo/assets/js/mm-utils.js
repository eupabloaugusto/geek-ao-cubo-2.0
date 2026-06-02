/**
 * Utilitários Globais Geek ao Cubo
 * 
 * Contém funções Javascript reutilizáveis (como animações de slide)
 * para evitar duplicação de código nos organismos e moléculas.
 */

window.mmUtils = window.mmUtils || {};

( function ( mmUtils ) {
	'use strict';

	/**
	 * Slide Down Nativo (jQuery slideDown equivalent)
	 */
	mmUtils.slideDown = function ( target, duration = 250 ) {
		target.style.removeProperty( 'display' );
		let display = window.getComputedStyle( target ).display;
		if ( display === 'none' ) {
			display = 'block';
		}
		target.style.display = display;
		let height = target.offsetHeight;
		target.style.overflow = 'hidden';
		target.style.height = 0;
		target.style.paddingTop = 0;
		target.style.paddingBottom = 0;
		target.style.marginTop = 0;
		target.style.marginBottom = 0;
		target.offsetHeight; // Reflow síncrono
		target.style.boxSizing = 'border-box';
		target.style.transitionProperty = 'height, margin, padding';
		target.style.transitionDuration = duration + 'ms';
		target.style.height = height + 'px';
		target.style.removeProperty( 'padding-top' );
		target.style.removeProperty( 'padding-bottom' );
		target.style.removeProperty( 'margin-top' );
		target.style.removeProperty( 'margin-bottom' );
		window.setTimeout( () => {
			target.style.removeProperty( 'height' );
			target.style.removeProperty( 'overflow' );
			target.style.removeProperty( 'transition-duration' );
			target.style.removeProperty( 'transition-property' );
		}, duration );
	};

	/**
	 * Slide Up Nativo (jQuery slideUp equivalent)
	 */
	mmUtils.slideUp = function ( target, duration = 250 ) {
		target.style.transitionProperty = 'height, margin, padding';
		target.style.transitionDuration = duration + 'ms';
		target.style.boxSizing = 'border-box';
		target.style.height = target.offsetHeight + 'px';
		target.offsetHeight; // Reflow síncrono
		target.style.overflow = 'hidden';
		target.style.height = 0;
		target.style.paddingTop = 0;
		target.style.paddingBottom = 0;
		target.style.marginTop = 0;
		target.style.marginBottom = 0;
		window.setTimeout( () => {
			target.style.display = 'none';
			target.style.removeProperty( 'height' );
			target.style.removeProperty( 'padding-top' );
			target.style.removeProperty( 'padding-bottom' );
			target.style.removeProperty( 'margin-top' );
			target.style.removeProperty( 'margin-bottom' );
			target.style.removeProperty( 'overflow' );
			target.style.removeProperty( 'transition-duration' );
			target.style.removeProperty( 'transition-property' );
		}, duration );
	};

} )( window.mmUtils );
