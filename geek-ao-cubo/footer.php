<?php
/**
 * The template for displaying the footer
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Renderiza o nosso organismo global de rodapé semântico
mm_render_component( 'organisms', 'footer' );

wp_footer();
?>
</body>
</html>
