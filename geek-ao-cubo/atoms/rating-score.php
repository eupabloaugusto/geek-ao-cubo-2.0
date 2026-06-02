<?php
/**
 * Atom: Rating Score (rating-score)
 *
 * Exibe a nota de destaque na página de detalhes do anime, com rótulos superior e inferior contextuais.
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Higienização e Validação dos Argumentos
$score  = isset( $args['score'] ) ? esc_html( $args['score'] ) : '';
$label  = isset( $args['label'] ) ? esc_html( $args['label'] ) : __( 'Média de votos', 'geek-ao-cubo' );
$votes  = isset( $args['votes'] ) ? esc_html( $args['votes'] ) : '';
$class  = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

// Impede a renderização se o valor da nota estiver vazio
if ( empty( $score ) ) {
	return;
}

// Determina se a nota é considerada baixa (abaixo de 5.0) para aplicar estilo visual alternativo
$numeric_score = (float) $score;
$modifier_class = ( $numeric_score < 5.0 && $numeric_score > 0 ) ? 'rating-score--error' : '';
?>
<div class="rating-score <?php echo $modifier_class; ?> <?php echo $class; ?>">
	<?php if ( ! empty( $label ) ) : ?>
		<span class="rating-score__label"><?php echo $label; ?></span>
	<?php endif; ?>

	<div class="rating-score__value"><?php echo $score; ?></div>

	<?php if ( ! empty( $votes ) ) : ?>
		<span class="rating-score__votes"><?php echo $votes; ?></span>
	<?php endif; ?>
</div>
