<?php
/**
 * Organism: Lista do CatÃ¡logo de dubladores (lista-dubladores)
 *
 * Exibe a listagem completa de dubladores do catÃ¡logo, agrupada por letra inicial.
 * Inclui a navegaÃ§Ã£o alfabÃ©tica sticky no topo e divisores de seÃ§Ã£o por letra.
 *
 * Dados: dubladores sÃ£o lidos dos transients Jikan (jikan_anime_chars_{mal_id})
 * jÃ¡ aquecidos pelos animes publicados no banco. NÃ£o faz chamadas Ã  API ao vivo.
 *
 * Filtragem:
 * - LÃª $_GET['letra'] para exibir apenas uma letra por vez (ex: ?letra=M).
 * - Sem letra: exibe todos agrupados em seÃ§Ãµes Aâ€“Z.
 * - LÃª $_GET['busca'] para busca textual por nome de dublador.
 * - LÃª $_GET['ordem'] para ordenaÃ§Ã£o (populares | alfabetica).
 *
 * @package vibe-animes
 *
 * @param string $class         Classes CSS adicionais.
 * @param array  $grupos        Dados prÃ©-formatados (override externo).
 * @param array  $letras_ativas Letras com conteÃºdo (override externo).
 * @param int    $max_num_pages Total de pÃ¡ginas (override externo).
 * @param string $aria_label    Label acessÃ­vel do section.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$class           = isset( $args['class'] )           ? esc_attr( $args['class'] )     : '';
$grupos_externos = isset( $args['grupos'] )          ? (array) $args['grupos']        : null;
$letras_ativas   = isset( $args['letras_ativas'] )   ? (array) $args['letras_ativas'] : null;
$max_num_pages   = isset( $args['max_num_pages'] )   ? (int) $args['max_num_pages']   : 0;
$aria_label      = isset( $args['aria_label'] )      ? esc_attr( $args['aria_label'] ) : __( 'CatÃ¡logo de dubladores', 'vibe-animes' );

// â”€â”€ Tratamento de Busca â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$s_query = isset( $_GET['busca'] ) ? sanitize_text_field( wp_unslash( $_GET['busca'] ) ) : '';

// â”€â”€ ParÃ¢metro de letra via GET â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$letra_get = isset( $_GET['letra'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['letra'] ) ) ) : '';

// Valida: apenas letras Aâ€“Z ou o caractere especial '#'
if ( ! preg_match( '/^[A-Z#]$/', $letra_get ) ) {
	$letra_get = '';
}

// â”€â”€ ParÃ¢metros de filtro â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$sel_ordem = isset( $_GET['ordem'] ) ? sanitize_key( wp_unslash( $_GET['ordem'] ) ) : '';

// ── URL base do catálogo ──────────────────────────────────────────────────────
$base_url           = remove_query_arg( 'pg' );
$base_url_sem_letra = remove_query_arg( 'letra', $base_url );

// ── Parâmetros de Paginação ──────────────────────────────────────────────────
$s_query    = isset( $_GET['busca'] )  ? sanitize_text_field( $_GET['busca'] ) : '';
$sel_ordem  = isset( $_GET['ordem'] )  ? sanitize_text_field( $_GET['ordem'] ) : 'populares';
$letra_get  = isset( $_GET['letra'] )  ? sanitize_text_field( $_GET['letra'] ) : '';
$idioma_get = isset( $_GET['idioma'] ) ? sanitize_text_field( $_GET['idioma'] ) : '';

$paged = max( 1, get_query_var( 'paged' ) ? get_query_var( 'paged' ) : ( isset( $_GET['paged'] ) ? (int) $_GET['paged'] : 1 ) );

// ── Agrupamento por Letra ─────────────────────────────────────────────────────
$grupos = array();

if ( null !== $grupos_externos ) {
	// ── MODO EXTERNO: usa dados passados via $args ────────────────────────
	$grupos = $grupos_externos;
	uksort( $grupos, function( $a, $b ) {
		if ( '#' === $a ) return -1;
		if ( '#' === $b ) return 1;
		return strcmp( $a, $b );
	} );
} else {
	// ── MODO INTERNO: agrega dubladores dos transients Jikan ──
	// Estratégia: lê todos os animes publicados → para cada anime com anime_id_mal,
	// lê o transient jikan_anime_chars_{mal_id} (já aquecido) → agrega dubladores únicos.
	// NÃO faz chamadas à API ao vivo. Usa apenas cache já existente.

	$todos_dubladores = array(); // [ mal_id => array ]
	$seen_mal_ids      = array(); // Deduplicação por MAL ID

	// ── Cache de agregação: evita reprocessar todos os animes a cada request ──
	$agg_cache_key  = 'mm_dubladores_catalogo_v4_' . md5( $letra_get . $s_query . $sel_ordem . $idioma_get . $paged );
	$agg_cached     = get_transient( $agg_cache_key );

	if ( false !== $agg_cached ) {
		$grupos        = $agg_cached['grupos'];
		$letras_ativas = $letras_ativas ?? $agg_cached['letras_ativas'];
	} else {
		// Limpa o nome do dublador: "Uzumaki, Naruto" â†’ "Naruto Uzumaki"
		$clean_name = function( $name ) {
			$parts = explode( ', ', $name );
			return ( count( $parts ) === 2 ) ? $parts[1] . ' ' . $parts[0] : $name;
		};

		// Extrai apenas o nome base da franquia do Anime (Remove temporadas, partes, etc)
		$clean_anime_title = function( $title ) {
			// Remove " (XÂª Temporada)" or " (TV)" or " (Movie)", etc.
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

			// Pega o tÃ­tulo do anime no WP e extrai o nome limpo da franquia
			$anime_raw_title = get_post_field( 'post_title', $anime_post_id );
			$anime_clean_title = $clean_anime_title( $anime_raw_title );

			// LÃª o transient de dubladores do anime (NÃƒO chama a API)
			$chars_raw = get_transient( 'jikan_anime_chars_' . $mal_id );
			if ( empty( $chars_raw ) || ! is_array( $chars_raw ) ) {
				continue;
			}

			foreach ( $chars_raw as $item ) {
				if ( empty( $item['voice_actors'] ) || ! is_array( $item['voice_actors'] ) ) {
					continue;
				}

				foreach ( $item['voice_actors'] as $va ) {
					$lang = isset( $va['language'] ) ? $va['language'] : '';
					$allowed_langs = array( 'Japanese', 'Portuguese', 'Portuguese (BR)', 'English', 'Spanish', 'French', 'German' );
					if ( ! in_array( $lang, $allowed_langs, true ) ) {
						continue;
					}

					if ( empty( $va['person'] ) || empty( $va['person']['mal_id'] ) ) {
						continue;
					}

					$char_mal_id = (int) $va['person']['mal_id'];

					$lang_map = array(
						'Japanese'        => 'Original',
						'Portuguese'      => 'PT-BR',
						'Portuguese (BR)' => 'PT-BR',
						'English'         => 'Inglês',
						'Spanish'         => 'Espanhol',
						'French'          => 'Francês',
						'German'          => 'Alemão',
					);
					$idioma_display = isset( $lang_map[ $lang ] ) ? $lang_map[ $lang ] : $lang;

					// Aplica o filtro de idioma, se houver
					if ( ! empty( $idioma_get ) && $idioma_get !== $idioma_display ) {
						continue;
					}

					// Deduplicação: cada dublador aparece apenas uma vez
					if ( isset( $seen_mal_ids[ $char_mal_id ] ) ) {
						continue;
					}

					$seen_mal_ids[ $char_mal_id ] = true;

					$char      = $va['person'];
					$char_name = $clean_name( $char['name'] ?? '' );
					$favorites = isset( $char['favorites'] ) ? (int) $char['favorites'] : 0;

					$imagem = '';
					if ( ! empty( $char['images']['webp']['image_url'] ) ) {
						$imagem = $char['images']['webp']['image_url'];
					} elseif ( ! empty( $char['images']['jpg']['image_url'] ) ) {
						$imagem = $char['images']['jpg']['image_url'];
					}

					$anime_slug = get_post_field( 'post_name', $anime_post_id );
					$url_dublador = site_url( '/' . $anime_slug . '/dubladores/' . sanitize_title( $char_name ) . '/' );

					$idioma_slug    = sanitize_title( $idioma_display );

					$todos_dubladores[ $char_mal_id ] = array(
						'mal_id'    => $char_mal_id,
						'titulo'    => $char_name,
						'url'       => $url_dublador,
						'imagem'    => esc_url( $imagem ),
						'banner_url'=> esc_url( $imagem ),
						'sinopse'   => $anime_clean_title,
						'idioma'    => $idioma_display,
						'idioma_slug'=> $idioma_slug,
						'generos'   => array(),
						'favorites' => $favorites,
					);
				}
			}
		}

		// â”€â”€ Busca textual â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
		if ( ! empty( $s_query ) ) {
			$s_lower = mb_strtolower( $s_query );
			$todos_dubladores = array_filter( $todos_dubladores, function( $p ) use ( $s_lower ) {
				return mb_stripos( $p['titulo'], $s_lower ) !== false;
			} );
		}

		// â”€â”€ Filtra por letra â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
		if ( ! empty( $letra_get ) ) {
			$todos_dubladores = array_filter( $todos_dubladores, function( $p ) use ( $letra_get ) {
				$inicial = strtoupper( mb_substr( $p['titulo'], 0, 1 ) );
				if ( '#' === $letra_get ) {
					return ! preg_match( '/[A-Z]/', $inicial );
				}
				return $inicial === $letra_get;
			} );
		}

		// â”€â”€ OrdenaÃ§Ã£o â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
		if ( 'populares' === $sel_ordem ) {
			uasort( $todos_dubladores, function( $a, $b ) {
				return $b['favorites'] - $a['favorites'];
			} );
		} else {
			// PadrÃ£o: alfabÃ©tica
			uasort( $todos_dubladores, function( $a, $b ) {
				return strcmp( mb_strtolower( $a['titulo'] ), mb_strtolower( $b['titulo'] ) );
			} );
		}

		// ── Descobre letras ativas ────────────────────────────────
		if ( null === $letras_ativas ) {
			$letras_ativas = function_exists( 'mm_get_letras_ativas_dubladores_catalogo' )
				? mm_get_letras_ativas_dubladores_catalogo()
				: array();
		}

		// â”€â”€ PaginaÃ§Ã£o manual (25 por pÃ¡gina) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
		$per_page        = 24;
		$total           = count( $todos_dubladores );
		$total_pages     = $total > 0 ? (int) ceil( $total / $per_page ) : 1;
		$max_num_pages   = $max_num_pages > 0 ? $max_num_pages : $total_pages;
		$paged_offset    = ( $paged - 1 ) * $per_page;
		$todos_dubladores = array_slice( $todos_dubladores, $paged_offset, $per_page, true );

		// â”€â”€ Agrupa por letra â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
		foreach ( $todos_dubladores as $p ) {
			$inicial = strtoupper( mb_substr( $p['titulo'], 0, 1 ) );
			if ( ! preg_match( '/[A-Z]/', $inicial ) ) {
				$inicial = '#';
			}
			$grupos[ $inicial ][] = $p;
		}

		// â”€â”€ Ordena grupos alfabeticamente â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
		uksort( $grupos, function( $a, $b ) {
			if ( '#' === $a ) return -1;
			if ( '#' === $b ) return 1;
			return strcmp( $a, $b );
		} );

		// â”€â”€ Cache de 10 minutos para evitar reprocessamento â”€â”€â”€â”€â”€â”€â”€
		set_transient( $agg_cache_key, array(
			'grupos'        => $grupos,
			'letras_ativas' => $letras_ativas,
			'max_num_pages' => $max_num_pages,
		), 10 * MINUTE_IN_SECONDS );
	}

	// Recupera max_num_pages do cache se nÃ£o foi definido
	if ( 0 === $max_num_pages && false !== $agg_cached ) {
		$max_num_pages = $agg_cached['max_num_pages'] ?? 1;
	}
}

// â”€â”€ Renderiza o Organismo â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
?>
<section class="lista-catalogo js-ajax-container <?php echo $class; ?>" aria-label="<?php echo $aria_label; ?>" id="catalogo">

	<!-- Nav AlfabÃ©tica Sticky (Alvo do Scroll no AJAX) -->
	<div class="js-ajax-scroll-target">
		<?php mm_render_component( 'molecules', 'nav-alfabetica', array(
			'letra_atual'   => $letra_get,
			'letras_ativas' => $letras_ativas,
			'base_url'      => $base_url_sem_letra,
		) ); ?>
	</div>

	<!-- Lista de dubladores -->
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
							__( 'Nenhum dublador encontrado para "%s".', 'vibe-animes' ),
							$s_query
						) );
					} elseif ( $has_letra && $has_filtros ) {
						echo esc_html( sprintf(
							__( 'Nenhum dublador encontrado com a letra "%s" e os filtros selecionados.', 'vibe-animes' ),
							$letra_get
						) );
					} elseif ( $has_letra ) {
						echo esc_html( sprintf(
							__( 'Nenhum dublador encontrado com a letra "%s".', 'vibe-animes' ),
							$letra_get
						) );
					} elseif ( $has_filtros ) {
						esc_html_e( 'Nenhum dublador encontrado com os filtros selecionados. Tente ampliar a busca.', 'vibe-animes' );
					} else {
						esc_html_e( 'Nenhum dublador cadastrado ainda.', 'vibe-animes' );
					}
					?>
				</p>
			</div>

		<?php else : ?>
			<?php
			$global_card_index = 0; // Contador global de cards para injeÃ§Ã£o de anÃºncio
			foreach ( $grupos as $letra => $dubladores ) :
			?>

				<!-- Separador de letra -->
				<?php mm_render_component( 'atoms', 'separador-letra', array(
					'letra' => $letra,
					'id'    => 'secao-' . strtolower( '#' === $letra ? 'num' : $letra ),
				) ); ?>

				<!-- Cards do grupo -->
				<div class="lista-catalogo__grupo" role="list" aria-label="<?php echo esc_attr( sprintf( __( 'dubladores com %s', 'vibe-animes' ), $letra ) ); ?>">
					<?php foreach ( $dubladores as $dublador ) : ?>

						<?php
						// InjeÃ§Ã£o de AnÃºncio In-Line (Banner) a cada 5 cards
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
								'titulo'     => $dublador['titulo'],
								'url'        => $dublador['url'],
								'imagem_url' => $dublador['imagem'] ?? '',
								'banner_url' => $dublador['banner_url'] ?? '',
								'post_id'    => 0,
								'sinopse'    => $dublador['sinopse'] ?? '',
								'idioma'     => $dublador['idioma'] ?? '',
								'idioma_slug'=> $dublador['idioma_slug'] ?? '',
								'generos'    => $dublador['generos'] ?? array(),
							) ); ?>
						</div>
					<?php endforeach; ?>
				</div>

			<?php endforeach; ?>
		<?php endif; ?>
	</div>

	<!-- PaginaÃ§Ã£o -->
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

	<!-- AnÃºncio Banner Base -->
	<div class="lista-catalogo__ad-bottom" style="margin-top: var(--space-600); width: 100%; text-align: center;">
		<?php mm_render_component( 'atoms', 'anuncio-adsense', array(
			'slot'     => 'catalogo-dubladores-bottom-banner',
			'variacao' => 'banner',
		) ); ?>
	</div>

</section>


