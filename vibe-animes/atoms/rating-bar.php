<?php
/**
 * Atom: Barra de Avaliação (rating-bar)
 *
 * Exibe uma linha com a nota, a barra horizontal de progresso representando o preenchimento percentual de votos,
 * e a porcentagem correspondente (com contagem opcional de votos).
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Higienização e Resolução de Argumentos
$score      = isset( $args['score'] ) ? esc_html( $args['score'] ) : '';
$percentage = isset( $args['percentage'] ) ? floatval( $args['percentage'] ) : 0.0;
$votes      = isset( $args['votes'] ) ? esc_html( $args['votes'] ) : '';
$class      = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

// Garante limites razoáveis de porcentagem
if ( $percentage < 0 ) {
	$percentage = 0.0;
} elseif ( $percentage > 100 ) {
	$percentage = 100.0;
}

// Se não houver nota a exibir, impede a renderização
if ( empty( $score ) ) {
	return;
}
?>
<div class="rating-bar <?php echo $class; ?>" title="<?php echo sprintf( 'Nota %s: %s%% dos votos (%s)', $score, $percentage, ! empty( $votes ) ? $votes : 'sem contagem' ); ?>">
	<!-- 1. Rótulo da Nota -->
	<span class="rating-bar__score"><?php echo $score; ?></span>
	
	<!-- 2. Trilho e Preenchimento com Acessibilidade Progressbar -->
	<div class="rating-bar__track">
		<div 
			class="rating-bar__fill" 
			role="progressbar" 
			aria-valuenow="<?php echo $percentage; ?>" 
			aria-valuemin="0" 
			aria-valuemax="100" 
			aria-label="<?php echo sprintf( '%s por cento dos votos para nota %s', $percentage, $score ); ?>"
			style="--rating-bar-width: <?php echo $percentage; ?>%;"
		></div>
	</div>
	
	<!-- 3. Percentual e Votos Detalhados -->
	<div class="rating-bar__meta">
		<span class="rating-bar__percentage"><?php echo number_format( $percentage, 1, ',', '.' ); ?>%</span>
		<?php if ( ! empty( $votes ) ) : ?>
			<span class="rating-bar__votes-count" aria-hidden="true">(<?php echo $votes; ?>)</span>
		<?php endif; ?>
	</div>
</div>
