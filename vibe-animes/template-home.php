<?php
/**
 * Template Name: Página Inicial (Home)
 *
 * Template dinâmico para a página inicial (capa) do portal Geek ao Cubo.
 * Coordena a exibição das seções editoriais, carrosséis de episódios e a barra lateral.
 *
 * @package geek-ao-cubo
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

?>

<div class="home-page">

	<!-- AD: Banner full-width abaixo do header -->
	<?php mm_render_component( 'atoms', 'anuncio-adsense', array(
		'slot'    => 'home-banner-top',
		'variacao' => 'banner',
	) ); ?>

	<!-- 1. SEÇÃO DE TOPO / HERO CARROSSEL (Task 1.2 — Destaques) -->
	<div class="home-page__hero-section">
		<?php
		// Query centralizada para buscar os posts de Destaque
		$query_carousel = mm_query_posts_destaque( 4 );
		$posts_carousel = array();
		$exclude_ids    = array();

		if ( $query_carousel->have_posts() ) {
			while ( $query_carousel->have_posts() ) {
				$query_carousel->the_post();
				$exclude_ids[] = get_the_ID(); // Coleta os IDs para evitar duplicidade posterior!
				
				$featured_img  = get_the_post_thumbnail_url( get_the_ID(), 'large' );
				$categories    = get_the_category();
				$category_name = ! empty( $categories ) ? $categories[0]->name : __( 'Geral', 'geek-ao-cubo' );

				$posts_carousel[] = array(
					'titulo'     => get_the_title(),
					'url'        => get_permalink(),
					'imagem_url' => $featured_img,
					'categoria'  => $category_name,
					'autor'      => get_the_author(),
					'data'       => get_the_date(),
					'resumo'     => get_the_excerpt(),
				);
			}
			wp_reset_postdata();
		}

		if ( ! empty( $posts_carousel ) ) {
			mm_render_component( 'organisms', 'secao-carrossel-destaque', array(
				'posts_carousel' => $posts_carousel,
			) );
		} else {
			// Fallback visual estético caso o banco esteja vazio
			mm_render_component( 'molecules', 'home-placeholder-carousel' );
		}
		?>
	</div>

	<!-- 2. LAYOUT EM DUAS COLUNAS: CONTEÚDO PRINCIPAL + SIDEBAR -->
	<div class="home-page__layout">
		
		<!-- A. COLUNA PRINCIPAL (Esquerda) -->
		<main class="home-page__main" id="main-content">
			
			<?php
				// Obtém o cronograma de animes de hoje via Jikan API Cache
				$schedule_today = Jikan_API::get_schedules_today();
				$animes_list_eps = array();
				$added_mal_ids = array();

				if ( ! empty( $schedule_today ) ) {
					foreach ( $schedule_today as $anime_data ) {
						$mal_id = $anime_data['mal_id'] ?? 0;
						if ( ! $mal_id || in_array( $mal_id, $added_mal_ids, true ) ) {
							continue;
						}
						
						// Tenta buscar localmente para ver se temos no catálogo e pegar a URL do post local
						$local_data = mm_get_local_anime_by_mal_id( $mal_id );
						
						if ( $local_data ) {
							$added_mal_ids[] = $mal_id;
							
							$featured_img = $local_data['image'] ?: ( $anime_data['images']['webp']['large_image_url'] ?? $anime_data['images']['jpg']['large_image_url'] ?? '' );
							
							$generos_mapped = array();
							if ( ! empty( $anime_data['genres'] ) ) {
								foreach ( $anime_data['genres'] as $gen ) {
									$generos_mapped[] = array(
										'name' => Jikan_API::translate_genre( $gen['name'] ),
										'url'  => '#',
									);
								}
							}
							
							// Horário de exibição
							$horario_str = '18:00'; // Default fallback
							$horario_utc = '';
							if ( ! empty( $anime_data['broadcast']['time'] ) ) {
								try {
									// Jikan broadcast time is generally JST (UTC+9)
									$dt = new DateTime( '2000-01-01 ' . $anime_data['broadcast']['time'], new DateTimeZone( 'Asia/Tokyo' ) );
									
									// Gerar ISO 8601 UTC string para o JavaScript
									$dt_utc = clone $dt;
									$dt_utc->setTimezone( new DateTimeZone( 'UTC' ) );
									$horario_utc = $dt_utc->format( 'Y-m-d\TH:i:s\Z' );
									
									// Fallback estático (antes do JS rodar)
									$horario_str = $dt->format( 'H:i' );
								} catch ( Exception $e ) {
									// keep fallback
								}
							}

							$animes_list_eps[] = array(
								'titulo'     => $anime_data['title'],
								'url'        => $local_data['url'],
								'imagem_url' => $featured_img,
								'nota'       => ! empty( $anime_data['score'] ) ? number_format( (float) $anime_data['score'], 2 ) : '0.00',
								'horario'    => $horario_str,
								'horario_utc'=> $horario_utc,
								'generos'    => $generos_mapped,
							);
						}
					}
				}

				// Fallback: se não encontrar episódios cadastrados, traz os últimos animes do catálogo
				if ( empty( $animes_list_eps ) ) {
					$args_animes_fallback = array(
						'post_type'      => 'anime',
						'posts_per_page' => 6,
					);
					$query_animes = new WP_Query( $args_animes_fallback );
					if ( $query_animes->have_posts() ) {
						while ( $query_animes->have_posts() ) {
							$query_animes->the_post();
							$a_id = get_the_ID();
							$mal_id = (int) get_field( 'anime_id_mal', $a_id );
							$jikan_data = $mal_id > 0 ? Jikan_API::get_anime_full( $mal_id ) : array();
							
							$featured_img = $jikan_data['images']['webp']['large_image_url'] ?? ( $jikan_data['images']['jpg']['large_image_url'] ?? '' );
							if ( empty( $featured_img ) ) {
								$featured_img = get_the_post_thumbnail_url( $a_id, 'large' );
							}
							
							$generos_mapped = array();
							if ( ! empty( $jikan_data['genres'] ) ) {
								foreach ( $jikan_data['genres'] as $gen ) {
									$generos_mapped[] = array(
										'name' => Jikan_API::translate_genre( $gen['name'] ),
										'url'  => '#',
									);
								}
							}
							
							$animes_list_eps[] = array(
								'titulo'     => get_the_title(),
								'url'        => get_permalink(),
								'imagem_url' => $featured_img,
								'nota'       => ! empty( $jikan_data['score'] ) ? number_format( (float) $jikan_data['score'], 2 ) : '0.00',
								'horario'    => '14:00',
								'horario_utc'=> '',
								'generos'    => $generos_mapped,
							);
						}
						wp_reset_postdata();
					}
				}

				if ( ! empty( $animes_list_eps ) ) {
					mm_render_component( 'organisms', 'secao-novos-episodios', array(
						'animes' => $animes_list_eps,
					) );
				} else {
					// Fallback visual estético absoluto caso o acervo esteja vazio
					mm_render_component( 'organisms', 'secao-titulo', array(
						'titulo'    => __( 'Novos Episódios', 'geek-ao-cubo' ),
						'sub_badge' => __( 'HOJE', 'geek-ao-cubo' ),
					) );
					mm_render_component( 'molecules', 'home-placeholder-episodes' );
				}
				?>
			</section>

			<!-- AD: Leaderboard após Novos Episódios -->
			<?php mm_render_component( 'atoms', 'anuncio-adsense', array(
				'slot'    => 'home-leaderboard-1',
				'variacao' => 'leaderboard',
			) ); ?>

			<!-- A2. ÚLTIMAS NOTÍCIAS (secao-noticias-recentes) -->
			<section class="home-section">
				<?php
				// Busca 5 notícias recentes excluindo as que já aparecem no topo
				$query_news   = mm_query_noticias_recentes( 5, $exclude_ids );
				$noticias_a2  = array();

				if ( $query_news->have_posts() ) {
					while ( $query_news->have_posts() ) {
						$query_news->the_post();
						$n_id          = get_the_ID();
						$exclude_ids[] = $n_id; // Acumula IDs para evitar duplicidade na seção A4

						$categories    = get_the_category();
						$category_name = ! empty( $categories ) ? $categories[0]->name : __( 'Geral', 'geek-ao-cubo' );

						$noticias_a2[] = array(
							'titulo'     => get_the_title(),
							'url'        => get_permalink(),
							'imagem_url' => get_the_post_thumbnail_url( $n_id, 'large' ),
							'categoria'  => $category_name,
							'autor'      => get_the_author(),
							'data'       => get_the_date(),
							'resumo'     => get_the_excerpt(),
						);
					}
					wp_reset_postdata();
				}

				if ( ! empty( $noticias_a2 ) ) {
					mm_render_component( 'organisms', 'secao-noticias-recentes', array(
						'titulo'  => __( 'Últimas notícias', 'geek-ao-cubo' ),
						'noticias' => $noticias_a2,
					) );
				}
				?>
			</section>

			<!-- A3. ESTEIRA DE ANIMES: ANIMES DA TEMPORADA (secao-esteira-animes) -->
			<section class="home-section">
				<?php 
				// Busca até 10 animes atualmente em exibição
				$args_season = array(
					'per_page' => 10,
					'status'   => array( 'airing', 'em-exibicao' ),
				);
				$query_season = mm_query_animes( $args_season );

				// Fallback caso não haja animes marcados como 'airing'
				if ( ! $query_season->have_posts() ) {
					$query_season = mm_query_animes( array( 'per_page' => 10 ) );
				}

				$animes_season_list = array();
				if ( $query_season->have_posts() ) {
					while ( $query_season->have_posts() ) {
						$query_season->the_post();
						$a_id = get_the_ID();
						
						$mal_id = (int) get_field( 'anime_id_mal', $a_id );
						$jikan_data = $mal_id > 0 ? Jikan_API::get_anime_full( $mal_id ) : array();

						$featured_img = $jikan_data['images']['webp']['large_image_url'] ?? ( $jikan_data['images']['jpg']['large_image_url'] ?? '' );
						if ( empty( $featured_img ) ) {
							$featured_img = get_the_post_thumbnail_url( $a_id, 'large' );
						}
						
						$generos_mapped = array();
						if ( ! empty( $jikan_data['genres'] ) ) {
							foreach ( $jikan_data['genres'] as $gen ) {
								$generos_mapped[] = array(
									'name' => Jikan_API::translate_genre( $gen['name'] ),
									'url'  => '#',
								);
							}
						}
						
						$animes_season_list[] = array(
							'titulo'     => get_the_title(),
							'url'        => get_permalink(),
							'imagem_url' => $featured_img,
							'nota'       => ! empty( $jikan_data['score'] ) ? number_format( (float) $jikan_data['score'], 2 ) : '0.00',
							'horario'    => '',
							'generos'    => $generos_mapped,
						);
					}
					wp_reset_postdata();
				}

				if ( ! empty( $animes_season_list ) ) {
					mm_render_component( 'organisms', 'secao-esteira-animes', array(
						'titulo_secao'  => __( 'Animes da Temporada', 'geek-ao-cubo' ),
						'url_ver_todos' => home_url( '/animes/' ),
						'animes'        => $animes_season_list,
					) );
				}
				?>
			</section>

			<!-- AD: Leaderboard após Animes da Temporada -->
			<?php mm_render_component( 'atoms', 'anuncio-adsense', array(
				'slot'    => 'home-leaderboard-2',
				'variacao' => 'leaderboard',
			) ); ?>

			<!-- A4. VEJA TAMBÉM (secao-noticias-recentes) -->
			<section class="home-section">
				<?php
				// Busca mais 5 notícias excluindo TODOS os posts já exibidos acima (duplicidade zero)
				$query_see_also = mm_query_noticias_recentes( 5, $exclude_ids );
				$noticias_a4    = array();

				if ( $query_see_also->have_posts() ) {
					while ( $query_see_also->have_posts() ) {
						$query_see_also->the_post();
						$n_id          = get_the_ID();

						$categories    = get_the_category();
						$category_name = ! empty( $categories ) ? $categories[0]->name : __( 'Geral', 'geek-ao-cubo' );

						$noticias_a4[] = array(
							'titulo'     => get_the_title(),
							'url'        => get_permalink(),
							'imagem_url' => get_the_post_thumbnail_url( $n_id, 'large' ),
							'categoria'  => $category_name,
							'autor'      => get_the_author(),
							'data'       => get_the_date(),
							'resumo'     => get_the_excerpt(),
						);
					}
					wp_reset_postdata();
				}

				if ( ! empty( $noticias_a4 ) ) {
					mm_render_component( 'organisms', 'secao-noticias-recentes', array(
						'titulo'   => __( 'Veja também', 'geek-ao-cubo' ),
						'noticias' => $noticias_a4,
					) );
				}
				?>
			</section>

		</main>

		<!-- B. COLUNA DA SIDEBAR (Direita - Sticky no Desktop) -->
		<aside class="home-page__sidebar">
			<?php 
			// Renderiza o organismo da Sidebar já construído e homologado!
			mm_render_component( 'organisms', 'sidebar', array(
				'adsense_slot' => '9876543210'
			) ); 
			?>
		</aside>

	</div>

</div>

<?php
get_footer();
