<?php
/**
 * Organism: Seção de Personagens em Acordeão (secao-personagens)
 *
 * Exibe a lista completa de personagens do anime em uma única aba de acordeão unificada,
 * ordenados por importância (principais e secundários juntos).
 * Grid Responsivo: 3 colunas no desktop/tablet e 2 colunas no mobile.
 * Paginação Dinâmica Computada: Limite inicial de 5 linhas visíveis, com botões "Ver mais" e "Ver menos".
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titulo      = isset( $args['titulo'] ) ? esc_html( $args['titulo'] ) : __( 'Personagens', 'geek-ao-cubo' );
$personagens = isset( $args['personagens'] ) ? (array) $args['personagens'] : array();

if ( empty( $personagens ) ) {
	return;
}

// Configuração da Hierarquia Decrescente de Papéis (Ordenação por Importância)
$hierarquia_roles = array(
	'protagonista'                       => 1,
	'antagonista'                        => 2,
	'deuteragonista'                     => 3,
	'tritagonista'                       => 4,
	'anti-herói'                         => 5,
	'anti-heroi'                         => 5,
	'vilão'                              => 6,
	'vilao'                              => 6,
	'arqui-inimigo'                      => 7,
	'arqui-inimiga'                      => 7,
	'arqui-inimigo (arqui-inimiga)'      => 7,
	'rival'                              => 8,
	'mentor'                             => 9,
	'confidente'                         => 10,
	'par romântico'                      => 11,
	'par romantico'                      => 11,
	'sidekick (escudeiro/companheiro)'   => 12,
	'sidekick'                           => 12,
	'escudeiro'                          => 12,
	'companheiro'                        => 12,
	'aliado'                             => 13,
	'figura parental'                    => 14,
	'guardião'                           => 15,
	'guardiao'                           => 15,
	'mensageiro'                         => 16,
	'camaleão (shapeshifter)'            => 17,
	'camaleão'                           => 17,
	'camaleao'                           => 17,
	'shapeshifter'                       => 17,
	'alívio cômico'                      => 18,
	'alivio comico'                      => 18,
	'foil (contraponto)'                 => 19,
	'foil'                               => 19,
	'contraponto'                        => 19,
	'principal'                          => 20, // Fallback para "Principal" da API
	'main'                               => 20,
	'coadjuvante'                        => 21,
	'personagem secundário'              => 22,
	'personagem secundario'              => 22,
	'secundário'                         => 23, // Fallback para "Secundário" da API
	'secundario'                         => 23,
	'supporting'                         => 23,
	'personagem coral'                   => 24,
	'personagem terciário'               => 25,
	'personagem terciario'               => 25,
	'terciário'                          => 25,
	'terciario'                          => 25,
	'personagem de apoio'                => 26,
	'apoio'                              => 26,
	'personagem de fundo'                => 27,
	'fundo'                              => 27,
	'personagem episódico'               => 28,
	'personagem episodico'               => 28,
	'episódico'                          => 28,
	'episodico'                          => 28,
	'narrador'                           => 29,
	'figurante'                          => 30,
);

// Ordena o array de personagens de forma decrescente pela importância/hierarquia
usort( $personagens, function( $a, $b ) use ( $hierarquia_roles ) {
	$role_a = isset( $a['role'] ) ? mb_strtolower( trim( $a['role'] ) ) : '';
	$role_b = isset( $b['role'] ) ? mb_strtolower( trim( $b['role'] ) ) : '';

	// Determina a prioridade baseada em correspondência parcial ou exata
	$prio_a = 999;
	foreach ( $hierarquia_roles as $role_key => $prio_val ) {
		if ( str_contains( $role_a, $role_key ) ) {
			$prio_a = $prio_val;
			break;
		}
	}

	$prio_b = 999;
	foreach ( $hierarquia_roles as $role_key => $prio_val ) {
		if ( str_contains( $role_b, $role_key ) ) {
			$prio_b = $prio_val;
			break;
		}
	}

	if ( $prio_a === $prio_b ) {
		// Desempate por popularidade (favorites MAL) — maior primeiro
		$fav_a = isset( $a['favorites'] ) ? (int) $a['favorites'] : 0;
		$fav_b = isset( $b['favorites'] ) ? (int) $b['favorites'] : 0;
		return $fav_b - $fav_a;
	}

	return $prio_a - $prio_b;
} );

$total_cards = count( $personagens );
$grupo_id    = 'grupo-char-elenco';
?>

<section class="secao-personagens" aria-label="<?php echo esc_attr( $titulo ); ?>">
	<div class="secao-personagens__inner">

		<?php mm_render_component( 'organisms', 'secao-titulo', array(
			'titulo' => $titulo,
		) ); ?>

		<div class="secao-personagens__list">
			<div class="secao-personagens__item js-accordion-item" data-state="open">
				
				<!-- Gatilho do Acordeão (Acessível) -->
				<button 
					type="button" 
					class="secao-personagens__trigger js-accordion-trigger" 
					aria-expanded="true"
					aria-controls="<?php echo $grupo_id; ?>"
				>
					<span class="secao-personagens__trigger-title"><?php _e( 'Elenco de Personagens', 'geek-ao-cubo' ); ?></span>
					<span class="secao-personagens__trigger-badge">
						<?php echo sprintf( _n( '%s personagem', '%s personagens', $total_cards, 'geek-ao-cubo' ), $total_cards ); ?>
					</span>
					<svg class="secao-personagens__trigger-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<polyline points="6 9 12 15 18 9"></polyline>
					</svg>
				</button>

				<!-- Conteúdo do Acordeão -->
				<div 
					id="<?php echo $grupo_id; ?>" 
					class="secao-personagens__content js-accordion-content"
					role="region"
				>
					<div class="secao-personagens__content-inner">
						
						<!-- Grid de Cards de Personagens -->
						<div class="secao-personagens__grid js-char-grid">
							<?php foreach ( $personagens as $card_idx => $personagem ) : ?>
								<div class="js-char-card" data-index="<?php echo $card_idx; ?>">
									<?php
									mm_render_component( 'molecules', 'card-personagem', (array) $personagem );
									?>
								</div>
							<?php endforeach; ?>
						</div>

						<!-- Ações de Paginação (Controladas Dinamicamente via JS) -->
						<div class="secao-personagens__actions js-char-actions" style="display: none;">
							
							<?php
							mm_render_component( 'molecules', 'btn-pagination', array(
								'prefix'      => 'char',
								'total_items' => $total_cards,
							) );
							?>

						</div>

					</div>
				</div>
				
			</div>
		</div>

	</div>
</section>
