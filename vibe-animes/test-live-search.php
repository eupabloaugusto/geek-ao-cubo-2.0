<?php
/**
 * Template Name: Test Live Search
 */
get_header();

echo '<div style="background:#111;color:#0f0;padding:20px;font-family:monospace;">';
echo "GET: " . print_r($_GET, true) . "<br>";
echo "get_search_query: '" . get_search_query() . "'<br>";
echo "is_search: " . (is_search() ? 'true' : 'false') . "<br>";

$search_query = get_search_query();
$anime_results = mm_query_animes_por_letra( '', array(
'busca'          => $search_query,
'posts_per_page' => 6,
'paged'          => 1,
) );
echo "Anime found: " . ($anime_results ? $anime_results->found_posts : 'null') . "<br>";

$manga_results = mm_query_animes_por_letra( '', array(
'busca'             => $search_query,
'filtro_tipo_midia' => 'manga',
'posts_per_page'    => 6,
'paged'             => 1,
) );
echo "Manga found: " . ($manga_results ? $manga_results->found_posts : 'null') . "<br>";

global $wp_query;
echo "Blog found: " . $wp_query->found_posts . "<br>";
echo "wp_query s: '" . $wp_query->get('s') . "'<br>";
echo '</div>';

get_footer();
