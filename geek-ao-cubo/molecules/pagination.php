<?php
/**
 * Molecule: Pagination (pagination)
 *
 * Exibe botões de paginação estilizados nativos do WordPress (paginate_links).
 * Requer que o loop atual tenha sido processado ou que o objeto $query seja passado por arg.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$class = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

// Enfileira o script global de navegação AJAX 
// (só será carregado se houver paginação na tela, mantendo a performance)
wp_enqueue_script( 'geek-ao-cubo-ajax-navigation', get_template_directory_uri() . '/assets/js/mm-ajax-navigation.js', array(), '1.0', true );

// Permite injetar WP_Query customizada ou max_num_pages, caso não use a query principal
global $wp_query;
$query_obj     = isset( $args['query'] ) ? $args['query'] : $wp_query;
$max_num_pages = isset( $args['max_num_pages'] ) ? (int) $args['max_num_pages'] : ( isset( $query_obj->max_num_pages ) ? $query_obj->max_num_pages : 1 );
$current_page  = isset( $args['current_page'] ) ? (int) $args['current_page'] : max( 1, get_query_var( 'paged' ) );

// Ícones SVG em vez de texto
$svg_prev = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"></polyline></svg>';
$svg_next = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>';

$base   = isset( $args['base'] ) ? $args['base'] : '';
$format = isset( $args['format'] ) ? $args['format'] : '';

// No mobile, reduz o número de botões ao redor da página atual para evitar quebra de linha
$mid_size = wp_is_mobile() ? 1 : 2;

// Parâmetros base da paginação
$paginate_args = array(
	'total'     => $max_num_pages,
	'current'   => $current_page,
	'prev_text' => $svg_prev,
	'next_text' => $svg_next,
	'prev_next' => false,
	'type'      => 'list',
	'end_size'  => 1,
	'mid_size'  => $mid_size,
);

if ( ! empty( $base ) ) {
	$paginate_args['base'] = $base;
}

if ( ! empty( $format ) ) {
	$paginate_args['format'] = $format;
}

$links = paginate_links( $paginate_args );

// Se houver menos de 2 páginas, não exibe nada
if ( ! $links ) {
	return;
}
?>

<nav class="pagination js-ajax-replace <?php echo $class; ?>" aria-label="<?php esc_attr_e( 'Navegação de página', 'geek-ao-cubo' ); ?>">
	<?php 
	// $links já contém uma <ul class="page-numbers"> nativa do WP.
	// Nós injetamos ela aqui e a controlamos via CSS.
	// Nota: o replace abaixo garante que os itens da lista também tenham uma classe fácil se precisarmos, 
	// mas o CSS pode agir direto no .page-numbers
	echo $links; 
	?>
</nav>
