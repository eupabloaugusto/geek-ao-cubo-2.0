<?php
/**
 * Molecule: Filtro Pills (filtro-pills)
 *
 * Exibe uma navegação horizontal estilo "pílulas" para ordenação de itens.
 * Totalmente responsivo com scroll horizontal suave no mobile (swipeable).
 * Atualiza o parâmetro na URL mantendo o SEO. Ao alterar o filtro, reseta a paginação.
 *
 * @package geek-ao-cubo
 *
 * @param array  $options        Array associativo de chave => Label do filtro.
 * @param string $active_key     Chave atualmente ativa.
 * @param string $param_name     Nome do parâmetro GET (default: 'ordem').
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options    = isset( $args['options'] ) ? (array) $args['options'] : array();
$active_key = isset( $args['active_key'] ) ? $args['active_key'] : '';
$param_name = isset( $args['param_name'] ) ? $args['param_name'] : 'ordem';

if ( empty( $options ) ) {
	return;
}

// Ensure CSS and JS are enqueued
wp_enqueue_style( 'geek-ao-cubo-filtro-pills', get_template_directory_uri() . '/atoms/filtro-pills.css', array(), '1.0' );
wp_enqueue_script( 'geek-ao-cubo-ajax-navigation', get_template_directory_uri() . '/assets/js/mm-ajax-navigation.js', array(), '1.0', true );
?>

<nav class="filtro-pills js-ajax-replace" aria-label="<?php esc_attr_e( 'Opções de ordenação', 'geek-ao-cubo' ); ?>">
	<div class="filtro-pills__container">
		<ul class="filtro-pills__list">
			<?php foreach ( $options as $key => $label ) : 
				$is_active = ( $key === $active_key );
				
				$url = remove_query_arg( 'pg' );
				$url = add_query_arg( $param_name, $key, $url );
			?>
				<li class="filtro-pills__item">
					<a 
						href="<?php echo esc_url( $url ); ?>" 
						class="filtro-pills__link js-ajax-link <?php echo $is_active ? 'filtro-pills__link--active' : ''; ?>"
						<?php echo $is_active ? 'aria-current="page"' : ''; ?>
					>
						<?php echo esc_html( $label ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</nav>
