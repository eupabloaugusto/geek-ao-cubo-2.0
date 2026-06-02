<?php
/**
 * Organism: Widget Links Oficiais
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$external = isset( $args['external'] ) ? (array) $args['external'] : array();

if ( empty( $external ) ) {
	return;
}
?>
<section class="widget-external" aria-label="<?php esc_attr_e('Links Oficiais', 'geek-ao-cubo'); ?>">
	<?php mm_render_component( 'organisms', 'secao-titulo', array(
		'titulo' => __( 'Links Oficiais', 'geek-ao-cubo' ),
	) ); ?>
	<ul class="widget-external__list">
		<?php foreach ( $external as $link ) : 
			if ( empty($link['name']) || empty($link['url']) ) continue;
			$name = esc_html($link['name']);
			$url = esc_url($link['url']);
		?>
		<li class="widget-external__item">
			<?php
			mm_render_component( 'atoms', 'icone-externo-link', array(
				'label' => $name,
				'url'   => $url,
				'class' => 'widget-external__link-atom',
			) );
			?>
		</li>
		<?php endforeach; ?>
	</ul>
</section>
