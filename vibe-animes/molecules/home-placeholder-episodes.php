<?php
/**
 * Molecule: Home Placeholder Episodes (home-placeholder-episodes)
 *
 * Exibe uma grade de cards esqueleto para simular novos episódios pendentes de importação.
 *
 * @package geek-ao-cubo
 * @since   4.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="home-placeholder-episodes">
	<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
		<div class="home-placeholder-card">
			<div class="home-placeholder-card__image"><span>?</span></div>
			<div class="home-placeholder-card__title"><?php _e( 'Aguardando Importação', 'geek-ao-cubo' ); ?></div>
			<div class="home-placeholder-card__status"><?php _e( 'Sem novos episódios', 'geek-ao-cubo' ); ?></div>
		</div>
	<?php endfor; ?>
</div>
