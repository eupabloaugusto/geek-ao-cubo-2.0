<?php
/**
 * Rewrite Rules — Flush na Ativação e Desativação do Tema
 *
 * Garante que as URLs amigáveis dos CPTs registrados (anime, episodio, temporada, review)
 * sejam reconhecidas pelo WordPress imediatamente, sem exigir que o usuário
 * acesse Configurações > Links Permanentes manualmente.
 *
 * ⚠️  `flush_rewrite_rules()` é uma operação pesada. NUNCA chamar em `init` ou
 *     em qualquer hook executado a cada requisição. Apenas em ativação/desativação.
 *
 * @package geek-ao-cubo
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ativação do tema filho: registra os CPTs e faz flush das regras.
 *
 * O WordPress só executa `after_switch_theme` uma vez, quando o tema é ativado.
 * Os CPTs já foram registrados pelas funções em includes/ via `init`,
 * por isso podemos fazer o flush com segurança aqui.
 */
function mm_flush_rewrite_on_activation() {
	// Força o registro imediato dos CPTs antes do flush
	mm_register_cpt_anime();
	mm_register_taxonomy_genero();
	mm_register_taxonomy_status_exibicao();
	mm_register_cpt_manga();
	mm_register_taxonomy_status_manga();
	mm_register_cpt_episodio();
	mm_register_cpt_temporada();
	mm_register_cpt_review();

	// Atualiza as regras de rewrite no banco de dados
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'mm_flush_rewrite_on_activation' );


/**
 * Desativação do tema filho: limpa as regras de rewrite dos CPTs.
 *
 * Isso evita que URLs de CPTs quebrem se o tema for trocado.
 */
function mm_flush_rewrite_on_deactivation() {
	flush_rewrite_rules();
}
add_action( 'switch_theme', 'mm_flush_rewrite_on_deactivation' );
