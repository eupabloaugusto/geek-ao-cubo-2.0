<?php
/**
 * Test script: Verifica se a infraestrutura de tradução foi carregada corretamente.
 * Deve ser executado acessando http://geekaocubocom.local/wp-content/themes/vibe-animes/test_translation_flow.php
 */

// Busca robusta pelo wp-load.php
$dirs = array(
    dirname(dirname(dirname(dirname(__FILE__)))),
    dirname(dirname(dirname(dirname(__FILE__)))) . '/public',
    dirname(dirname(dirname(__FILE__))),
    dirname(dirname(dirname(dirname(dirname(__FILE__))))),
);

$wp_load_path = '';
foreach ($dirs as $dir) {
    $path = $dir . '/wp-load.php';
    if (file_exists($path)) {
        $wp_load_path = $path;
        break;
    }
}

if (empty($wp_load_path)) {
    die("Erro: Não foi possível localizar o wp-load.php nos caminhos escaneados.");
}

require_once $wp_load_path;

echo "<pre>";
echo "=== Diagnóstico da Infraestrutura de Tradução de Posts ===\n\n";

// 1. Verificar Classes
echo "1. Verificando carregamento das classes:\n";
if (class_exists('Vibe_Post_Translator')) {
    echo "   [OK] Classe Vibe_Post_Translator carregada com sucesso.\n";
} else {
    echo "   [ERRO] Classe Vibe_Post_Translator NÃO encontrada.\n";
}

if (class_exists('Vibe_Admin_Post_Translation_Meta_Box')) {
    echo "   [OK] Classe Vibe_Admin_Post_Translation_Meta_Box carregada com sucesso.\n";
} else {
    echo "   [ERRO] Classe Vibe_Admin_Post_Translation_Meta_Box NÃO encontrada.\n";
}

// 2. Verificar API Key da Groq
echo "\n2. Verificando chave da API Groq:\n";
$key = Vibe_Post_Translator::get_groq_api_key();
if (!empty($key)) {
    $masked_key = substr($key, 0, 8) . '...' . substr($key, -4);
    echo "   [OK] Groq API Key encontrada: " . $masked_key . "\n";
} else {
    echo "   [AVISO] Groq API Key não encontrada no wp-config.php ou .env.\n";
}

// 3. Verificar Registro de Filtros/Ganchos
echo "\n3. Verificando ganchos de tradução e busca registrados:\n";
global $wp_filter;

$has_transition_hook = has_action('transition_post_status', array('Vibe_Post_Translator', 'handle_post_status_transition'));
if ($has_transition_hook !== false) {
    echo "   [OK] transition_post_status hook registrado com prioridade: " . $has_transition_hook . "\n";
} else {
    echo "   [ERRO] transition_post_status hook NÃO registrado.\n";
}

$has_ajax_hook = has_action('wp_ajax_vibe_translate_post_manual', array('Vibe_Post_Translator', 'handle_manual_translate_ajax'));
if ($has_ajax_hook !== false) {
    echo "   [OK] wp_ajax_vibe_translate_post_manual hook registrado com prioridade: " . $has_ajax_hook . "\n";
} else {
    echo "   [ERRO] wp_ajax_vibe_translate_post_manual hook NÃO registrado.\n";
}

$has_search_hook = has_filter('posts_search', 'vibe_multilingual_posts_search_meta');
if ($has_search_hook !== false) {
    echo "   [OK] posts_search filter registrado com prioridade: " . $has_search_hook . "\n";
} else {
    echo "   [ERRO] posts_search filter NÃO registrado.\n";
}

// 4. Teste de Busca SQL
echo "\n4. Verificando geração de Query de Busca Multilíngue:\n";
$test_query = new WP_Query(array(
    'post_type' => 'post',
    's'         => 'TestTranslationTerm',
    'app_lang'  => 'en', // Força idioma inglês na busca
    'fields'    => 'ids'
));

$sql = $test_query->request;
if (strpos($sql, '_post_title_en') !== false && strpos($sql, '_post_content_en') !== false) {
    echo "   [OK] Busca SQL personalizada gerada com sucesso! Os metadados traduzidos estão sendo inclusos no WHERE.\n";
    echo "   Query SQL Gerada:\n   " . esc_html($sql) . "\n";
} else {
    echo "   [ERRO] A Query SQL não incluiu a busca por metadados traduzidos.\n";
    echo "   Query SQL Gerada:\n   " . esc_html($sql) . "\n";
}

echo "\n=== Fim do Diagnóstico ===\n";
echo "</pre>";
