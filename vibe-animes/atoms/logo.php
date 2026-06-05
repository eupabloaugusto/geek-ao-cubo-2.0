<?php
/**
 * Atom: Logo
 *
 * Exibe o logotipo do site em uma das 5 variantes disponíveis,
 * injetando o SVG inline para máximo controle via CSS e performance.
 *
 * @package geek-ao-cubo
 *
 * @param string $variante  Variante do logo: 'horizontal-01' | 'horizontal-02' | 'wordmark' | 'icone-quadrado' | 'icone-simples'. Default: 'horizontal-02'.
 * @param bool   $link      Envolve o logo em um link para a home. Default: true.
 * @param string $url       URL de destino do link. Default: home_url('/').
 * @param string $class     Classes CSS adicionais para o elemento raiz.
 * @param string $alt       Texto alternativo descritivo para acessibilidade. Default: nome do site.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$variante  = isset( $args['variante'] ) ? sanitize_key( $args['variante'] ) : 'horizontal-02';
$com_link  = isset( $args['link'] ) ? (bool) $args['link'] : true;
$url       = isset( $args['url'] ) ? esc_url( $args['url'] ) : esc_url( home_url( '/' ) );
$classes   = isset( $args['class'] ) ? ' ' . esc_attr( $args['class'] ) : '';
$site_name = get_bloginfo( 'name' );
$alt       = isset( $args['alt'] ) ? esc_attr( $args['alt'] ) : esc_attr( $site_name );

$variantes_validas = array(
	'horizontal-01',
	'horizontal-02',
	'wordmark',
	'icone-quadrado',
	'icone-simples',
);

if ( ! in_array( $variante, $variantes_validas, true ) ) {
	$variante = 'horizontal-02';
}

$svg_map = array(
	'horizontal-01'  => 'Vibe Animes - Logo 01.svg',
	'horizontal-02'  => 'Vibe Animes - Logo 02.svg',
	'wordmark'       => 'Vibe Animes - Logo 02.svg',
	'icone-quadrado' => 'Vibe Animes - Logo 04.svg',
	'icone-simples'  => 'Vibe Animes - Logo 03.svg',
);

$svg_filename = $svg_map[ $variante ];
$svg_path     = get_stylesheet_directory() . '/img/logos/' . $svg_filename;
$svg_content  = file_exists( $svg_path ) ? file_get_contents( $svg_path ) : '';
?>

<?php
$tag_name = ( is_front_page() || is_home() ) ? 'h1' : 'div';
?>
<<?php echo $tag_name; ?> class="logo logo--<?php echo esc_attr( $variante ); ?><?php echo $classes; ?>" role="img" aria-label="<?php echo $alt; ?>">
	<?php if ( $com_link ) : ?>
		<a href="<?php echo $url; ?>" class="logo__link" aria-label="<?php echo $alt; ?> — Página Inicial">
	<?php endif; ?>

	<?php if ( $svg_content ) : ?>
		<span class="logo__svg" aria-hidden="true">
			<?php echo $svg_content; ?>
		</span>
	<?php else : ?>
		<span class="logo__fallback"><?php echo esc_html( $site_name ); ?></span>
	<?php endif; ?>

	<?php if ( $com_link ) : ?>
		</a>
	<?php endif; ?>
</<?php echo $tag_name; ?>>
