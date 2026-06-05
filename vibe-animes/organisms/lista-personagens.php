<?php
/**
 * Organism: Lista do Catálogo de Personagens (lista-personagens)
 *
 * Exibe a listagem completa de personagens do catálogo, agrupada por letra inicial.
 * Inclui a navegação alfabética sticky no topo e divisores de seção por letra.
 *
 * Dados: personagens são lidos dos transients Jikan (jikan_anime_chars_{mal_id})
 * já aquecidos pelos animes publicados no banco. Não faz chamadas à API ao vivo.
 *
 * Filtragem:
 * - Lê $_GET['letra'] para exibir apenas uma letra por vez (ex: ?letra=M).
 * - Sem letra: exibe todos agrupados em seções A–Z.
 * - Lê $_GET['busca'] para busca textual por nome de personagem.
 * - Lê $_GET['ordem'] para ordenação (populares | alfabetica).
 *
 * @package vibe-animes
 *
 * @param string $class         Classes CSS adicionais.
 * @param array  $grupos        Dados pré-formatados (override externo).
 * @param array  $letras_ativas Letras com conteúdo (override externo).
 * @param int    $max_num_pages Total de páginas (override externo).
 * @param string $aria_label    Label acessível do section.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$class           = isset( $args['class'] )           ? esc_attr( $args['class'] )     : '';
$grupos_externos = isset( $args['grupos'] )          ? (array) $args['grupos']        : null;
$letras_ativas   = isset( $args['letras_ativas'] )   ? (array) $args['letras_ativas'] : null;
$max_num_pages   = isset( $args['max_num_pages'] )   ? (int) $args['max_num_pages']   : 0;
$aria_label      = isset( $args['aria_label'] )      ? esc_attr( $args['aria_label'] ) : __( 'Catálogo de personagens', 'vibe-animes' );

// ── Tratamento de Busca ────────────────────────────────────
$s_query = isset( $_GET['busca'] ) ? sanitize_text_field( wp_unslash( $_GET['busca'] ) ) : '';

// ── Parâmetro de letra via GET ────────────────────────────
$letra_get = isset( $_GET['letra'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['letra'] ) ) ) : '';

// Valida: apenas letras A–Z ou o caractere especial '#'
if ( ! preg_match( '/^[A-Z#]$/', $letra_get ) ) {
	$letra_get = '';
}

// ── Parâmetros de filtro ────────────────────────────────────
$sel_ordem = isset( $_GET['ordem'] ) ? sanitize_key( wp_unslash( $_GET['ordem'] ) ) : '';

// ── URL base do catálogo ──────────────────────────────────
$base_url           = remove_query_arg( 'pg' );
$base_url_sem_letra = remove_query_arg( 'letra', $base_url );

// ── Parâmetros de Paginação ─────────────────────────────────
$paged = isset( $_GET['pg'] ) ? max( 1, (int) wp_unslash( $_GET['pg'] ) ) : 1;

if ( 1 === $paged ) {
	if ( get_query_var( 'paged' ) ) {
		$paged = get_query_var( 'paged' );
	} elseif ( get_query_var( 'page' ) ) {
		$paged = get_query_var( 'page' );
	}
}

// ── Agrupamento por Letra ─────────────────────────────────
$grupos = array();

if ( null !== $grupos_externos ) {
	// ── MODO EXTERNO: usa dados passados via $args ────────────
	$grupos = $grupos_externos;
	uksort( $grupos, function( $a, $b ) {
		if ( '#' === $a ) return -1;
		if ( '#' === $b ) return 1;
		return strcmp( $a, $b );
	} );
} else {
	// ── MODO INTERNO: agrega personagens dos transients Jikan ──
	// Estratégia: lê todos os animes publicados → para cada anime com anime_id_mal,
	// lê o transient jikan_anime_chars_{mal_id} (já aquecido) → agrega personagens únicos.
	// NÃO faz chamadas à API ao vivo. Usa apenas cache já existente.

	$todos_personagens = array(); // [ mal_id => array ]
	$seen_mal_ids      = array(); // Deduplicação por MAL ID

	// ── Cache de agregação: evita reprocessar todos os animes a cada request ──
	$agg_cache_key  = 'mm_personagens_catalogo_agg_' . md5( $letra_get . $s_query . $sel_ordem . $paged );
	$agg_cached     = get_transient( $agg_cache_key );

	if ( false !== $agg_cached ) {
		$grupos        = $agg_cached['grupos'];
		$letras_ativas = $letras_ativas ?? $agg_cached['letras_ativas'];
	} else {
		// Limpa o nome do personagem: "Uzumaki, Naruto" → "Naruto Uzumaki"
		$clean_name = function( $name ) {
			$parts = explode( ', ', $name );
			return ( count( $parts ) === 2 ) ? $parts[1] . ' ' . $parts[0] : $name;
		};

		// Extrai apenas o nome base da franquia do Anime (Remove temporadas, partes, etc)
		$clean_anime_title = function( $title ) {
			// Remove " (Xª Temporada)" or " (TV)" or " (Movie)", etc.
			$title = preg_replace( '/\s*\([^\)]*(Temporada|Season|Part|Cour|TV|Movie|OVA|ONA|Dub|Leg)[^\)]*\)/i', '', $title );
			// Remove " - Season X" or " 2nd Season"
			$title = preg_replace( '/\s*-?\s*\d+(st|nd|rd|th)?\s+Season/i', '', $title );
			$title = preg_replace( '/\s*-?\s*Season\s*\d+/i', '', $title );
			// Remove " Part X"
			$title = preg_replace( '/\s*-?\s*Part\s*\d+/i', '', $title );
			return trim( $title );
		};

		// Busca IDs de todos os animes com mal_id definido (apenas IDs para performance)
		$anime_ids = get_posts( array(
			'post_type'      => 'anime',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => array(
				array(
					'key'     => 'anime_id_mal',
					'compare' => 'EXISTS',
				),
			),
		) );

		foreach ( $anime_ids as $anime_post_id ) {
			$mal_id = (int) get_post_meta( $anime_post_id, 'anime_id_mal', true );
			if ( ! $mal_id ) {
				continue;
			}

			// Pega o título do anime no WP e extrai o nome limpo da franquia
			$anime_raw_title = get_post_field( 'post_title', $anime_post_id );
			$anime_clean_title = $clean_anime_title( $anime_raw_title );

			// Lê o transient de personagens do anime (NÃO chama a API)
			$chars_raw = get_transient( 'jikan_anime_chars_' . $mal_id );
			if ( empty( $chars_raw ) || ! is_array( $chars_raw ) ) {
				continue;
			}

			foreach ( $chars_raw as $item ) {
				if ( empty( $item['character'] ) || empty( $item['character']['mal_id'] ) ) {
					continue;
				}

				$char_mal_id = (int) $item['character']['mal_id'];

				// Deduplicação: cada personagem aparece apenas uma vez
				if ( isset( $seen_mal_ids[ $char_mal_id ] ) ) {
					// Atualiza favorites se maior (personagem pode aparecer em mais de um anime)
					$favs = isset( $item['character']['favorites'] ) ? (int) $item['character']['favorites'] : 0;
					if ( $favs > $todos_personagens[ $char_mal_id ]['favorites'] ) {
						$todos_personagens[ $char_mal_id ]['favorites'] = $favs;
					}
					continue;
				}

				$seen_mal_ids[ $char_mal_id ] = true;

				$char      = $item['character'];
				$char_name = $clean_name( $char['name'] ?? '' );
				$favorites = isset( $char['favorites'] ) ? (int) $char['favorites'] : 0;

				$imagem = '';
				if ( ! empty( $char['images']['webp']['image_url'] ) ) {
					$imagem = $char['images']['webp']['image_url'];
				} elseif ( ! empty( $char['images']['jpg']['image_url'] ) ) {
					$imagem = $char['images']['jpg']['image_url'];
				}

				$anime_slug = get_post_field( 'post_name', $anime_post_id );
				$url_personagem = site_url( '/' . $anime_slug . '/personagem/' . sanitize_title( $char_name ) . '/' );

				$todos_personagens[ $char_mal_id ] = array(
					'mal_id'    => $char_mal_id,
					'titulo'    => $char_name,
					'url'       => $url_personagem,
					'imagem'    => esc_url( $imagem ),
					'banner_url'=> esc_url( $imagem ),
					'sinopse'   => $anime_clean_title,
					'idioma'    => '',
					'idioma_slug'=> '',
					'generos'   => array(),
					'favorites' => $favorites,
				);
			}
		}

		// ── Busca textual ────────────────────────────────────────
		if ( ! empty( $s_query ) ) {
			$s_lower = mb_strtolower( $s_query );
			$s_words = array_filter( explode( ' ', $s_lower ) );
			$todos_personagens = array_filter( $todos_personagens, function( $p ) use ( $s_words ) {
				$titulo_lower = mb_strtolower( $p['titulo'] );
				foreach ( $s_words as $word ) {
					if ( mb_stripos( $titulo_lower, $word ) === false ) {
						return false;
					}
				}
				return true;
			} );
		}

		// ── Filtra por letra ──────────────────────────────────────
		if ( ! empty( $letra_get ) ) {
			$todos_personagens = array_filter( $todos_personagens, function( $p ) use ( $letra_get ) {
				$inicial = strtoupper( mb_substr( $p['titulo'], 0, 1 ) );
				if ( '#' === $letra_get ) {
					return ! preg_match( '/[A-Z]/', $inicial );
				}
				return $inicial === $letra_get;
			} );
		}

		// ── Ordenação ─────────────────────────────────────────────
		if ( 'populares' === $sel_ordem ) {
			uasort( $todos_personagens, function( $a, $b ) {
				return $b['favorites'] - $a['favorites'];
			} );
		} else {
			// Padrão: alfabética
			uasort( $todos_personagens, function( $a, $b ) {
				return strcmp( mb_strtolower( $a['titulo'] ), mb_strtolower( $b['titulo'] ) );
			} );
		}

		// ── Descobre letras ativas ────────────────────────────────
		if ( null === $letras_ativas ) {
			$letras_ativas = function_exists( 'mm_get_letras_ativas_personagens' )
				? mm_get_letras_ativas_personagens()
				: array();
		}

		// ── Paginação manual (25 por página) ─────────────────────
		$per_page        = 24;
		$total           = count( $todos_personagens );
		$total_pages     = $total > 0 ? (int) ceil( $total / $per_page ) : 1;
		$max_num_pages   = $max_num_pages > 0 ? $max_num_pages : $total_pages;
		$paged_offset    = ( $paged - 1 ) * $per_page;
		$todos_personagens = array_slice( $todos_personagens, $paged_offset, $per_page, true );

		// ── Agrupa por letra ──────────────────────────────────────
		foreach ( $todos_personagens as $p ) {
			$inicial = strtoupper( mb_substr( $p['titulo'], 0, 1 ) );
			if ( ! preg_match( '/[A-Z]/', $inicial ) ) {
				$inicial = '#';
			}
			$grupos[ $inicial ][] = $p;
		}

		// ── Ordena grupos alfabeticamente ─────────────────────────
		uksort( $grupos, function( $a, $b ) {
			if ( '#' === $a ) return -1;
			if ( '#' === $b ) return 1;
			return strcmp( $a, $b );
		} );

		// ── Cache de 10 minutos para evitar reprocessamento ───────
		set_transient( $agg_cache_key, array(
			'grupos'        => $grupos,
			'letras_ativas' => $letras_ativas,
			'max_num_pages' => $max_num_pages,
		), 10 * MINUTE_IN_SECONDS );
	}

	// Recupera max_num_pages do cache se não foi definido
	if ( 0 === $max_num_pages && false !== $agg_cached ) {
		$max_num_pages = $agg_cached['max_num_pages'] ?? 1;
	}
}

// ── Renderiza o Organismo ─────────────────────────────────
?>
<section class="lista-catalogo js-ajax-container <?php echo $class; ?>" aria-label="<?php echo $aria_label; ?>" id="catalogo">

	<!-- Nav Alfabética Sticky (Alvo do Scroll no AJAX) -->
	<div class="js-ajax-scroll-target">
		<?php mm_render_component( 'molecules', 'nav-alfabetica', array(
			'letra_atual'   => $letra_get,
			'letras_ativas' => $letras_ativas,
			'base_url'      => $base_url_sem_letra,
		) ); ?>
	</div>

	<!-- Lista de Personagens -->
	<div class="lista-catalogo__corpo js-ajax-replace">
		<?php if ( empty( $grupos ) ) : ?>
			<div class="lista-catalogo__vazio">
				<p class="lista-catalogo__vazio-msg">
					<?php
					$has_busca   = ! empty( $s_query );
					$has_letra   = ! empty( $letra_get );
					$has_filtros = ! empty( $sel_ordem );

					if ( $has_busca ) {
						echo esc_html( sprintf(
							__( 'Nenhum personagem encontrado para "%s".', 'vibe-animes' ),
							$s_query
						) );
					} elseif ( $has_letra && $has_filtros ) {
						echo esc_html( sprintf(
							__( 'Nenhum personagem encontrado com a letra "%s" e os filtros selecionados.', 'vibe-animes' ),
							$letra_get
						) );
					} elseif ( $has_letra ) {
						echo esc_html( sprintf(
							__( 'Nenhum personagem encontrado com a letra "%s".', 'vibe-animes' ),
							$letra_get
						) );
					} elseif ( $has_filtros ) {
						esc_html_e( 'Nenhum personagem encontrado com os filtros selecionados. Tente ampliar a busca.', 'vibe-animes' );
					} else {
						esc_html_e( 'Nenhum personagem cadastrado ainda.', 'vibe-animes' );
					}
					?>
				</p>
			</div>

		<?php else : ?>
			<?php
			$global_card_index = 0; // Contador global de cards para injeção de anúncio
			foreach ( $grupos as $letra => $personagens ) :
			?>

				<!-- Separador de letra -->
				<?php mm_render_component( 'atoms', 'separador-letra', array(
					'letra' => $letra,
					'id'    => 'secao-' . strtolower( '#' === $letra ? 'num' : $letra ),
				) ); ?>

				<!-- Cards do grupo -->
				<div class="lista-catalogo__grupo" role="list" aria-label="<?php echo esc_attr( sprintf( __( 'Personagens com %s', 'vibe-animes' ), $letra ) ); ?>">
					<?php foreach ( $personagens as $personagem ) : ?>

						<?php
						// Injeção de Anúncio In-Line (Banner) a cada 5 cards
						if ( $global_card_index > 0 && $global_card_index % 5 === 0 ) :
						?>
							<div role="listitem" class="lista-catalogo__ad-item" style="width: 100%;">
								<?php mm_render_component( 'atoms', 'anuncio-adsense', array(
									'slot'     => 'catalogo-in-line-' . $global_card_index,
									'variacao' => 'banner',
								) ); ?>
							</div>
						<?php
						endif;
						$global_card_index++;
						?>

						<div role="listitem">
							<?php mm_render_component( 'molecules', 'card-catalogo', array(
								'titulo'     => $personagem['titulo'],
								'url'        => $personagem['url'],
								'imagem_url' => $personagem['imagem'] ?? '',
								'banner_url' => $personagem['banner_url'] ?? '',
								'post_id'    => 0,
								'sinopse'    => $personagem['sinopse'] ?? '',
								'idioma'     => $personagem['idioma'] ?? '',
								'idioma_slug'=> $personagem['idioma_slug'] ?? '',
								'generos'    => $personagem['generos'] ?? array(),
							) ); ?>
						</div>
					<?php endforeach; ?>
				</div>

			<?php endforeach; ?>
		<?php endif; ?>
	</div>

	<!-- Paginação -->
	<?php
	$_max_pages = $max_num_pages > 0 ? $max_num_pages : 0;
	if ( $_max_pages > 1 ) :
	?>
		<div class="lista-catalogo__paginacao js-ajax-replace">
			<?php mm_render_component( 'molecules', 'pagination', array(
				'max_num_pages' => $_max_pages,
				'current_page'  => $paged,
				'base'          => add_query_arg( 'pg', '%#%', $base_url ),
				'format'        => '?pg=%#%',
			) ); ?>
		</div>
	<?php endif; ?>

	<!-- Anúncio Banner Base -->
	<div class="lista-catalogo__ad-bottom" style="margin-top: var(--space-600); width: 100%; text-align: center;">
		<?php mm_render_component( 'atoms', 'anuncio-adsense', array(
			'slot'     => 'catalogo-personagens-bottom-banner',
			'variacao' => 'banner',
		) ); ?>
	</div>

</section>

