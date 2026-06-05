/**
 * Organism JS: Seção de Episódios em Acordeão (secao-episodios-accordion)
 *
 * Adiciona interatividade para:
 *   1. Abertura/Fechamento suave dos acordeões.
 *   2. Paginação dinâmica local 15+15+15 com botões "Ver Mais" e "Ver Menos".
 *
 * @package geek-ao-cubo
 * @since   2026-05-27
 */

( function () {
	'use strict';

	/**
	 * Configuração e inicialização das ações de acordeão
	 */
	function initAccordions() {
		const triggers = document.querySelectorAll( '.secao-episodios-accordion .js-accordion-trigger' );

		triggers.forEach( trigger => {
			trigger.addEventListener( 'click', function () {
				const item    = this.closest( '.js-accordion-item' );
				const content = item.querySelector( '.js-accordion-content' );
				const isOpen  = item.getAttribute( 'data-state' ) === 'open';

				// Alterna estados de acessibilidade
				this.setAttribute( 'aria-expanded', isOpen ? 'false' : 'true' );
				item.setAttribute( 'data-state', isOpen ? 'closed' : 'open' );

				// Transição suave via JS nativo (display block/none com animação de slide)
				if ( isOpen ) {
					// Fecha o acordeão
					mmUtils.slideUp( content, 250 );
				} else {
					// Abre o acordeão
					mmUtils.slideDown( content, 250 );
				}
			} );
		} );
	}



	/**
	 * Paginação Dinâmica Local 15+15+15
	 */
	function initEpisodePagination() {
		const moreButtons = document.querySelectorAll( '.js-ep-more' );
		const lessButtons = document.querySelectorAll( '.js-ep-less' );

		// Ação de Ver Mais
		moreButtons.forEach( button => {
			button.addEventListener( 'click', function () {
				const item       = this.closest( '.js-accordion-item' );
				const rows       = item.querySelectorAll( '.js-ep-row' );
				const total      = parseInt( this.dataset.total, 10 );
				let current      = parseInt( this.dataset.current, 10 );
				const lessBtn    = item.querySelector( '.js-ep-less' );

				const nextLimit  = current + 15;

				// Revela as próximas 15 linhas
				rows.forEach( row => {
					const idx = parseInt( row.dataset.index, 10 );
					if ( idx >= current && idx < nextLimit ) {
						row.style.display = '';
					}
				} );

				// Atualiza o estado atual do botão
				this.dataset.current = nextLimit;

				// Exibe o botão "Ver menos" já que revelamos mais dados
				if ( lessBtn ) {
					lessBtn.style.display = '';
				}

				// Se todos os episódios já foram revelados, esconde o botão "Ver mais"
				if ( nextLimit >= total ) {
					this.style.display = 'none';
				}
			} );
		} );

		// Ação de Ver Menos (Colapsar para 15 episódios)
		lessButtons.forEach( button => {
			button.addEventListener( 'click', function () {
				const item     = this.closest( '.js-accordion-item' );
				const rows     = item.querySelectorAll( '.js-ep-row' );
				const moreBtn  = item.querySelector( '.js-ep-more' );

				// Esconde todas as linhas a partir do índice 15
				rows.forEach( row => {
					const idx = parseInt( row.dataset.index, 10 );
					if ( idx >= 15 ) {
						row.style.display = 'none';
					}
				} );

				// Reseta o estado do botão "Ver mais"
				if ( moreBtn ) {
					moreBtn.dataset.current = '15';
					moreBtn.style.display = '';
				}

				// Oculta o botão "Ver menos"
				this.style.display = 'none';

				// Rola a tela de volta de forma suave até o topo do acordeão se o usuário já estiver abaixo
				const headerOffset = 100;
				const elementPosition = item.getBoundingClientRect().top;
				const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

				window.scrollTo( {
					top: offsetPosition,
					behavior: 'smooth'
				} );
			} );
		} );
	}

	/**
	 * Inicialização principal do componente
	 */
	function init() {
		initAccordions();
		initEpisodePagination();
	}

	// Executa ao carregar o DOM
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

} )();
