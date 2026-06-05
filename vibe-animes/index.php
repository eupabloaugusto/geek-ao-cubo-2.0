<?php
/**
 * Fallback template — WordPress exige este arquivo em todo tema.
 * Em condições normais nunca é renderizado (front-page.php, single-*.php tomam precedência).
 *
 * @package geek-ao-cubo
 */

get_header();
?>
<main id="content" role="main">
<?php
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
endif;
?>
</main>
<?php
get_footer();
