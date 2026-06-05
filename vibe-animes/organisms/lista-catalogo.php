<?php
/**
 * Organism: Lista do Catálogo (lista-catalogo)
 *
 * Exibe a listagem completa de animes do catálogo, agrupada por letra inicial.
 * Inclui a navegação alfabética sticky no topo e divisores de seção por letra.
 *
 * Filtragem:
 * - Lê $_GET['letra'] para exibir apenas uma letra por vez (ex: ?letra=M).
 * - Sem letra: exibe todos agrupados em seções A–Z.
 *
 * Cada anime exibe: thumbnail, título, sinopse e idioma (Legendado/Leg | Dub).
 * O idioma é lido do campo ACF 'anime_idioma'. Se vazio, não exibe.
 *
 * @package vibe-animes
 *
 * @param string $class         Classes CSS adicionais.
 * @param array  $query_extra   Args extras para mm_query_animes_por_letra() (override).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$class       = isset( $args['class'] )       ? esc_attr( $args['class'] )     : '';
$query_extra = isset( $args['query_extra'] ) ? (array) $args['query_extra']   : array();

// ── MODO EXTERNO: dados pré-formatados passados via $args ──
$grupos_externos = isset( $args['grupos'] ) ? (array) $args['grupos'] : null;
$letras_ativas   = isset( $args['letras_ativas'] ) ? (array) $args['letras_ativas'] : null;
$max_num_pages   = isset( $args['max_num_pages'] ) ? (int) $args['max_num_pages'] : 0;
$sel_tipo_midia  = isset( $_GET['tipo_midia'] ) ? sanitize_key( wp_unslash( $_GET['tipo_midia'] ) ) : '';
$aria_label      = isset( $args['aria_label'] ) ? esc_attr( $args['aria_label'] ) : ( ( 'manga' === $sel_tipo_midia ) ? __( 'Catálogo de mangás', 'vibe-animes' ) : __( 'Catálogo de animes', 'vibe-animes' ) );

// ── Tratamento de Busca (Custom parameter 'busca' para não conflitar com search.php) ──
$s_query = isset( $_GET['busca'] ) ? sanitize_text_field( wp_unslash( $_GET['busca'] ) ) : '';
if ( ! empty( $s_query ) ) {
	$query_extra['busca'] = $s_query;
}

// ── Parâmetro de letra via GET ────────────────────────────
$letra_get = isset( $_GET['letra'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['letra'] ) ) ) : '';

// Valida: apenas letras A–Z ou o caractere especial '#'
if ( ! preg_match( '/^[A-Z#]$/', $letra_get ) ) {
	$letra_get = '';
}

// ── URL base do catálogo ──────────────────────────────────
// Remove apenas paginação; preserva filtros ativos na navegação alfabética
$base_url = remove_query_arg( 'pg' );
$base_url_sem_letra = remove_query_arg( 'letra', $base_url );

// ── Parâmetros de Paginação ─────────────────────────────────
// Tentamos pegar via ?pg= para evitar o 404 da query nativa, senão usamos fallback
$paged = isset( $_GET['pg'] ) ? max( 1, (int) wp_unslash( $_GET['pg'] ) ) : 1;

if ( 1 === $paged ) {
	if ( get_query_var( 'paged' ) ) {
		$paged = get_query_var( 'paged' );
	} elseif ( get_query_var( 'page' ) ) {
		$paged = get_query_var( 'page' );
	}
}

// ── Agrupamento por Letra ─────────────────────────────────
$grupos = array(); // [ 'A' => [ post, post ], 'N' => [...] ]

if ( null !== $grupos_externos ) {
	// ── MODO EXTERNO: usa dados passados via $args ────────────
	$grupos = $grupos_externos;
	uksort( $grupos, function( $a, $b ) {
		if ( '#' === $a ) return -1;
		if ( '#' === $b ) return 1;
		return strcmp( $a, $b );
	} );
} else {
	// ── MODO INTERNO: query de Animes/Mangás ─────────────────
	$sel_generos    = isset( $_GET['genero'] )       ? array_map( 'sanitize_title', (array) wp_unslash( $_GET['genero'] ) )   : array();
	$sel_status     = isset( $_GET['status_anime'] ) ? sanitize_key( wp_unslash( $_GET['status_anime'] ) )                    : '';
	$sel_idioma     = isset( $_GET['idioma'] )       ? sanitize_text_field( wp_unslash( $_GET['idioma'] ) )                   : '';
	$sel_tipo_midia = isset( $_GET['tipo_midia'] )   ? sanitize_key( wp_unslash( $_GET['tipo_midia'] ) )                      : '';
	$sel_ordem      = isset( $_GET['ordem'] )        ? sanitize_key( wp_unslash( $_GET['ordem'] ) )                           : '';

	// ── Descobre quais letras têm animes (para nav desabilitada) ──────────────
	if ( null === $letras_ativas ) {
		$target_post_type = ( isset( $sel_tipo_midia ) && 'manga' === $sel_tipo_midia ) ? 'manga' : 'anime';
		$letras_ativas = mm_get_letras_ativas_catalogo( $target_post_type );
	}

	// ── Query de Animes ───────────────────────────────────────
	$query_filter_args = array(
		'posts_per_page'    => 24,
		'paged'             => $paged,
		'filtro_generos'    => $sel_generos,
		'filtro_status'     => $sel_status,
		'filtro_idioma'     => $sel_idioma,
		'filtro_tipo_midia' => $sel_tipo_midia,
		'filtro_ordem'      => $sel_ordem,
		'busca'             => $s_query,
	);

	$query = mm_query_animes_por_letra( $letra_get, $query_filter_args );

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();

			$post_id         = get_the_ID();
			$titulo          = get_the_title();
			$inicial         = strtoupper( mb_substr( $titulo, 0, 1 ) );
			$post_type_atual = get_post_type( $post_id );

			// Letras não-alfabéticas agrupadas em '#'
			if ( ! preg_match( '/[A-Z]/', $inicial ) ) {
				$inicial = '#';
			}

			// ── Imagem e Sinopse: comportamento diferente para Anime vs Mangá ────────
			$imagem  = get_the_post_thumbnail_url( $post_id, 'large' );
			$sinopse = '';

			if ( 'manga' === $post_type_atual ) {
				// Mangá: imagem e sinopse vêm do Jikan cache (jikan_manga_full_{id})
				$manga_mal_id = (int) get_post_meta( $post_id, 'manga_id_mal', true );
				$manga_cache  = $manga_mal_id ? get_transient( 'jikan_manga_full_' . $manga_mal_id ) : false;

				if ( ! $manga_cache && $manga_mal_id && class_exists( 'Jikan_API' ) ) {
					$manga_cache = Jikan_API::get_manga_full( $manga_mal_id );
				}

				if ( empty( $imagem ) && $manga_cache ) {
					$imagem = $manga_cache['images']['webp']['large_image_url']
						?? $manga_cache['images']['jpg']['large_image_url']
						?? '';
				}

				$sinopse_manual = get_field( 'manga_sinopse_manual', $post_id );
				if ( ! empty( $sinopse_manual ) ) {
					$sinopse = $sinopse_manual;
				} elseif ( $manga_cache && ! empty( $manga_cache['synopsis'] ) ) {
					$sinopse = $manga_cache['synopsis'];
				} else {
					$sinopse = get_the_excerpt();
				}
			} else {
				// Anime: ACF → Jikan cache → excerpt
				if ( empty( $imagem ) ) {
					$imagem = get_field( 'anime_imagem_capa_url', $post_id );
				}
				$sinopse_manual = get_field( 'anime_sinopse', $post_id );
				if ( ! empty( $sinopse_manual ) ) {
					$sinopse = $sinopse_manual;
				} else {
					$anime_mal_id = (int) get_post_meta( $post_id, 'anime_id_mal', true );
					if ( $anime_mal_id ) {
						$anime_jikan = get_transient( 'jikan_anime_full_' . $anime_mal_id );
						if ( $anime_jikan && ! empty( $anime_jikan['synopsis'] ) ) {
							$sinopse = $anime_jikan['synopsis'];
						}
					}
					if ( empty( $sinopse ) ) {
						$sinopse = get_the_excerpt();
					}
				}
			}

			$idioma_raw = get_field( 'anime_idioma', $post_id );
			if ( empty( $idioma_raw ) ) {
				$idioma_raw = get_post_meta( $post_id, 'anime_idioma', true );
			}
			$idioma     = '';
			$idioma_slug = '';
			if ( ! empty( $idioma_raw ) ) {
				if ( is_array( $idioma_raw ) ) {
					$idioma_raw = reset( $idioma_raw );
				}
				$idioma_slug = sanitize_key( $idioma_raw );
				$idioma_labels = array(
					'legendado' => __( 'Legendado', 'vibe-animes' ),
					'dublado'   => __( 'Dublado', 'vibe-animes' ),
				);
				$idioma = isset( $idioma_labels[ $idioma_slug ] ) ? $idioma_labels[ $idioma_slug ] : $idioma_raw;
			}

			// Extrai os termos de gênero (WP taxonomy)
			$termos_generos = get_the_terms( $post_id, 'genero' );
			$lista_generos  = array();
			if ( $termos_generos && ! is_wp_error( $termos_generos ) ) {
				foreach ( $termos_generos as $termo ) {
					$lista_generos[] = $termo->name;
				}
			}
			// Fallback para mangás: busca gêneros no cache Jikan se a taxonomia WP estiver vazia
			if ( empty( $lista_generos ) && 'manga' === $post_type_atual ) {
				$_manga_mal_id = (int) get_post_meta( $post_id, 'manga_id_mal', true );
				if ( $_manga_mal_id ) {
					$_mc = isset( $manga_cache ) ? $manga_cache : get_transient( 'jikan_manga_full_' . $_manga_mal_id );
					if ( $_mc && ! empty( $_mc['genres'] ) && class_exists( 'Jikan_API' ) ) {
						foreach ( $_mc['genres'] as $_g ) {
							if ( ! empty( $_g['name'] ) ) {
								$lista_generos[] = Jikan_API::translate_genre( $_g['name'] );
							}
						}
					}
				}
			}

			$grupos[ $inicial ][] = array(
				'post_id'    => $post_id,
				'titulo'     => $titulo,
				'url'        => get_permalink(),
				'imagem'     => esc_url( $imagem ?: '' ),
				'banner_url' => esc_url( $imagem ?: '' ),
				'sinopse'    => $sinopse,
				'idioma'      => $idioma,
				'idioma_slug' => $idioma_slug,
				'generos'    => $lista_generos,
			);
		}
		wp_reset_postdata();
	}

	// Ordena os grupos alfabeticamente (# sempre primeiro)
	uksort( $grupos, function( $a, $b ) {
		if ( '#' === $a ) return -1;
		if ( '#' === $b ) return 1;
		return strcmp( $a, $b );
	} );

	if ( null === $letras_ativas ) {
		$target_post_type = ( isset( $sel_tipo_midia ) && 'manga' === $sel_tipo_midia ) ? 'manga' : 'anime';
		$letras_ativas = mm_get_letras_ativas_catalogo( $target_post_type );
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

	<!-- Lista de Animes -->
	<div class="lista-catalogo__corpo js-ajax-replace">
		<?php if ( empty( $grupos ) ) : ?>
			<div class="lista-catalogo__vazio">
				<p class="lista-catalogo__vazio-msg">
					<?php
					// Monta uma mensagem que reflete o estado real dos filtros
					$has_busca   = ! empty( $s_query );
					$has_letra   = ! empty( $letra_get );
					$has_filtros = ! empty( $sel_generos ) || ! empty( $sel_status ) || ! empty( $sel_idioma ) || ! empty( $sel_tipo_midia );

					if ( $has_busca ) {
						echo esc_html( sprintf(
							__( 'Nenhum anime encontrado para "%s".', 'vibe-animes' ),
							$s_query
						) );
					} elseif ( $has_letra && $has_filtros ) {
						echo esc_html( sprintf(
							__( 'Nenhum anime encontrado com a letra "%s" e os filtros selecionados.', 'vibe-animes' ),
							$letra_get
						) );
					} elseif ( $has_letra ) {
						echo esc_html( sprintf(
							__( 'Nenhum anime encontrado com a letra "%s".', 'vibe-animes' ),
							$letra_get
						) );
					} elseif ( $has_filtros ) {
						esc_html_e( 'Nenhum anime encontrado com os filtros selecionados. Tente ampliar a busca.', 'vibe-animes' );
					} else {
						esc_html_e( 'Nenhum anime cadastrado ainda.', 'vibe-animes' );
					}
					?>
				</p>
			</div>

		<?php else : ?>
			<?php 
			$global_card_index = 0; // Contador global de cards para injeção de anúncio
			foreach ( $grupos as $letra => $animes ) : 
			?>

				<!-- Separador de letra -->
				<?php mm_render_component( 'atoms', 'separador-letra', array(
					'letra' => $letra,
					'id'    => 'secao-' . strtolower( '#' === $letra ? 'num' : $letra ),
				) ); ?>

				<!-- Cards do grupo -->
				<div class="lista-catalogo__grupo" role="list" aria-label="<?php echo esc_attr( sprintf( __( 'Animes com %s', 'vibe-animes' ), $letra ) ); ?>">
					<?php foreach ( $animes as $anime ) : ?>
						
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
								'titulo'     => $anime['titulo'],
								'url'        => $anime['url'],
								'imagem_url' => $anime['imagem'] ?? '',
								'banner_url' => $anime['banner_url'] ?? '',
								'post_id'    => $anime['post_id'] ?? 0,
								'sinopse'    => $anime['sinopse'] ?? '',
								'idioma'     => $anime['idioma'] ?? '',
								'idioma_slug'=> $anime['idioma_slug'] ?? '',
								'generos'    => $anime['generos'] ?? array(),
							) ); ?>
						</div>
					<?php endforeach; ?>
				</div>

			<?php endforeach; ?>
		<?php endif; ?>
	</div>

	<!-- Paginação -->
	<?php
	$_max_pages = $max_num_pages > 0 ? $max_num_pages : ( isset( $query ) ? $query->max_num_pages : 0 );
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
			'slot'     => 'catalogo-bottom-banner',
			'variacao' => 'banner',
		) ); ?>
	</div>

</section>

