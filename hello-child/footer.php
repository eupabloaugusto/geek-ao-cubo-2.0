<?php
/**
 * The template for displaying the footer
 *
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Renderiza o nosso organismo global de rodapé semântico se não for a página inicial
if ( ! is_front_page() && ! is_home() ) {
	mm_render_component( 'organisms', 'footer' );
}

wp_footer();
?>
</body>
</html>
