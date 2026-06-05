<?php
/**
 * Test script: Verifica se a query e os helpers do catálogo de Mangás funcionam corretamente.
 * Deve ser executado acessando http://geekaocubocom.local/wp-content/themes/vibe-animes/test_manga_catalog.php
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

header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNÓSTICO DO CATÁLOGO DE MANGÁS ===\n\n";

// 1. Verificar se a função mm_get_letras_ativas_catalogo existe
if (function_exists('mm_get_letras_ativas_catalogo')) {
    echo "[OK] Função mm_get_letras_ativas_catalogo encontrada.\n";
} else {
    die("[FAIL] Função mm_get_letras_ativas_catalogo não encontrada.\n");
}

// 2. Testar mm_get_letras_ativas_catalogo('manga') e ('anime')
$letras_manga = mm_get_letras_ativas_catalogo('manga');
echo "[OK] Letras ativas para mangás: " . implode(', ', $letras_manga) . "\n";

$letras_anime = mm_get_letras_ativas_catalogo('anime');
echo "[OK] Letras ativas para animes: " . implode(', ', $letras_anime) . "\n";

// 3. Verificar mm_query_animes_por_letra
if (function_exists('mm_query_animes_por_letra')) {
    echo "[OK] Função mm_query_animes_por_letra encontrada.\n";
} else {
    die("[FAIL] Função mm_query_animes_por_letra não encontrada.\n");
}

// 4. Testar query com tipo_midia=manga e status=finalizado
$args_manga = array(
    'filtro_tipo_midia' => 'manga',
    'filtro_status'     => 'finalizado',
    'filtro_ordem'      => 'populares',
);

$query_manga = mm_query_animes_por_letra('', $args_manga);
echo "\n--- Teste de Query de Mangás (Populares + Finalizado) ---\n";
echo "Post Type Solicitado: " . $query_manga->query_vars['post_type'] . "\n";
echo "Tax Query:\n";
print_r($query_manga->query_vars['tax_query'] ?? []);
echo "Meta Query:\n";
print_r($query_manga->query_vars['meta_query'] ?? []);
echo "SQL Query:\n";
echo $query_manga->request . "\n\n";

// 5. Testar query com tipo_midia=anime e status=finalizado
$args_anime = array(
    'filtro_tipo_midia' => 'serie',
    'filtro_status'     => 'finalizado',
    'filtro_ordem'      => 'populares',
);

$query_anime = mm_query_animes_por_letra('', $args_anime);
echo "--- Teste de Query de Animes (Populares + Finalizado) ---\n";
echo "Post Type Solicitado: " . $query_anime->query_vars['post_type'] . "\n";
echo "Tax Query:\n";
print_r($query_anime->query_vars['tax_query'] ?? []);
echo "Meta Query:\n";
print_r($query_anime->query_vars['meta_query'] ?? []);
echo "SQL Query:\n";
echo $query_anime->request . "\n\n";

echo "=== FIM DOS TESTES ===\n";
