<?php
/**
 * Molecule: Bloco de Estatísticas (stat-bloco)
 *
 * Bloco de estatísticas para página de detalhes do anime.
 * Compõe rating-score + rank + popularidade + membros.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Higienização e Validação dos Argumentos
$score        = isset( $args['score'] ) ? esc_html( $args['score'] ) : '';
$score_label  = isset( $args['score_label'] ) ? esc_html( $args['score_label'] ) : __( 'Média', 'geek-ao-cubo' );
$score_votes  = isset( $args['score_votes'] ) ? esc_html( $args['score_votes'] ) : '';

$rank         = isset( $args['rank'] ) ? esc_html( $args['rank'] ) : '';
$rank_label   = isset( $args['rank_label'] ) ? esc_html( $args['rank_label'] ) : __( 'Ranking', 'geek-ao-cubo' );

$popularity   = isset( $args['popularity'] ) ? esc_html( $args['popularity'] ) : '';
$pop_label    = isset( $args['pop_label'] ) ? esc_html( $args['pop_label'] ) : __( 'Popularidade', 'geek-ao-cubo' );

$members      = isset( $args['members'] ) ? esc_html( $args['members'] ) : '';
$members_label = isset( $args['members_label'] ) ? esc_html( $args['members_label'] ) : __( 'Membros', 'geek-ao-cubo' );

$class        = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';
?>
<div class="stat-bloco <?php echo $class; ?>">
	
	<!-- 1. Rating Score (Átomo existente) -->
	<?php if ( ! empty( $score ) ) : ?>
		<?php 
		mm_render_component( 'atoms', 'rating-score', array(
			'score' => $score,
			'label' => $score_label,
			'votes' => $score_votes
		) );
		?>
	<?php endif; ?>

	<!-- 2. Outras Estatísticas -->
	<div class="stat-bloco__stats">
		
		<!-- Rank -->
		<?php if ( ! empty( $rank ) ) : ?>
			<div class="stat-bloco__item">
				<span class="stat-bloco__label"><?php echo $rank_label; ?></span>
				<span class="stat-bloco__value"><?php echo $rank; ?></span>
			</div>
		<?php endif; ?>

		<!-- Popularidade -->
		<?php if ( ! empty( $popularity ) ) : ?>
			<div class="stat-bloco__item">
				<span class="stat-bloco__label"><?php echo $pop_label; ?></span>
				<span class="stat-bloco__value"><?php echo $popularity; ?></span>
			</div>
		<?php endif; ?>

		<!-- Membros -->
		<?php if ( ! empty( $members ) ) : ?>
			<div class="stat-bloco__item">
				<span class="stat-bloco__label"><?php echo $members_label; ?></span>
				<span class="stat-bloco__value"><?php echo $members; ?></span>
			</div>
		<?php endif; ?>

	</div>
</div>
