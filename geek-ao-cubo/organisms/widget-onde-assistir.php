<?php
/**
 * Organism: Widget Onde Assistir
 *
 * Exibe os links oficiais de streaming fornecidos pela Jikan API.
 *
 * @package geek-ao-cubo
 *
 * @param array $streaming Array de streaming vindo do Jikan (['name', 'url'])
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$streaming = isset( $args['streaming'] ) ? (array) $args['streaming'] : array();

if ( empty( $streaming ) ) {
	return;
}
?>
<section class="widget-streaming" aria-label="<?php esc_attr_e('Onde Assistir', 'geek-ao-cubo'); ?>">
	<?php mm_render_component( 'organisms', 'secao-titulo', array(
		'titulo' => __( 'Onde Assistir', 'geek-ao-cubo' ),
	) ); ?>
	<ul class="widget-streaming__list">
		<?php foreach ( $streaming as $stream ) : 
			if ( empty($stream['name']) || empty($stream['url']) ) continue;
			$name = esc_html($stream['name']);
			$url = esc_url($stream['url']);
		?>
		<li class="widget-streaming__item">
			<?php
			mm_render_component( 'atoms', 'icone-externo-link', array(
				'label' => $name,
				'url'   => $url,
				'class' => 'widget-streaming__link-atom',
			) );
			?>
		</li>
		<?php endforeach; ?>
	</ul>
</section>
