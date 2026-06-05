/**
 * Organism JS: Seção de Obras do Personagem (secao-obras-personagem)
 *
 * Adiciona interatividade para paginação dinâmica baseada em CSS Grid computado.
 * Regras: 3 linhas visíveis no desktop, 4 itens fixos no mobile.
 *
 * @package geek-ao-cubo
 */

( function () {
	'use strict';

	/**
	 * Obtém o número de colunas ativo na tela atual através do CSS Grid computado
	 */
	function getActiveColumns( gridElement ) {
		if ( ! gridElement ) {
			return 4; // Padrão desktop
		}
		const computedStyle = window.getComputedStyle( gridElement );
		const templateCols  = computedStyle.getPropertyValue( 'grid-template-columns' );
		
		if ( ! templateCols ) {
			return 4;
		}
		
		const colsArray = templateCols.trim().split( /\s+/ );
		return colsArray.length || 4;
	}

	/**
	 * Retorna o limite inicial (items visíveis) de acordo com o tamanho da tela
	 */
	function getInitialLimit( cols ) {
		if ( cols >= 4 ) {
			return 12; // Desktop: 3 linhas (3 * 4 = 12)
		} else if ( cols === 3 ) {
			return 9; // Tablet largo: 3 linhas (3 * 3 = 9)
		} else {
			return 4; // Mobile (1 ou 2 colunas): 4 animes fixos
		}
	}

	/**
	 * Atualiza ou inicializa o estado de paginação da seção
	 */
	function updatePaginationState( container ) {
		const grid    = container.querySelector( '.js-obras-grid' );
		const cards   = container.querySelectorAll( '.js-obras-card' );
		const actions = container.querySelector( '.js-obras-actions' );
		const moreBtn = container.querySelector( '.js-obras-more' );
		const lessBtn = container.querySelector( '.js-obras-less' );
		
		if ( ! grid || cards.length === 0 || ! moreBtn ) {
			return;
		}

		const total = cards.length;
		const cols  = getActiveColumns( grid );
		const limit = getInitialLimit( cols );

		let current = parseInt( actions.dataset.current, 10 );

		// Se está na inicialização (current === 0)
		if ( current === 0 ) {
			if ( total > limit ) {
				// Oculta cards excedentes
				cards.forEach( card => {
					const idx = parseInt( card.dataset.index, 10 );
					if ( idx >= limit ) {
						card.style.display = 'none';
					} else {
						card.style.removeProperty( 'display' );
					}
				} );
				
				// Exibe os botões de ação e define o estado atual
				actions.style.removeProperty( 'display' );
				moreBtn.style.removeProperty( 'display' );
				actions.dataset.current = limit;
				if ( lessBtn ) {
					lessBtn.style.display = 'none';
				}
			} else {
				// Tudo cabe na primeira página, oculta as ações progressivas
				cards.forEach( card => card.style.removeProperty( 'display' ) );
				actions.style.display = 'none';
			}
		} else {
			// Se a tela mudou de tamanho e a paginação já foi acionada, mantemos os abertos
			const currentLimit = current;
			cards.forEach( card => {
				const idx = parseInt( card.dataset.index, 10 );
				if ( idx >= currentLimit ) {
					card.style.display = 'none';
				} else {
					card.style.removeProperty( 'display' );
				}
			} );

			if ( total > currentLimit ) {
				moreBtn.style.removeProperty( 'display' );
			} else {
				moreBtn.style.display = 'none';
			}
		}
	}

	function initPagination() {
		const containers = document.querySelectorAll( '.js-obras-container' );

		containers.forEach( container => {
			const moreBtn = container.querySelector( '.js-obras-more' );
			const lessBtn = container.querySelector( '.js-obras-less' );
			const grid    = container.querySelector( '.js-obras-grid' );
			const cards   = container.querySelectorAll( '.js-obras-card' );
			const actions = container.querySelector( '.js-obras-actions' );

			if ( ! moreBtn || ! lessBtn || ! grid || ! actions ) {
				return;
			}

			// Inicializa no carregamento
			updatePaginationState( container );

			// Ação de Ver Mais (Adiciona mais o tamanho do limite)
			moreBtn.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				const cols       = getActiveColumns( grid );
				const total      = parseInt( actions.dataset.total, 10 );
				let current      = parseInt( actions.dataset.current, 10 );
				const step       = getInitialLimit( cols ); 
				const nextLimit  = current + step;

				// Revela os cards do lote seguinte
				cards.forEach( card => {
					const idx = parseInt( card.dataset.index, 10 );
					if ( idx >= current && idx < nextLimit ) {
						card.style.removeProperty( 'display' );
					}
				} );

				// Atualiza o estado
				actions.dataset.current = nextLimit;
				lessBtn.style.removeProperty( 'display' );

				// Se todos os registros foram mostrados, oculta o botão "Ver mais"
				if ( nextLimit >= total ) {
					moreBtn.style.display = 'none';
				}
			} );

			// Ação de Ver Menos
			lessBtn.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				const cols  = getActiveColumns( grid );
				const limit = getInitialLimit( cols );

				// Oculta tudo que passe do limite
				cards.forEach( card => {
					const idx = parseInt( card.dataset.index, 10 );
					if ( idx >= limit ) {
						card.style.display = 'none';
					}
				} );

				// Reseta estado
				actions.dataset.current = limit;
				moreBtn.style.removeProperty( 'display' );
				lessBtn.style.display = 'none';

				// Rola a página de volta para o topo da seção
				const headerOffset    = 80;
				const elementPosition = container.getBoundingClientRect().top;
				const offsetPosition  = elementPosition + window.pageYOffset - headerOffset;

				window.scrollTo( {
					top: offsetPosition,
					behavior: 'smooth'
				} );
			} );
		} );

		// Recalcula limites ao redimensionar a tela (debounced)
		let resizeTimeout;
		window.addEventListener( 'resize', function () {
			clearTimeout( resizeTimeout );
			resizeTimeout = setTimeout( () => {
				containers.forEach( container => {
					const actions = container.querySelector( '.js-obras-actions' );
					if ( actions && actions.dataset.current !== "0" ) {
						updatePaginationState( container );
					}
				} );
			}, 150 );
		} );
	}

	// Aguarda o carregamento do DOM
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initPagination );
	} else {
		initPagination();
	}

} )();
