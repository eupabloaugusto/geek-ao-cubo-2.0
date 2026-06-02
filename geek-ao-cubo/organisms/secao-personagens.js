/**
 * Organism JS: Seção de Personagens em Acordeão (secao-personagens)
 *
 * Adiciona interatividade para:
 *   1. Abertura/Fechamento suave das abas retráteis.
 *   2. Paginação progressiva dinâmica de 5 em 5 linhas baseada em colunas computadas.
 *
 * @package geek-ao-cubo
 * @since   2026-05-27
 */

( function () {
	'use strict';



	/**
	 * Inicializa a interatividade de acordeão nas abas retráteis
	 */
	function initAccordions() {
		const triggers = document.querySelectorAll( '.secao-personagens .js-accordion-trigger' );

		triggers.forEach( trigger => {
			trigger.addEventListener( 'click', function () {
				const item    = this.closest( '.js-accordion-item' );
				const content = item.querySelector( '.js-accordion-content' );
				const isOpen  = item.getAttribute( 'data-state' ) === 'open';

				// Alterna estados ARIA para acessibilidade
				this.setAttribute( 'aria-expanded', isOpen ? 'false' : 'true' );
				item.setAttribute( 'data-state', isOpen ? 'closed' : 'open' );

				if ( isOpen ) {
					mmUtils.slideUp( content, 250 );
				} else {
					mmUtils.slideDown( content, 250 );
					
					// Recalcula a paginação ao abrir
					const moreBtn = item.querySelector( '.js-char-more' );
					if ( moreBtn ) {
						updatePaginationState( item );
					}
				}
			} );
		} );
	}

	/**
	 * Obtém o número de colunas ativo na tela atual através do CSS Grid computado
	 */
	function getActiveColumns( gridElement ) {
		if ( ! gridElement ) {
			return 3;
		}
		const computedStyle = window.getComputedStyle( gridElement );
		const templateCols  = computedStyle.getPropertyValue( 'grid-template-columns' );
		
		if ( ! templateCols ) {
			return 3;
		}
		
		const colsArray = templateCols.trim().split( /\s+/ );
		return colsArray.length || 3;
	}

	/**
	 * Atualiza ou inicializa o estado de paginação progressiva de um acordeão
	 */
	function updatePaginationState( item ) {
		const grid      = item.querySelector( '.js-char-grid' );
		const cards     = item.querySelectorAll( '.js-char-card' );
		const actions   = item.querySelector( '.js-char-actions' );
		const moreBtn   = item.querySelector( '.js-char-more' );
		const lessBtn   = item.querySelector( '.js-char-less' );
		
		if ( ! grid || cards.length === 0 || ! moreBtn ) {
			return;
		}

		const total = cards.length;
		const cols  = getActiveColumns( grid );
		const limit = cols * 5; // Limite de 5 linhas visíveis (15 no desktop/tablet, 10 no mobile)

		let current = parseInt( moreBtn.dataset.current, 10 );

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
				moreBtn.dataset.current = limit;
				if ( lessBtn ) {
					lessBtn.style.display = 'none';
				}
			} else {
				// Tudo cabe na primeira página, oculta as ações progressivas
				cards.forEach( card => card.style.removeProperty( 'display' ) );
				actions.style.display = 'none';
			}
		} else {
			// Se a tela mudou de tamanho, ajustamos dinamicamente as visibilidades
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

	/**
	 * Configura os listeners dos botões de paginação (Ver mais / Ver menos)
	 */
	function initCharacterPagination() {
		const items = document.querySelectorAll( '.secao-personagens .js-accordion-item' );

		items.forEach( item => {
			const moreBtn = item.querySelector( '.js-char-more' );
			const lessBtn = item.querySelector( '.js-char-less' );
			const grid    = item.querySelector( '.js-char-grid' );
			const cards   = item.querySelectorAll( '.js-char-card' );

			if ( ! moreBtn || ! lessBtn || ! grid ) {
				return;
			}

			// Inicializa no carregamento
			updatePaginationState( item );

			// Ação de Ver Mais (Adiciona mais 5 linhas)
			moreBtn.addEventListener( 'click', function () {
				const cols       = getActiveColumns( grid );
				const total      = parseInt( this.dataset.total, 10 );
				let current      = parseInt( this.dataset.current, 10 );
				const step       = cols * 5; // Mais 5 linhas
				const nextLimit  = current + step;

				// Revela os cards do lote seguinte
				cards.forEach( card => {
					const idx = parseInt( card.dataset.index, 10 );
					if ( idx >= current && idx < nextLimit ) {
						card.style.removeProperty( 'display' );
					}
				} );

				// Atualiza o estado
				this.dataset.current = nextLimit;
				lessBtn.style.removeProperty( 'display' );

				// Se todos os registros foram mostrados, oculta o botão "Ver mais"
				if ( nextLimit >= total ) {
					this.style.display = 'none';
				}
			} );

			// Ação de Ver Menos (Colapsa de volta às 5 linhas iniciais)
			lessBtn.addEventListener( 'click', function () {
				const cols  = getActiveColumns( grid );
				const limit = cols * 5;

				// Oculta tudo que passe do limite de 5 linhas
				cards.forEach( card => {
					const idx = parseInt( card.dataset.index, 10 );
					if ( idx >= limit ) {
						card.style.display = 'none';
					}
				} );

				// Reseta estado
				moreBtn.dataset.current = limit;
				moreBtn.style.removeProperty( 'display' );
				this.style.display = 'none';

				// Rola a página de volta de forma suave ao topo do acordeão
				const headerOffset   = 80;
				const elementPosition = item.getBoundingClientRect().top;
				const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

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
				items.forEach( item => {
					const moreBtn = item.querySelector( '.js-char-more' );
					if ( moreBtn && moreBtn.dataset.current !== "0" ) {
						updatePaginationState( item );
					}
				} );
			}, 150 );
		} );
	}

	/**
	 * Inicializador Principal
	 */
	function init() {
		initAccordions();
		initCharacterPagination();
	}

	// Aguarda o carregamento do DOM
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

} )();
