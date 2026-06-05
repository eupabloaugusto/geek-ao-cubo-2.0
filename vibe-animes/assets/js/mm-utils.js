/**
 * Utilitários Globais Vibe Animes
 * 
 * Contém funções Javascript reutilizáveis (como animações de slide)
 * para evitar duplicação de código nos organismos e moléculas.
 */

window.mmUtils = window.mmUtils || {};

( function ( mmUtils ) {
	'use strict';

	// #region agent log (debug-5342ce)
	function mmDbg5342ce( payload ) {
		try {
			fetch( 'http://127.0.0.1:7750/ingest/ab41193b-f4d9-4315-8dc0-7f981894347d', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-Debug-Session-Id': '5342ce',
				},
				body: JSON.stringify( Object.assign( {
					sessionId: '5342ce',
					timestamp: Date.now(),
				}, payload ) ),
			} ).catch( () => {} );
		} catch ( e ) {}
	}

	function mmSafeUrl() {
		try {
			return window.location.href;
		} catch ( e ) {
			return '';
		}
	}

	document.addEventListener( 'submit', function ( ev ) {
		const form = ev.target;
		if ( !form || form.tagName !== 'FORM' ) return;
		if ( !form.classList.contains( 'form-busca' ) && form.getAttribute( 'role' ) !== 'search' ) return;

		const input = form.querySelector( 'input[name=\"s\"]' );
		const s = input ? String( input.value || '' ) : '';
		mmDbg5342ce( {
			runId: 'pre-fix',
			hypothesisId: 'H4',
			location: 'mm-utils.js:search-form-submit',
			message: 'search form submitted',
			data: {
				s,
				action: form.getAttribute( 'action' ) || '',
				method: form.getAttribute( 'method' ) || '',
				urlBefore: mmSafeUrl(),
			},
		} );
	}, { capture: true } );

	document.addEventListener( 'DOMContentLoaded', function () {
		const params = new URLSearchParams( window.location.search || '' );
		const s = params.get( 's' ) || '';
		if ( s ) {
			mmDbg5342ce( {
				runId: 'pre-fix',
				hypothesisId: 'H5',
				location: 'mm-utils.js:dom-ready',
				message: 'page loaded with search param',
				data: {
					s,
					url: mmSafeUrl(),
					path: window.location.pathname,
				},
			} );
		}
	} );
	// #endregion agent log (debug-5342ce)

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

