<?php
/**
 * Front Page Template
 *
 * Ponto de entrada da homepage. O WordPress usa este arquivo automaticamente
 * quando "Leituras > Sua pagina inicial exibe" esta configurado como "Uma pagina estatica".
 *
 * Delega toda a renderizacao para template-home.php mantendo a logica centralizada.
 *
 * @package vibe-animes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Inclui diretamente o template da home para evitar duplicidade de logica
include get_template_directory() . '/template-home.php';
