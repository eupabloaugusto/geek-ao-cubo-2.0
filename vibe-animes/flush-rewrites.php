<?php
/**
 * Flush Rewrite Rules Helper — USE UMA ÚNICA VEZ, DEPOIS APAGUE ESTE ARQUIVO.
 * Acesse: http://seusite.local/wp-content/themes/vibe-animes/flush-rewrites.php
 */
define( 'WP_USE_THEMES', false );
require_once dirname( __FILE__ ) . '/../../../wp-load.php';

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Você precisa estar logado como administrador.' );
}

// Força o recarregamento de todas as rewrite rules
flush_rewrite_rules( true );

echo '<html><head><meta charset="utf-8"></head><body>';
echo '<h2 style="color:green">✅ Rewrite Rules atualizadas com sucesso!</h2>';
echo '<p>As rotas multilíngues foram regeneradas. Você já pode testar as URLs.</p>';
echo '<p><strong>⚠️ Apague este arquivo (<code>flush-rewrites.php</code>) do servidor após usar.</strong></p>';
echo '<hr>';
echo '<h3>Regras registradas (primeiras 30):</h3>';
echo '<pre style="font-size:12px;background:#f5f5f5;padding:10px;overflow:auto;">';
$rules = get_option( 'rewrite_rules' );
$count = 0;
foreach ( (array) $rules as $regex => $redirect ) {
	if ( strpos( $regex, 'en/' ) === 0 || strpos( $regex, 'es/' ) === 0 || strpos( $regex, 'fr/' ) === 0 || strpos( $regex, 'de/' ) === 0 ) {
		echo esc_html( $regex ) . "\n  → " . esc_html( $redirect ) . "\n\n";
		$count++;
		if ( $count >= 30 ) break;
	}
}
echo '</pre>';
echo '</body></html>';
