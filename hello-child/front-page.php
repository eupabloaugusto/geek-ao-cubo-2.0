<?php
/**
4:  * Template Name: Página Inicial (Home)
5:  *
6:  * Template dinâmico para a página inicial (capa) do portal Modo Maratona / Geek ao Cubo.
7:  * Coordena a exibição das seções editoriais, carrosséis de episódios e a barra lateral.
8:  *
9:  * @package hello-elementor-child
10:  * @since   2.0.0
11:  */
12: 
13: if ( ! defined( 'ABSPATH' ) ) {
14: 	exit; // Exit if accessed directly.
15: }
16: 
17: get_header();
18: 
19: // Enfileira o estilo específico deste template
20: wp_enqueue_style(
21: 	'mm-style-front-page',
22: 	get_stylesheet_directory_uri() . '/front-page.css',
23: 	array( 'mm-design-tokens' ),
24: 	'1.0.0'
25: );
26: ?>
27: 
28: <div class="home-page">
29: 	
30: 	<!-- 1. SEÇÃO DE TOPO / HERO CARROSSEL (Task 1.2 — Destaques) -->
	<div class="home-page__hero-section">
		<?php 
		// Query para buscar os posts de Destaque
		$args_query = array(
			'post_type'      => 'post',
			'posts_per_page' => 4,
			'tax_query'      => array(
				'relation' => 'OR',
				array(
					'taxonomy' => 'category',
					'field'    => 'slug',
					'terms'    => 'destaque',
				),
				array(
					'taxonomy' => 'post_tag',
					'field'    => 'slug',
					'terms'    => 'destaque',
				),
			),
		);
		$query = new WP_Query( $args_query );
		
		// Fallback: se não encontrar com 'destaque', traz os últimos posts publicados
		if ( ! $query->have_posts() ) {
			$args_query_fallback = array(
				'post_type'      => 'post',
				'posts_per_page' => 4,
			);
			$query = new WP_Query( $args_query_fallback );
		}

		$posts_carousel = array();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$featured_img = get_the_post_thumbnail_url( get_the_ID(), 'large' );
				$categories = get_the_category();
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
			// Fallback visual estético caso o banco local esteja 100% zerado de posts
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
45: 
46: 	<!-- 2. LAYOUT EM DUAS COLUNAS (CONTEÚDO PRINCIPAL + SIDEBAR) -->
47: 	<div class="home-page__layout">
48: 		
49: 		<!-- A. COLUNA PRINCIPAL (Esquerda/Centro) -->
50: 		<main class="home-page__main" id="main-content">
51: 			
52: 			<!-- A1. ESTEIRA DE EPISÓDIOS (Task 1.4 — Novos Episódios) -->
53: 			<section class="home-section">
54: 				<h2 class="home-section__title">
55: 					<?php _e( 'Novos Episódios', 'hello-elementor-child' ); ?>
56: 					<span class="home-section__sub-badge"><?php _e( 'HOJE', 'hello-elementor-child' ); ?></span>
57: 				</h2>
58: 				
59: 				<div class="home-placeholder-episodes">
60: 					<div class="home-placeholder-card">
61: 						<div class="home-placeholder-card__image"><span>Episódio 5</span></div>
62: 						<div class="home-placeholder-card__title">Solo Leveling</div>
63: 						<div class="home-placeholder-card__status"><?php _e( 'Task 1.4 (Aguardando)', 'hello-elementor-child' ); ?></div>
64: 					</div>
65: 					<div class="home-placeholder-card">
66: 						<div class="home-placeholder-card__image"><span>Episódio 10</span></div>
67: 						<div class="home-placeholder-card__title">Chainsaw Man</div>
68: 						<div class="home-placeholder-card__status"><?php _e( 'Task 1.4 (Aguardando)', 'hello-elementor-child' ); ?></div>
69: 					</div>
70: 					<div class="home-placeholder-card">
71: 						<div class="home-placeholder-card__image"><span>Episódio 24</span></div>
72: 						<div class="home-placeholder-card__title">Frieren</div>
73: 						<div class="home-placeholder-card__status"><?php _e( 'Task 1.4 (Aguardando)', 'hello-elementor-child' ); ?></div>
74: 					</div>
75: 				</div>
76: 			</section>
77: 
78: 						<!-- A2. GRADE DE NOTÍCIAS RECENTES (Task 1.3 — Notícias) -->
			<section class="home-section" style="margin-top: var(--space-600); display: block;">
				<?php 
				// Evita duplicar os posts que já estão aparecendo no carrossel de destaques no topo
				$exclude_ids = array();
				if ( isset( $query->posts ) && is_array( $query->posts ) ) {
					$exclude_ids = wp_list_pluck( $query->posts, 'ID' );
				}

				$args_news = array(
					'post_type'      => 'post',
					'posts_per_page' => 4,
					'post__not_in'   => $exclude_ids,
				);
				$query_news = new WP_Query( $args_news );
				$noticias_list = array();

				if ( $query_news->have_posts() ) {
					while ( $query_news->have_posts() ) {
						$query_news->the_post();
						$featured_img = get_the_post_thumbnail_url( get_the_ID(), 'large' );
						$categories = get_the_category();
						$category_name = ! empty( $categories ) ? $categories[0]->name : __( 'Geral', 'hello-elementor-child' );

						$noticias_list[] = array(
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

				if ( ! empty( $noticias_list ) ) {
					mm_render_component( 'organisms', 'secao-noticias-recentes', array(
						'noticias'     => $noticias_list,
						'titulo'       => __( 'Últimas Novidades', 'hello-elementor-child' ),
						'ver_mais_url' => home_url( '/noticias/' ),
					) );
				} else {
					// Fallback visual estético caso o banco local não possua posts suficientes
					?>
					<h2 class="home-section__title"><?php _e( 'Últimas Novidades', 'hello-elementor-child' ); ?></h2>
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
93: 
94: 		</main>
95: 
96: 		<!-- B. COLUNA LATERAL / SIDEBAR -->
97: 		<div class="home-page__sidebar">
98: 			<?php 
99: 			// Renderiza o organismo da Sidebar já construído!
100: 			mm_render_component( 'organisms', 'sidebar', array(
101: 				'adsense_slot' => '9876543210'
102: 			) ); 
103: 			?>
104: 		</div>
105: 
106: 	</div>
107: 
108: </div>
109: 
110: <?php
111: get_footer();
112: 
