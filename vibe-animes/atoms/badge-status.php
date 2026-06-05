<?php
/**
 * Atom: Badge de Status
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Fallbacks e higienização
$status_input = isset( $args['status'] ) ? trim( $args['status'] ) : 'completed';
$class        = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

// Map various input formats (raw Jikan API, translated, or slugs, including sanitized variants) to standard keys
$status_map = array(
	// Airing
	'currently airing'      => 'airing',
	'em exibição'           => 'airing',
	'em exibicao'           => 'airing',
	'emexibio'              => 'airing',
	'emexibicao'            => 'airing',
	'en emision'            => 'airing',
	'en emisión'            => 'airing',
	'en emisión'            => 'airing',
	'enemision'             => 'airing',
	'en cours de diffusion' => 'airing',
	'encoursdediffusion'    => 'airing',
	'wird ausgestrahlt'     => 'airing',
	'wirdausgestrahlt'      => 'airing',
	'airing'                => 'airing',

	// Completed
	'finished airing'       => 'completed',
	'finished'              => 'completed',
	'finalizado'            => 'completed',
	'terminé'               => 'completed',
	'termine'               => 'completed',
	'abgeschlossen'         => 'completed',
	'completed'             => 'completed',

	// Upcoming
	'not yet aired'         => 'upcoming',
	'em breve'              => 'upcoming',
	'embreve'               => 'upcoming',
	'próximamente'          => 'upcoming',
	'proximamente'          => 'upcoming',
	'bientôt'               => 'upcoming',
	'bientot'               => 'upcoming',
	'demnächst'             => 'upcoming',
	'demnachst'             => 'upcoming',
	'upcoming'              => 'upcoming',

	// Publishing
	'publishing'            => 'publishing',
	'em publicação'         => 'publishing',
	'em publicacao'         => 'publishing',
	'empublicacao'          => 'publishing',
	'en publicação'         => 'publishing',
	'en publicacion'        => 'publishing',
	'enpublicacion'         => 'publishing',
	'en publication'        => 'publishing',
	'enpublication'         => 'publishing',
	'in veröffentlichung'   => 'publishing',
	'in veroffentlichung'   => 'publishing',
	'inveroffentlichung'    => 'publishing',

	// Hiatus
	'on hiatus'             => 'hiatus',
	'em hiato'              => 'hiatus',
	'emhiato'               => 'hiatus',
	'en pausa'              => 'hiatus',
	'enpausa'               => 'hiatus',
	'en pause'              => 'hiatus',
	'enpause'               => 'hiatus',
	'pausiert'              => 'hiatus',
	'hiatus'                => 'hiatus',

	// Discontinued
	'discontinued'          => 'discontinued',
	'descontinuado'         => 'discontinued',
	'arrêté'                => 'discontinued',
	'arrete'                => 'discontinued',
	'eingestellt'           => 'discontinued',
);

$status_lower = strtolower( $status_input );
$status_key   = isset( $status_map[ $status_lower ] ) ? $status_map[ $status_lower ] : $status_input;
$status       = sanitize_key( $status_key );

// Labels padrões com internacionalização baseada no status
$status_labels = array(
	'airing'       => __( 'Em exibição', 'geek-ao-cubo' ),
	'completed'    => __( 'Finalizado', 'geek-ao-cubo' ),
	'upcoming'     => __( 'Em breve', 'geek-ao-cubo' ),
	'publishing'   => __( 'Em publicação', 'geek-ao-cubo' ),
	'hiatus'       => __( 'Em hiato', 'geek-ao-cubo' ),
	'discontinued' => __( 'Descontinuado', 'geek-ao-cubo' ),
);

$label = isset( $args['label'] ) ? esc_html( __( $args['label'], 'geek-ao-cubo' ) ) : ( isset( $status_labels[ $status ] ) ? $status_labels[ $status ] : ucfirst( $status ) );
?>

<span class="badge-status badge-status--<?php echo $status; ?> <?php echo $class; ?>">
	<?php echo $label; ?>
</span>
