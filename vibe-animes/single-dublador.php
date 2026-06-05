<?php
/**
 * Template Name: Single Dublador
 * Template Post Type: dublador
 *
 * Exibe a página individual de um dublador, carregando seus dados e papéis da API do Jikan.
 *
 * @package geek-ao-cubo
 */

get_header();

// ID do dublador no MyAnimeList (MAL_ID) definido no painel do WordPress via Custom Field (ACF)
$mal_id = get_field( 'dublador_id_mal' );

// Busca os dados completos da API Jikan
$person_data = $mal_id ? Jikan_API::get_person_full( $mal_id ) : null;
?>

<main class="single-dublador-page">
	
	<?php if ( ! $person_data ) : ?>
		
		<div class="single-dublador-page__empty">
			<h2>Dublador não encontrado ou ID MAL não configurado.</h2>
			<p>Edite este post no painel e adicione um ID válido do MyAnimeList no campo "ID MAL do Dublador".</p>
		</div>
		
	<?php else : ?>
		
		<!-- Organism 1: Hero -->
		<?php mm_render_component( 'organisms', 'hero-dublador', array(
			'jikan_data' => $person_data,
		) ); ?>

		<!-- Organism 2: Grid de Trabalhos de Voz -->
		<?php if ( ! empty( $person_data['voices'] ) ) : ?>
			<?php mm_render_component( 'organisms', 'grid-trabalhos-voz', array(
				'voices' => $person_data['voices'],
			) ); ?>
		<?php endif; ?>

		<!-- Organism 3: Grid de Staff (Opcional, futuro) -->
		<?php if ( ! empty( $person_data['anime'] ) ) : ?>
			<!-- Poderíamos ter um grid-trabalhos-staff aqui -->
		<?php endif; ?>

	<?php endif; ?>

</main>

<?php get_footer(); ?>
