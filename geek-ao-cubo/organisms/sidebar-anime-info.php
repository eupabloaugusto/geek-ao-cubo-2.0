<?php
/**
 * Organism: Sidebar Anime Info (sidebar-anime-info)
 *
 * Exibe metadados detalhados e exclusivos do anime na lateral da página:
 * estatísticas secundárias (ranking, popularidade, membros) e
 * metadados não duplicados com o hero-anime (exibição, transmissão, produtores, fonte, etc.).
 *
 * @package geek-ao-cubo
 *
 * @param string $rank       Ranking (ex: "#4").
 * @param string $popularity Popularidade (ex: "#39").
 * @param string $members    Membros (ex: "3.2M").
 * @param array  $metadata   Array associativo chave => valor com informações textuais adicionais.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rank       = isset( $args['rank'] ) ? esc_html( $args['rank'] ) : '';
$popularity = isset( $args['popularity'] ) ? esc_html( $args['popularity'] ) : '';
$members    = isset( $args['members'] ) ? esc_html( $args['members'] ) : '';
$metadata   = isset( $args['metadata'] ) ? (array) $args['metadata'] : array();
?>

<aside class="sidebar-anime-info">

	<!-- 1. Bloco Compacto de Estatísticas Secundárias (sem Score) -->
	<?php if ( ! empty( $rank ) || ! empty( $popularity ) || ! empty( $members ) ) : ?>
		<div class="sidebar-anime-info__stats">
			<?php
			mm_render_component( 'molecules', 'stat-bloco', array(
				'rank'       => $rank,
				'popularity' => $popularity,
				'members'    => $members,
			) );
			?>
		</div>
	<?php endif; ?>

	<!-- 2. Lista Vertical de Informações Técnicas -->
	<?php if ( ! empty( $metadata ) ) : ?>
		<div class="sidebar-anime-info__content">
			<h4 class="sidebar-anime-info__content-title"><?php _e( 'Informações', 'geek-ao-cubo' ); ?></h4>
			<dl class="sidebar-anime-info__list">
				<?php foreach ( $metadata as $label => $value ) : ?>
					<?php if ( ! empty( $value ) ) : ?>
						<div class="sidebar-anime-info__item">
							<dt class="sidebar-anime-info__label"><?php echo esc_html( $label ); ?></dt>
							<dd class="sidebar-anime-info__value"><?php echo wp_kses_post( $value ); ?></dd>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</dl>
		</div>
	<?php endif; ?>

</aside>
