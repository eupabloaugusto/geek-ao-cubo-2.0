<?php
/**
 * Template Name: Página Inicial (Home)
 *
 * Template dinâmico para a página inicial (capa) do portal Geek ao Cubo.
 * Coordena a exibição das seções editoriais, carrosséis de episódios e a barra lateral.
 *
 * @package hello-elementor-child
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

?>

<div class="home-page">

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
				$category_name = ! empty( $categories ) ? $categories[0]->name : __( 'Geral', 'hello-elementor-child' );

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
			?>
			<div class="home-placeholder-carousel">
				<div class="home-placeholder-carousel__slide">
					<div class="home-placeholder-carousel__badge"><?php _e( 'EM DESTAQUE', 'hello-elementor-child' ); ?></div>
					<h2 class="home-placeholder-carousel__title"><?php _e( 'Nenhum artigo encontrado no banco de dados local.', 'hello-elementor-child' ); ?></h2>
					<p class="home-placeholder-carousel__desc"><?php _e( 'Por favor, execute o script de sincronização ou crie alguns posts de teste no seu painel administrativo local do WordPress.', 'hello-elementor-child' ); ?></p>
					<span class="home-placeholder-carousel__meta"><?php _e( 'Sem posts para exibir', 'hello-elementor-child' ); ?></span>
				</div>
			</div>
			<?php
		}
		?>
	</div>

	<!-- 2. LAYOUT EM DUAS COLUNAS: CONTEÚDO PRINCIPAL + SIDEBAR -->
	<div class="home-page__layout">
		
		<!-- A. COLUNA PRINCIPAL (Esquerda) -->
		<main class="home-page__main" id="main-content">
			
			<!-- A1. ESTEIRA DE EPISÓDIOS (Task 1.4 — Novos Episódios) -->
			<section class="home-section">
				<?php 
				// Busca os últimos 10 episódios para mapear os animes correspondentes
				$args_eps = array(
					'post_type'      => 'episodio',
					'posts_per_page' => 10,
					'orderby'        => 'date',
					'order'          => 'DESC',
				);
				$query_eps = new WP_Query( $args_eps );
				$animes_list_eps = array();
				$added_anime_ids = array();

				if ( $query_eps->have_posts() ) {
					while ( $query_eps->have_posts() ) {
						$query_eps->the_post();
						$anime_rel = get_field( 'ep_anime_relacionado' );
						if ( ! empty( $anime_rel ) ) {
							$anime_post = is_array( $anime_rel ) ? $anime_rel[0] : $anime_rel;
							$anime_post_id = is_object( $anime_post ) ? $anime_post->ID : (int) $anime_post;

							if ( ! in_array( $anime_post_id, $added_anime_ids, true ) ) {
								$added_anime_ids[] = $anime_post_id;
								
								$featured_img = get_the_post_thumbnail_url( $anime_post_id, 'large' );
								if ( empty( $featured_img ) ) {
									$featured_img = get_field( 'anime_imagem_capa_url', $anime_post_id );
								}

								$terms_genero = get_the_terms( $anime_post_id, 'genero' );
								$generos_mapped = array();
								if ( ! empty( $terms_genero ) && ! is_wp_error( $terms_genero ) ) {
									foreach ( $terms_genero as $term ) {
										$generos_mapped[] = array(
											'name' => $term->name,
											'url'  => get_term_link( $term ),
										);
									}
								}

								// Pega o horário de exibição do anime ou do lançamento do episódio (UTC)
								$horario_str = get_field( 'anime_horario_exibicao', $anime_post_id );
								if ( empty( $horario_str ) ) {
									$ep_date = get_field( 'ep_data_lancamento' );
									if ( ! empty( $ep_date ) ) {
										$horario_str = date( 'H:i', strtotime( $ep_date ) );
									} else {
										$horario_str = '18:00';
									}
								}

								$animes_list_eps[] = array(
									'titulo'     => get_the_title( $anime_post_id ),
									'url'        => get_permalink( $anime_post_id ),
									'imagem_url' => $featured_img,
									'nota'       => get_field( 'anime_nota_mal', $anime_post_id ) ? number_format( (float) get_field( 'anime_nota_mal', $anime_post_id ), 2 ) : '0.00',
									'horario'    => $horario_str,
									'generos'    => $generos_mapped,
								);
							}
						}
					}
					wp_reset_postdata();
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
							$featured_img = get_the_post_thumbnail_url( $a_id, 'large' );
							if ( empty( $featured_img ) ) {
								$featured_img = get_field( 'anime_imagem_capa_url', $a_id );
							}
							$terms_genero = get_the_terms( $a_id, 'genero' );
							$generos_mapped = array();
							if ( ! empty( $terms_genero ) && ! is_wp_error( $terms_genero ) ) {
								foreach ( $terms_genero as $term ) {
									$generos_mapped[] = array(
										'name' => $term->name,
										'url'  => get_term_link( $term ),
									);
								}
							}
							$animes_list_eps[] = array(
								'titulo'     => get_the_title(),
								'url'        => get_permalink(),
								'imagem_url' => $featured_img,
								'nota'       => get_field( 'anime_nota_mal', $a_id ) ? number_format( (float) get_field( 'anime_nota_mal', $a_id ), 2 ) : '0.00',
								'horario'    => '14:00',
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
					?>
					<h2 class="home-section__title">
						<?php _e( 'Novos Episódios', 'hello-elementor-child' ); ?>
						<span class="home-section__sub-badge"><?php _e( 'HOJE', 'hello-elementor-child' ); ?></span>
					</h2>
					<div class="home-placeholder-episodes">
						<div class="home-placeholder-card">
							<div class="home-placeholder-card__image"><span>?</span></div>
							<div class="home-placeholder-card__title"><?php _e( 'Aguardando Importação', 'hello-elementor-child' ); ?></div>
							<div class="home-placeholder-card__status"><?php _e( 'Sem novos episódios', 'hello-elementor-child' ); ?></div>
						</div>
					</div>
					<?php
				}
				?>
			</section>

			<!-- A2. SEÇÃO DESTAQUE: ÚLTIMAS NOTÍCIAS (secao-destaque) -->
			<section class="home-section" style="margin-top: var(--space-600);">
				<h2 class="home-section__title"><?php _e( 'Últimas notícias', 'hello-elementor-child' ); ?></h2>
				<?php 
				// Busca 5 notícias recentes excluindo as que já aparecem no topo
				$query_news = mm_query_noticias_recentes( 5, $exclude_ids );
				$post_hero_news = array();
				$posts_grid_news = array();

				if ( $query_news->have_posts() ) {
					$count = 0;
					while ( $query_news->have_posts() ) {
						$query_news->the_post();
						$n_id = get_the_ID();
						$exclude_ids[] = $n_id; // Adiciona aos excluídos para a próxima seção "Veja também"
						
						$featured_img  = get_the_post_thumbnail_url( $n_id, 'large' );
						$categories    = get_the_category();
						$category_name = ! empty( $categories ) ? $categories[0]->name : __( 'Geral', 'hello-elementor-child' );

						$post_item = array(
							'titulo'     => get_the_title(),
							'url'        => get_permalink(),
							'imagem_url' => $featured_img,
							'categoria'  => $category_name,
							'autor'      => get_the_author(),
							'data'       => get_the_date(),
							'resumo'     => get_the_excerpt(),
						);

						if ( $count === 0 ) {
							$post_hero_news = $post_item;
						} else {
							$posts_grid_news[] = $post_item;
						}
						$count++;
					}
					wp_reset_postdata();
				}

				if ( ! empty( $post_hero_news ) ) {
					mm_render_component( 'organisms', 'secao-destaque', array(
						'post_hero'  => $post_hero_news,
						'posts_grid' => $posts_grid_news,
					) );
				} else {
					// Fallback caso não haja notícias no banco local
					?>
					<div class="home-placeholder-news">
						<div class="home-placeholder-news-hero">
							<div class="home-placeholder-news-hero__image"></div>
							<div class="home-placeholder-news-hero__content">
								<h3 class="home-placeholder-news-hero__title"><?php _e( 'Nenhuma notícia adicional encontrada no banco de dados local.', 'hello-elementor-child' ); ?></h3>
								<p class="home-placeholder-news-hero__desc"><?php _e( 'Crie mais alguns posts no seu painel administrativo local para ver o layout editorial em ação!', 'hello-elementor-child' ); ?></p>
								<span class="home-placeholder-news-hero__status"><?php _e( 'Aguardando mais posts', 'hello-elementor-child' ); ?></span>
							</div>
						</div>
					</div>
					<?php
				}
				?>
			</section>

			<!-- A3. ESTEIRA DE ANIMES: ANIMES DA TEMPORADA (secao-esteira-animes) -->
			<section class="home-section" style="margin-top: var(--space-600);">
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
						
						$featured_img = get_the_post_thumbnail_url( $a_id, 'large' );
						if ( empty( $featured_img ) ) {
							$featured_img = get_field( 'anime_imagem_capa_url', $a_id );
						}
						
						$terms_genero = get_the_terms( $a_id, 'genero' );
						$generos_mapped = array();
						if ( ! empty( $terms_genero ) && ! is_wp_error( $terms_genero ) ) {
							foreach ( $terms_genero as $term ) {
								$generos_mapped[] = array(
									'name' => $term->name,
									'url'  => get_term_link( $term ),
								);
							}
						}
						
						$animes_season_list[] = array(
							'titulo'     => get_the_title(),
							'url'        => get_permalink(),
							'imagem_url' => $featured_img,
							'nota'       => get_field( 'anime_nota_mal', $a_id ) ? number_format( (float) get_field( 'anime_nota_mal', $a_id ), 2 ) : '0.00',
							'horario'    => '',
							'generos'    => $generos_mapped,
						);
					}
					wp_reset_postdata();
				}

				if ( ! empty( $animes_season_list ) ) {
					mm_render_component( 'organisms', 'secao-esteira-animes', array(
						'titulo_secao'  => __( 'Animes da Temporada', 'hello-elementor-child' ),
						'url_ver_todos' => home_url( '/animes/' ),
						'animes'        => $animes_season_list,
					) );
				}
				?>
			</section>

			<!-- A4. SEÇÃO DESTAQUE: VEJA TAMBÉM (secao-destaque) -->
			<section class="home-section" style="margin-top: var(--space-600);">
				<h2 class="home-section__title"><?php _e( 'Veja também', 'hello-elementor-child' ); ?></h2>
				<?php 
				// Busca mais 5 notícias excluindo TODOS os posts que já apareceram acima na home (duplicidade zero)
				$query_see_also = mm_query_noticias_recentes( 5, $exclude_ids );
				$post_hero_see_also = array();
				$posts_grid_see_also = array();

				if ( $query_see_also->have_posts() ) {
					$count = 0;
					while ( $query_see_also->have_posts() ) {
						$query_see_also->the_post();
						$n_id = get_the_ID();
						
						$featured_img  = get_the_post_thumbnail_url( $n_id, 'large' );
						$categories    = get_the_category();
						$category_name = ! empty( $categories ) ? $categories[0]->name : __( 'Geral', 'hello-elementor-child' );

						$post_item = array(
							'titulo'     => get_the_title(),
							'url'        => get_permalink(),
							'imagem_url' => $featured_img,
							'categoria'  => $category_name,
							'autor'      => get_the_author(),
							'data'       => get_the_date(),
							'resumo'     => get_the_excerpt(),
						);

						if ( $count === 0 ) {
							$post_hero_see_also = $post_item;
						} else {
							$posts_grid_see_also[] = $post_item;
						}
						$count++;
					}
					wp_reset_postdata();
				}

				if ( ! empty( $post_hero_see_also ) ) {
					mm_render_component( 'organisms', 'secao-destaque', array(
						'post_hero'  => $post_hero_see_also,
						'posts_grid' => $posts_grid_see_also,
					) );
				} else {
					// Fallback visual estético caso o banco local de notícias esteja esgotado
					?>
					<div class="home-placeholder-news">
						<div class="home-placeholder-news-hero">
							<div class="home-placeholder-news-hero__image"></div>
							<div class="home-placeholder-news-hero__content">
								<h3 class="home-placeholder-news-hero__title"><?php _e( 'Nenhuma notícia adicional encontrada.', 'hello-elementor-child' ); ?></h3>
								<span class="home-placeholder-news-hero__status"><?php _e( 'Sem mais posts', 'hello-elementor-child' ); ?></span>
							</div>
						</div>
					</div>
					<?php
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
