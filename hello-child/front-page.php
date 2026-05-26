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
31: 	<div class="home-page__hero-section">
32: 		<?php 
33: 		// Nota: Quando implementarmos a Task 1.2, renderizaremos o secao-carrossel-destaque de forma dinâmica.
34: 		// Por enquanto, renderizamos um placeholder elegante seguindo o design system.
35: 		?>
36: 		<div class="home-placeholder-carousel">
37: 			<div class="home-placeholder-carousel__slide">
38: 				<div class="home-placeholder-carousel__badge"><?php _e( 'EM DESTAQUE', 'hello-elementor-child' ); ?></div>
39: 				<h2 class="home-placeholder-carousel__title"><?php _e( 'Frieren e a Jornada Além do Fim: Por que este anime é uma obra-prima?', 'hello-elementor-child' ); ?></h2>
40: 				<p class="home-placeholder-carousel__desc"><?php _e( 'Uma análise profunda sobre a narrativa, ritmo e a brilhante melancolia poética da obra da Madhouse que conquistou o topo do MyAnimeList.', 'hello-elementor-child' ); ?></p>
41: 				<span class="home-placeholder-carousel__meta"><?php _e( 'Task 1.2 — Carrossel de Destaques (Aguardando ativação)', 'hello-elementor-child' ); ?></span>
42: 			</div>
43: 		</div>
44: 	</div>
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
78: 			<!-- A2. GRADE DE NOTÍCIAS RECENTES (Task 1.3 — Notícias) -->
79: 			<section class="home-section" style="margin-top: var(--space-600);">
80: 				<h2 class="home-section__title"><?php _e( 'Últimas Novidades', 'hello-elementor-child' ); ?></h2>
81: 				
82: 				<div class="home-placeholder-news">
83: 					<div class="home-placeholder-news-hero">
84: 						<div class="home-placeholder-news-hero__image"></div>
85: 						<div class="home-placeholder-news-hero__content">
86: 							<h3 class="home-placeholder-news-hero__title"><?php _e( 'Guia de Animes da Temporada de Primavera 2026: As estreias imperdíveis', 'hello-elementor-child' ); ?></h3>
87: 							<p class="home-placeholder-news-hero__desc"><?php _e( 'Confira nossa lista completa com sinopse, estúdio de animação, data de lançamento e trailers de todos os novos animes que chegam nesta temporada sazonal.', 'hello-elementor-child' ); ?></p>
88: 							<span class="home-placeholder-news-hero__status"><?php _e( 'Task 1.3 — Grade de Notícias (Hero Placeholder)', 'hello-elementor-child' ); ?></span>
89: 						</div>
90: 					</div>
91: 				</div>
92: 			</section>
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
