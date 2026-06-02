<?php
/**
 * Molecule: Card de Anime (card-anime)
 *
 * Card vertical premium de exibição de anime individual. Incorpora imagem-capa (com nota e horário),
 * status e pílulas de gênero.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Extração e Higienização de Parâmetros
$titulo     = isset( $args['titulo'] ) ? esc_html( $args['titulo'] ) : '';
$url        = isset( $args['url'] ) ? esc_url( $args['url'] ) : '#';
$imagem_url = isset( $args['imagem_url'] ) ? esc_url( $args['imagem_url'] ) : '';
$nota       = isset( $args['nota'] ) ? esc_html( $args['nota'] ) : '';
$horario    = isset( $args['horario'] ) ? esc_html( $args['horario'] ) : '';
$generos    = isset( $args['generos'] ) ? $args['generos'] : array();
$class      = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

// Impede a renderização se o título do anime estiver vazio
if ( empty( $titulo ) ) {
	return;
}
?>

<div class="card-anime <?php echo $class; ?>">
	
	<!-- A. Capa de Mídia Widescreen (proporção vertical 2:3 controlada pelo atom imagem-capa) -->
	<a href="<?php echo $url; ?>" class="card-anime__media" aria-label="<?php echo esc_attr( sprintf( __( 'Ver detalhes do anime %s', 'geek-ao-cubo' ), $titulo ) ); ?>">
		<?php 
		mm_render_component( 'atoms', 'imagem-capa', array(
			'src'          => $imagem_url,
			'alt'          => sprintf( __( 'Capa do anime: %s', 'geek-ao-cubo' ), $titulo ),
			'mostrar_nota' => ! empty( $nota ),
			'nota'         => $nota,
			'horario'      => $horario,
			'class'        => ''
		) );
		?>
	</a>

	<!-- B. Conteúdo Textual e Categorias (Header, Title, Genres) -->
	<div class="card-anime__content">

		<!-- Trilho de Gêneros (Pills) -->
		<?php if ( ! empty( $generos ) && is_array( $generos ) ) : ?>
			<div class="card-anime__genres" role="list" aria-label="<?php esc_attr_e( 'Gêneros do anime', 'geek-ao-cubo' ); ?>">
				<?php 
				$limit = 2; // Limita a no máximo 2 gêneros visíveis para evitar transbordamento visual
				$count = 0;
				foreach ( $generos as $gen ) {
					if ( $count >= $limit ) {
						break;
					}
					
					$gen_name = '';
					$gen_url  = '#';

					if ( is_array( $gen ) ) {
						$gen_name = isset( $gen['name'] ) ? $gen['name'] : '';
						$gen_url  = isset( $gen['url'] ) ? $gen['url'] : '#';
					} else {
						$gen_name = $gen;
						$gen_url  = home_url( '/genero/' . sanitize_title( $gen ) . '/' );
					}

					if ( ! empty( $gen_name ) ) {
						mm_render_component( 'atoms', 'badge-genero', array(
							'genero' => $gen_name,
							'url'    => $gen_url,
							'class'  => 'card-anime__genre-badge'
						) );
						$count++;
					}
				}
				?>
			</div>
		<?php endif; ?>

		<!-- Título Semântico Dinâmico para Flexibilidade de SEO (h2 ou h3) -->
		<?php 
		$title_tag = isset( $args['title_tag'] ) ? esc_attr( $args['title_tag'] ) : 'h3';
		if ( ! in_array( $title_tag, array( 'h2', 'h3', 'h4', 'h5', 'h6' ), true ) ) {
			$title_tag = 'h3';
		}
		?>
		<<?php echo $title_tag; ?> class="card-anime__title">
			<a href="<?php echo $url; ?>" class="card-anime__title-link">
				<?php echo $titulo; ?>
			</a>
		</<?php echo $title_tag; ?>>

	</div>

</div>
