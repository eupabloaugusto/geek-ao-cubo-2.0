<?php
/**
 * Molecule: Card de Dublagem (card-dublagem)
 *
 * Card focado em SEO e interlinkagem. Exibe o personagem dublado e o anime ao qual pertence,
 * criando um link que aponta para a página de review/single do anime.
 *
 * @package geek-ao-cubo
 *
 * @param string $nome_personagem Nome do personagem.
 * @param string $papel           Papel (Main / Supporting).
 * @param string $foto_personagem URL da foto do personagem.
 * @param string $nome_anime      Nome do anime.
 * @param string $foto_anime      URL da capa/miniatura do anime.
 * @param string $url_anime       Link para a página do anime no nosso site.
 * @param string $class           Classes CSS adicionais.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$nome_personagem = isset( $args['nome_personagem'] ) ? esc_html( $args['nome_personagem'] ) : '';
$papel           = isset( $args['papel'] ) ? esc_html( $args['papel'] ) : '';
$foto_personagem = isset( $args['foto_personagem'] ) ? esc_url( $args['foto_personagem'] ) : '';
$nome_anime      = isset( $args['nome_anime'] ) ? esc_html( $args['nome_anime'] ) : '';
$foto_anime      = isset( $args['foto_anime'] ) ? esc_url( $args['foto_anime'] ) : '';
$url_anime       = isset( $args['url_anime'] ) ? esc_url( $args['url_anime'] ) : '#';
$class           = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

// Traduz o papel
$papel_traduzido = ( 'Main' === $papel ) ? 'Principal' : ( ( 'Supporting' === $papel ) ? 'Secundário' : $papel );

if ( empty( $nome_personagem ) || empty( $nome_anime ) ) {
	return;
}
?>
<a href="<?php echo $url_anime; ?>" class="card-dublagem <?php echo $class; ?>" title="<?php echo esc_attr( sprintf( 'Ver análise de %s', $nome_anime ) ); ?>">
	
	<!-- Andar de Cima: Personagem (Alinhado à Esquerda) -->
	<div class="card-dublagem__row card-dublagem__row--char">
		<div class="card-dublagem__img-wrapper">
			<img src="<?php echo $foto_personagem; ?>" alt="<?php echo esc_attr( $nome_personagem ); ?>" class="card-dublagem__img" loading="lazy">
		</div>
		<div class="card-dublagem__info">
			<h3 class="card-dublagem__nome"><?php echo $nome_personagem; ?></h3>
			<span class="card-dublagem__papel"><?php echo $papel_traduzido; ?></span>
		</div>
	</div>

	<!-- Andar de Baixo: Anime (Alinhado à Direita) -->
	<div class="card-dublagem__row card-dublagem__row--anime">
		<div class="card-dublagem__info card-dublagem__info--right">
			<span class="card-dublagem__label">Anime</span>
			<h4 class="card-dublagem__anime-nome"><?php echo $nome_anime; ?></h4>
		</div>
		<div class="card-dublagem__img-wrapper">
			<img src="<?php echo $foto_anime; ?>" alt="<?php echo esc_attr( 'Capa de ' . $nome_anime ); ?>" class="card-dublagem__img" loading="lazy">
		</div>
	</div>
</a>
