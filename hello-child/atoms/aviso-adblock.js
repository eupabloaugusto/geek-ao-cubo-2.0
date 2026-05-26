/**
 * Atom: Aviso Adblock JS
 *
 * Implementa a detecção passiva de bloqueadores de anúncios e gerencia a
 * exibição do banner flutuante amigável e acessível.
 *
 * @package hello-elementor-child
 * @since   2.0.0
 */

document.addEventListener( 'DOMContentLoaded', function() {
	var CACHE_KEY  = 'mm-adblock-dismissed';
	var EXPIRY_MS  = 24 * 60 * 60 * 1000; // 24 Horas em milissegundos
	var dismissed  = localStorage.getItem( CACHE_KEY );

	// Se o usuário já fechou o aviso nas últimas 24 horas, não faz nada
	if ( dismissed ) {
		var elapsed = Date.now() - parseInt( dismissed, 10 );
		if ( elapsed < EXPIRY_MS ) {
			return;
		} else {
			localStorage.removeItem( CACHE_KEY ); // Expira o cache antigo
		}
	}

	var adblockDetected = false;

	// Função auxiliar para exibir o banner com micro-animação
	function showBanner() {
		var banner = document.querySelector( '.aviso-adblock' );
		if ( ! banner ) {
			return;
		}

		banner.classList.add( 'is-visible' );

		// Acessibilidade: anuncia o alerta para leitores de tela caso esteja escondido
		banner.setAttribute( 'aria-hidden', 'false' );

		// Gerencia o clique no botão de fechar
		var closeBtn = banner.querySelector( '.aviso-adblock__close' );
		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', function() {
				banner.classList.remove( 'is-visible' );
				banner.setAttribute( 'aria-hidden', 'true' );
				localStorage.setItem( CACHE_KEY, Date.now() );
			} );
		}
	}

	// Método 1: Tenta fazer fetch para um recurso de publicidade conhecido (Google AdSense)
	// Esta é a forma mais precisa e moderna de detecção passiva na rede
	fetch( 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js', {
		method: 'HEAD',
		mode: 'no-cors',
		cache: 'no-store'
	} ).then( function() {
		// Script carregou normalmente: provavelmente sem adblocker
	} ).catch( function() {
		// Bloqueado na rede: adblocker ativo!
		adblockDetected = true;
		showBanner();
	} );

	// Método 2: Criação de um elemento isca oculto com classes comuns de anúncio
	// Serve como redundância local se o fetch for burlado
	var bait = document.createElement( 'div' );
	bait.className = 'adsbygoogle ad-placeholder doubleclick-ad banner-ad';
	bait.style.cssText = 'position: absolute; left: -9999px; top: -9999px; width: 1px; height: 1px;';
	document.body.appendChild( bait );

	window.setTimeout( function() {
		var styles = window.getComputedStyle( bait );
		if ( styles.display === 'none' || styles.visibility === 'hidden' || bait.offsetHeight === 0 ) {
			adblockDetected = true;
		}
		bait.remove(); // Limpa a isca da árvore DOM

		if ( adblockDetected ) {
			showBanner();
		}
	}, 400 );
} );
