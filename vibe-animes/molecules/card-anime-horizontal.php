<?php
/**
 * Molecule: Card Anime Horizontal (card-anime-horizontal)
 *
 * Exibe um anime relacionado em formato horizontal para encaixar 
 * elegantemente ao final de artigos, mantendo consistência com o design system.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : 0;
if ( ! $post_id ) {
	return;
}

// 1. Dados Básicos do WordPress
$title = get_the_title( $post_id );
$url   = get_permalink( $post_id );
$image = get_the_post_thumbnail_url( $post_id, 'medium' );

// 2. Fallback de Imagem
if ( empty( $image ) ) {
	$image = get_template_directory_uri() . '/assets/images/placeholder-anime.jpg';
}

// 3. Status e Formato (via taxonomia e metadados)
$status_terms = get_the_terms( $post_id, 'status_exibicao' );
$status_name  = ( $status_terms && ! is_wp_error( $status_terms ) ) ? $status_terms[0]->name : '';

$tipo_midia = get_field( 'anime_tipo', $post_id );
if ( empty( $tipo_midia ) ) {
	$tipo_midia = 'TV';
}

// 4. Jikan API Dados (Nota e Ano)
$mal_id = (int) get_field( 'anime_id_mal', $post_id );
$score = 'N/A';
$year  = '';

if ( $mal_id > 0 && class_exists('Jikan_API') ) {
	$jikan_data = Jikan_API::get_anime_full( $mal_id );
	if ( ! empty( $jikan_data ) ) {
		$score = isset( $jikan_data['score'] ) ? number_format( (float) $jikan_data['score'], 2, '.', '' ) : 'N/A';
		$year  = isset( $jikan_data['year'] ) ? $jikan_data['year'] : '';
	}
}

$class = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
?>
<div class="card-anime-horizontal <?php echo $class; ?>">
	<a href="<?php echo esc_url( $url ); ?>" class="card-anime-horizontal__link" aria-label="<?php echo esc_attr( sprintf( __( 'Ver detalhes do anime %s', 'geek-ao-cubo' ), $title ) ); ?>">
		
		<div class="card-anime-horizontal__image-wrapper">
			<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="card-anime-horizontal__image" loading="lazy" />
			<?php if ( ! empty( $score ) && 'N/A' !== $score ) : ?>
				<span class="card-anime-horizontal__badge card-anime-horizontal__badge--score">⭐ <?php echo esc_html( $score ); ?></span>
			<?php endif; ?>
		</div>

		<div class="card-anime-horizontal__content">
			<div class="card-anime-horizontal__meta">
				<?php if ( ! empty( $tipo_midia ) ) : ?>
					<span class="card-anime-horizontal__type"><?php echo esc_html( strtoupper( $tipo_midia ) ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $year ) ) : ?>
					<span class="card-anime-horizontal__year">• <?php echo esc_html( $year ); ?></span>
				<?php endif; ?>
			</div>

			<h4 class="card-anime-horizontal__title"><?php echo esc_html( $title ); ?></h4>

			<?php if ( ! empty( $status_name ) ) : ?>
				<div class="card-anime-horizontal__status">
					<span class="card-anime-horizontal__status-dot"></span>
					<?php echo esc_html( $status_name ); ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="card-anime-horizontal__action">
			<span class="card-anime-horizontal__btn"><?php _e( 'Ver no Catálogo', 'geek-ao-cubo' ); ?> &rarr;</span>
		</div>
	</a>
</div>
