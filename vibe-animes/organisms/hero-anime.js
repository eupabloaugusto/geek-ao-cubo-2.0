/**
 * Hero Anime — Integração Dinâmica Jikan API (Sprint 4)
 *
 * Tasks implementadas:
 *   4.1 — Fetch assíncrono à Jikan API usando `data-anime-id` do hero
 *   4.2 — Cache via localStorage com TTL de 6 horas por ID consultado
 *   4.3 — Atualização suave de nota MAL e badge-status com micro-animação
 *
 * Também gerencia o toggle de sinopse (movido do inline do hero-anime.php).
 *
 * @package geek-ao-cubo
 * @version 1.0.0
 * @since   2026-05-26
 */

( function () {
	'use strict';

	/* =========================================================
	   CONFIGURAÇÃO
	   ========================================================= */

	const JIKAN_BASE    = 'https://api.jikan.moe/v4';
	const CACHE_PREFIX  = 'mm_jikan_';
	const CACHE_TTL_MS  = 6 * 60 * 60 * 1000; // 6 horas em milissegundos

	// Mapa de status Jikan → classe do badge-status existente
	const STATUS_MAP = {
		'Currently Airing': 'airing',
		'Finished Airing':  'completed',
		'Not yet aired':    'upcoming',
	};

	// Mapa de status → label em PT-BR (espelha badge-status.php)
	const STATUS_LABEL = {
		airing:    'Em exibição',
		completed: 'Finalizado',
		upcoming:  'Em breve',
	};

	/* =========================================================
	   TASK 4.2 — CACHE localStorage com TTL
	   ========================================================= */

	/**
	 * Lê um item do cache. Retorna null se expirado ou ausente.
	 * @param {string} animeId
	 * @returns {Object|null}
	 */
	function cacheGet( animeId ) {
		try {
			const raw = localStorage.getItem( CACHE_PREFIX + animeId );
			if ( ! raw ) return null;

			const entry = JSON.parse( raw );
			if ( Date.now() > entry.expires_at ) {
				localStorage.removeItem( CACHE_PREFIX + animeId );
				return null;
			}
			return entry.data;
		} catch ( e ) {
			// localStorage pode estar bloqueado (modo privado / storage cheio)
			return null;
		}
	}

	/**
	 * Grava um item no cache com expiração automática.
	 * @param {string} animeId
	 * @param {Object} data
	 */
	function cacheSet( animeId, data ) {
		try {
			const entry = {
				expires_at: Date.now() + CACHE_TTL_MS,
				data,
			};
			localStorage.setItem( CACHE_PREFIX + animeId, JSON.stringify( entry ) );
		} catch ( e ) {
			// Falha silenciosa — localStorage pode estar cheio
		}
	}

	/* =========================================================
	   TASK 4.1 — FETCH JIKAN API
	   ========================================================= */

	/**
	 * Busca dados frescos do anime na Jikan API.
	 * Primeiro tenta o cache; se ausente/expirado, faz a requisição real.
	 * @param {string} animeId
	 * @returns {Promise<Object|null>}
	 */
	async function fetchAnimeData( animeId ) {
		// Verifica cache antes de ir à rede
		const cached = cacheGet( animeId );
		if ( cached ) {
			console.debug( `[Jikan] Cache hit para ID ${animeId} (expira em ${Math.round( ( cacheGet( animeId ) ? CACHE_TTL_MS : 0 ) / 60000 )}min)` );
			return cached;
		}

		try {
			const response = await fetch( `${JIKAN_BASE}/anime/${animeId}`, {
				headers: { 'Accept': 'application/json' },
				// Sem credenciais — endpoint público
			} );

			// Jikan retorna 429 quando o rate limit é excedido (free tier: 3 req/s, 60/min)
			if ( response.status === 429 ) {
				console.warn( '[Jikan] Rate limit atingido (HTTP 429). Aguardando próxima visita.' );
				return null;
			}

			if ( ! response.ok ) {
				console.warn( `[Jikan] Erro HTTP ${response.status} para ID ${animeId}.` );
				return null;
			}

			const json = await response.json();
			const data = json.data;

			if ( ! data ) return null;

			// Persiste no cache para evitar chamadas repetidas
			cacheSet( animeId, data );

			return data;

		} catch ( err ) {
			// Falha de rede ou CORS — não quebra a página
			console.warn( '[Jikan] Falha na requisição:', err.message );
			return null;
		}
	}

	/* =========================================================
	   TASK 4.3 — ATUALIZAÇÃO SUAVE COM MICRO-ANIMAÇÃO
	   ========================================================= */

	/**
	 * Aplica a classe de fade-out → atualiza conteúdo → fade-in.
	 * Usa CSS transitions já declaradas no hero-anime.css via [data-jikan-updating].
	 * @param {HTMLElement} el
	 * @param {Function}    updateFn — função que modifica o DOM dentro do elemento
	 */
	function animatedUpdate( el, updateFn ) {
		el.setAttribute( 'data-jikan-updating', 'true' );

		// Aguarda o fade-out (150ms, definido no CSS) antes de trocar o conteúdo
		setTimeout( () => {
			updateFn();
			el.removeAttribute( 'data-jikan-updating' );
		}, 150 );
	}

	/**
	 * Atualiza a nota MAL exibida no hero se o valor da API for diferente do banco.
	 * @param {HTMLElement} hero
	 * @param {string|number} scoreFromApi
	 */
	function updateScore( hero, scoreFromApi ) {
		const scoreWrapper = hero.querySelector( '[data-jikan-field="score"]' );
		if ( ! scoreWrapper ) return;

		const formatted    = parseFloat( scoreFromApi ).toFixed( 2 );
		const notaEl       = scoreWrapper.querySelector( '.nota-mal__valor, .nota-mal__score, [class*="nota-mal"]' );
		const currentScore = notaEl ? notaEl.textContent.trim() : '';

		// Só atualiza se o valor realmente mudou (evita flicker desnecessário)
		if ( currentScore === formatted ) return;

		animatedUpdate( scoreWrapper, () => {
			if ( notaEl ) {
				notaEl.textContent = formatted;
			} else {
				// Fallback: atualiza o text node do wrapper diretamente
				scoreWrapper.textContent = formatted;
			}
		} );

		console.info( `[Jikan] Nota atualizada: ${currentScore} → ${formatted}` );
	}

	/**
	 * Atualiza o badge-status se o status da API for diferente do banco.
	 * Mantém a estrutura visual do átomo badge-status existente.
	 * @param {HTMLElement} hero
	 * @param {string}      statusFromApi — valor bruto da Jikan (ex: "Currently Airing")
	 */
	function updateStatus( hero, statusFromApi ) {
		const statusWrapper = hero.querySelector( '[data-jikan-field="status"]' );
		if ( ! statusWrapper ) return;

		const newStatusKey = STATUS_MAP[ statusFromApi ] || null;
		if ( ! newStatusKey ) return;

		const badgeEl      = statusWrapper.querySelector( '.badge-status' );
		const currentClass = badgeEl
			? ( badgeEl.className.match( /badge-status--(\w+)/ ) || [] )[ 1 ] || ''
			: '';

		if ( currentClass === newStatusKey ) return;

		const newLabel = STATUS_LABEL[ newStatusKey ] || statusFromApi;

		animatedUpdate( statusWrapper, () => {
			if ( badgeEl ) {
				// Troca a classe modificadora preservando a classe base
				badgeEl.className = badgeEl.className
					.replace( /badge-status--\w+/g, '' )
					.trim() + ` badge-status--${newStatusKey}`;

				const labelEl = badgeEl.querySelector( '.badge-status__label, span' );
				if ( labelEl ) {
					labelEl.textContent = newLabel;
				}
			}
		} );

		console.info( `[Jikan] Status atualizado: ${currentClass} → ${newStatusKey}` );
	}

	/**
	 * Atualiza a quantidade de membros/votos exibida se for diferente.
	 * @param {HTMLElement} hero
	 * @param {number}      membersFromApi
	 */
	function updateMembers( hero, membersFromApi ) {
		const membersWrapper = hero.querySelector( '[data-jikan-field="membros"]' );
		if ( ! membersWrapper ) return;

		// Formata número no padrão brasileiro pt-BR (ex: 1.234.567)
		const formatted = new Intl.NumberFormat( 'pt-BR' ).format( membersFromApi );
		const label     = membersFromApi === 1 ? 'voto' : 'votos';
		const newText   = `(${formatted} ${label})`;

		if ( membersWrapper.textContent.trim() === newText ) return;

		animatedUpdate( membersWrapper, () => {
			membersWrapper.textContent = newText;
		} );

		console.info( `[Jikan] Votos atualizados: ${membersWrapper.textContent.trim()} → ${newText}` );
	}

	/* =========================================================
	   SINOPSE TOGGLE (movido do inline do hero-anime.php)
	   ========================================================= */

	function initSinopseToggle() {
		document.querySelectorAll( '.js-hero-sinopse' ).forEach( sinopse => {
			const textEl = sinopse.querySelector( '.js-hero-sinopse-content' );
			const btn    = sinopse.querySelector( '.js-hero-sinopse-toggle' );

			if ( ! textEl || ! btn ) return;

			// Função local para verificar se o texto transborda o limite visual
			function checkOverflow() {
				// Guarda a classe para restaurar depois
				const wasExpanded = sinopse.classList.contains( 'hero-anime__sinopse--expanded' );
				
				// Desabilita transições temporariamente para evitar concorrência/atraso na medição
				textEl.style.transition = 'none';

				// Remove temporariamente a classe expandida para poder medir o estado nativo clampado
				sinopse.classList.remove( 'hero-anime__sinopse--expanded' );
				
				// Força reflow instantâneo do navegador para aplicar a mudança de estilo antes da medição
				textEl.offsetHeight;

				// Se a altura interna de rolagem for maior que a altura visível física, há transbordo.
				const isOverflowing = textEl.scrollHeight > textEl.offsetHeight;

				// Restaura o estado anterior
				if ( wasExpanded ) {
					sinopse.classList.add( 'hero-anime__sinopse--expanded' );
				}

				// Força reflow novamente e reativa a transição suave
				textEl.offsetHeight;
				textEl.style.transition = '';

				if ( isOverflowing ) {
					btn.style.display = 'inline-flex';
				} else {
					btn.style.display = 'none';
				}
			}

			// Executa a medição inicial após carregamento completo das fontes e layout
			const runCheck = () => {
				requestAnimationFrame( () => {
					checkOverflow();
				} );
			};

			if ( document.fonts && document.fonts.ready ) {
				document.fonts.ready.then( runCheck );
			} else {
				runCheck();
			}

			// Adiciona o ouvinte de clique
			btn.addEventListener( 'click', function () {
				const expanded = sinopse.classList.toggle( 'hero-anime__sinopse--expanded' );
				this.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
				const toggleText = this.querySelector( '.hero-anime__sinopse-toggle-text' );
				if ( toggleText ) {
					toggleText.textContent = expanded
						? this.dataset.labelCollapse
						: this.dataset.labelExpand;
				}
			} );

			// Recalcula o transbordo caso a janela seja redimensionada
			let resizeTimeout;
			window.addEventListener( 'resize', () => {
				clearTimeout( resizeTimeout );
				resizeTimeout = setTimeout( checkOverflow, 150 );
			} );
		} );
	}

	/* =========================================================
	   INICIALIZAÇÃO PRINCIPAL
	   ========================================================= */

	async function init() {
		// Toggle de sinopse — sem dependência de ID MAL
		initSinopseToggle();

		// Coleta todos os heroes com ID MAL na página
		const heroes = document.querySelectorAll( '.hero-anime[data-anime-id]' );
		if ( ! heroes.length ) return;

		for ( const hero of heroes ) {
			const animeId = hero.dataset.animeId;
			if ( ! animeId ) continue;

			const data = await fetchAnimeData( animeId );
			if ( ! data ) continue;

			// Task 4.3 — Atualiza nota e status se houver divergência
			if ( data.score ) {
				updateScore( hero, data.score );
			}

			if ( data.status ) {
				updateStatus( hero, data.status );
			}

			if ( data.members ) {
				updateMembers( hero, data.members );
			}
		}
	}

	// Dispara após o DOM estar pronto
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

} )();
