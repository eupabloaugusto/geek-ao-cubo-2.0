<?php
/**
 * Atom: Foto Perfil (foto-perfil)
 *
 * Exibe a imagem de perfil de uma pessoa (dublador, autor) de forma redonda.
 *
 * @package geek-ao-cubo
 *
 * @param string $url    URL da imagem.
 * @param string $alt    Texto alternativo para a imagem.
 * @param string $tamanho Tamanho do componente (md, lg, xl). Padrão: md.
 * @param string $class  Classes CSS adicionais.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$url     = isset( $args['url'] ) ? esc_url( $args['url'] ) : '';
$alt     = isset( $args['alt'] ) ? esc_attr( $args['alt'] ) : '';
$tamanho = isset( $args['tamanho'] ) ? esc_attr( $args['tamanho'] ) : 'md';
$class   = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

if ( empty( $url ) ) {
	// Fallback de avatar genérico (placeholder)
	$url = 'https://ui-avatars.com/api/?name=' . urlencode( $alt ?: '?' ) . '&background=1c1c1c&color=f56b15&size=256';
}
?>
<div class="foto-perfil foto-perfil--<?php echo $tamanho; ?> <?php echo $class; ?>">
	<img src="<?php echo $url; ?>" alt="<?php echo $alt; ?>" class="foto-perfil__img" loading="lazy" width="256" height="256">
</div>
