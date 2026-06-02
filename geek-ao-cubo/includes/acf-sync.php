<?php
/**
 * ACF Local JSON Sync
 *
 * Redireciona o ACF para salvar e carregar os grupos de campos
 * a partir da pasta /geek-ao-cubo/acf-json/ em vez do banco de dados,
 * permitindo versionamento completo no Git.
 *
 * @see https://www.advancedcustomfields.com/resources/local-json/
 * @package geek-ao-cubo
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define o diretório de SALVAMENTO dos arquivos JSON.
 * Toda vez que um grupo de campos é salvo no painel do ACF,
 * o arquivo .json é gravado nesta pasta automaticamente.
 */
function mm_acf_json_save_point( $path ) {
	return get_stylesheet_directory() . '/acf-json';
}
add_filter( 'acf/settings/save_json', 'mm_acf_json_save_point' );


/**
 * Define o(s) diretório(s) de CARREGAMENTO dos arquivos JSON.
 * O ACF lê estes arquivos ao iniciar e sincroniza com o banco de dados.
 */
function mm_acf_json_load_point( $paths ) {
	// Remove o caminho padrão do ACF (pasta do plugin)
	unset( $paths[0] );

	// Adiciona nossa pasta do child theme
	$paths[] = get_stylesheet_directory() . '/acf-json';

	return $paths;
}
add_filter( 'acf/settings/load_json', 'mm_acf_json_load_point' );
