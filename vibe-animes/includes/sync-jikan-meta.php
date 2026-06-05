<?php
/**
 * Sincronização de Metadados Críticos com a Jikan API
 *
 * Restaura e mantém o campo 'anime_tipo' no banco local para permitir
 * filtragem nativa via WP_Query no Catálogo.
 *
 * Contexto: O script cleanup_db.php removeu 'anime_tipo' do banco.
 * Este módulo o restaura via três mecanismos complementares:
 *   1. Auto-backfill silencioso (WP-Cron, roda uma vez, lê o Shadow Cache existente)
 *   2. Hook permanente no save de cada post do CPT 'anime'
 *   3. Ferramenta de backfill manual via URL (para admin, com feedback em tempo real)
 *
 * @package vibe-animes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// =========================================================================
// 1. AUTO-BACKFILL SILENCIOSO
// Roda uma única vez em background (WP-Cron) lendo apenas transients locais.
// Zero requests HTTP — usa o Shadow Cache que já existe no banco.
// =========================================================================

add_action( 'init', 'mm_auto_backfill_anime_tipo_once' );
function mm_auto_backfill_anime_tipo_once(): void {
	// Verifica flag para garantir execução única
	if ( get_option( 'mm_anime_tipo_backfill_done_v1' ) ) {
		return;
	}

	// Marca ANTES de agendar (evita agendamentos múltiplos em concurrent requests)
	update_option( 'mm_anime_tipo_backfill_done_v1', true, false );

	// Agenda em background — não bloqueia o request do usuário
	if ( ! wp_next_scheduled( 'mm_async_backfill_anime_tipo' ) ) {
		wp_schedule_single_event( time(), 'mm_async_backfill_anime_tipo' );
	}
}

add_action( 'mm_async_backfill_anime_tipo', 'mm_backfill_anime_tipo_from_cache' );
/**
 * Lê o Shadow Cache (transients) já existente e grava 'anime_tipo' no postmeta.
 * Para animes cujo cache ainda não existe, o fetch_and_cache_anime() em
 * class-jikan-api.php já persiste o tipo automaticamente no próximo acesso.
 */
function mm_backfill_anime_tipo_from_cache(): void {
	$anime_ids = get_posts( array(
		'post_type'      => 'anime',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'post_status'    => 'publish',
		'no_found_rows'  => true,
	) );

	foreach ( $anime_ids as $post_id ) {
		// Pula se já sincronizado
		if ( get_post_meta( $post_id, 'anime_tipo', true ) ) {
			continue;
		}

		$mal_id = (int) get_post_meta( $post_id, 'anime_id_mal', true );
		if ( $mal_id <= 0 ) {
			continue;
		}

		// Lê APENAS o transient local — sem request HTTP
		$cached_data = get_transient( 'jikan_anime_full_' . $mal_id );

		if ( ! empty( $cached_data['type'] ) ) {
			update_post_meta( $post_id, 'anime_tipo', sanitize_text_field( $cached_data['type'] ) );
		}
	}
}


// =========================================================================
// 2. HOOK PERMANENTE: Sincroniza anime_tipo ao salvar qualquer Anime
// =========================================================================

/**
 * Ao salvar um Anime, busca o 'type' no Shadow Cache (ou na Jikan como fallback)
 * e persiste no postmeta 'anime_tipo' para filtragem via WP_Query.
 *
 * Prioridade 20: garante que o ACF já tenha salvo 'anime_id_mal' antes.
 */
add_action( 'save_post_anime', 'mm_sync_anime_tipo_on_save', 20, 3 );
function mm_sync_anime_tipo_on_save( int $post_id, WP_Post $post, bool $update ): void {
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$mal_id = (int) get_post_meta( $post_id, 'anime_id_mal', true );

	if ( $mal_id <= 0 ) {
		return;
	}

	// Usa Shadow Cache — sem request HTTP se o cache estiver válido
	$data = Jikan_API::get_anime_full( $mal_id );

	if ( ! empty( $data['type'] ) ) {
		update_post_meta( $post_id, 'anime_tipo', sanitize_text_field( $data['type'] ) );
	}

	if ( function_exists( 'mm_sync_anime_idioma_meta' ) ) {
		mm_sync_anime_idioma_meta( $mal_id, $post_id );
	} elseif ( ! get_post_meta( $post_id, 'anime_idioma', true ) ) {
		update_post_meta( $post_id, 'anime_idioma', 'legendado' );
	}
}

/**
 * Ao salvar um Anime, sincroniza a sinopse do ACF para o post_content.
 * Isso permite que a busca nativa do WordPress (s) encontre o anime
 * por palavras-chave presentes na sinopse.
 *
 * Prioridade 30: garante que o ACF já tenha salvo 'anime_sinopse' antes.
 */
add_action( 'save_post_anime', 'mm_sync_anime_sinopse_to_content', 30, 3 );
function mm_sync_anime_sinopse_to_content( int $post_id, WP_Post $post, bool $update ): void {
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Evita loop infinito
	remove_action( 'save_post_anime', 'mm_sync_anime_sinopse_to_content', 30 );

	$sinopse = '';
	if ( function_exists( 'get_field' ) ) {
		$sinopse = get_field( 'anime_sinopse', $post_id );
	}
	if ( empty( $sinopse ) ) {
		$sinopse = get_post_meta( $post_id, 'anime_sinopse', true );
	}

	if ( ! empty( $sinopse ) && $sinopse !== $post->post_content ) {
		wp_update_post( array(
			'ID'           => $post_id,
			'post_content' => $sinopse,
		) );
	}

	add_action( 'save_post_anime', 'mm_sync_anime_sinopse_to_content', 30, 3 );
}

/**
 * Ao salvar um Manga, sincroniza a sinopse do ACF para o post_content.
 * Prioridade 30: garante que o ACF já tenha salvo 'manga_sinopse_manual' antes.
 */
add_action( 'save_post_manga', 'mm_sync_manga_sinopse_to_content', 30, 3 );
function mm_sync_manga_sinopse_to_content( int $post_id, WP_Post $post, bool $update ): void {
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	remove_action( 'save_post_manga', 'mm_sync_manga_sinopse_to_content', 30 );

	$sinopse = '';
	if ( function_exists( 'get_field' ) ) {
		$sinopse = get_field( 'manga_sinopse_manual', $post_id );
	}
	if ( empty( $sinopse ) ) {
		$sinopse = get_post_meta( $post_id, 'manga_sinopse_manual', true );
	}

	if ( ! empty( $sinopse ) && $sinopse !== $post->post_content ) {
		wp_update_post( array(
			'ID'           => $post_id,
			'post_content' => $sinopse,
		) );
	}

	add_action( 'save_post_manga', 'mm_sync_manga_sinopse_to_content', 30, 3 );
}


// =========================================================================
// 2b. SINCRONIZAÇÃO DE NOMES ALTERNATIVOS (Títulos em japonês, inglês, sinônimos)
// Salva em meta campo para permitir busca por nomes alternativos e fuzzy matching.
// =========================================================================

/**
 * Extrai todos os nomes/títulos de um objeto de dados da Jikan API.
 *
 * @param array $jikan_data Dados da Jikan API (anime ou manga).
 * @return string Todos os nomes concatenados separados por espaço.
 */
function mm_extract_nomes_busca_from_jikan( array $jikan_data ): string {
	$nomes = array();

	// Títulos principais
	$campos = array( 'title', 'title_english', 'title_japanese' );
	foreach ( $campos as $campo ) {
		if ( ! empty( $jikan_data[ $campo ] ) && is_string( $jikan_data[ $campo ] ) ) {
			$nomes[] = $jikan_data[ $campo ];
		}
	}

	// Sinônimos (array de strings)
	if ( ! empty( $jikan_data['title_synonyms'] ) && is_array( $jikan_data['title_synonyms'] ) ) {
		foreach ( $jikan_data['title_synonyms'] as $syn ) {
			if ( is_string( $syn ) && strlen( $syn ) > 0 ) {
				$nomes[] = $syn;
			}
		}
	}

	// Array de objetos titles (type => title)
	if ( ! empty( $jikan_data['titles'] ) && is_array( $jikan_data['titles'] ) ) {
		foreach ( $jikan_data['titles'] as $t ) {
			if ( ! empty( $t['title'] ) && is_string( $t['title'] ) ) {
				$nomes[] = $t['title'];
			}
		}
	}

	// Deduplica e junta
	$nomes = array_unique( array_map( 'trim', $nomes ) );
	return implode( ' ', $nomes );
}

/**
 * Ao salvar um Anime, sincroniza nomes alternativos do Jikan para meta campo.
 * Prioridade 25: entre anime_tipo (20) e sinopse (30).
 */
add_action( 'save_post_anime', 'mm_sync_anime_nomes_busca_on_save', 25, 3 );
function mm_sync_anime_nomes_busca_on_save( int $post_id, WP_Post $post, bool $update ): void {
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$mal_id = (int) get_post_meta( $post_id, 'anime_id_mal', true );
	if ( $mal_id <= 0 ) {
		return;
	}

	$data = Jikan_API::get_anime_full( $mal_id );
	if ( empty( $data ) ) {
		return;
	}

	$nomes = mm_extract_nomes_busca_from_jikan( $data );
	if ( ! empty( $nomes ) ) {
		update_post_meta( $post_id, 'anime_nomes_busca', $nomes );
	}
}

/**
 * Ao salvar um Mangá, sincroniza nomes alternativos do Jikan para meta campo.
 */
add_action( 'save_post_manga', 'mm_sync_manga_nomes_busca_on_save', 25, 3 );
function mm_sync_manga_nomes_busca_on_save( int $post_id, WP_Post $post, bool $update ): void {
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$mal_id = (int) get_post_meta( $post_id, 'manga_id_mal', true );
	if ( $mal_id <= 0 ) {
		return;
	}

	$data = Jikan_API::get_manga_full( $mal_id );
	if ( empty( $data ) ) {
		return;
	}

	$nomes = mm_extract_nomes_busca_from_jikan( $data );
	if ( ! empty( $nomes ) ) {
		update_post_meta( $post_id, 'manga_nomes_busca', $nomes );
	}
}


// =========================================================================
// 3. FERRAMENTA ADMINISTRATIVA: Backfill manual com feedback em tempo real
// URL: /?run_jikan_backfill=1  (requer login como admin)
// Re-sync forçado de todos: &force_all=1
// =========================================================================

add_action( 'init', 'mm_run_jikan_backfill' );
function mm_run_jikan_backfill(): void {
	if ( ! isset( $_GET['run_jikan_backfill'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Acesso negado. Faça login como administrador.' );
	}

	set_time_limit( 600 );
	@ini_set( 'memory_limit', '256M' ); // phpcs:ignore

	$force_all = isset( $_GET['force_all'] );

	// Reseta a flag para que o auto-backfill possa rodar novamente se necessário
	delete_option( 'mm_anime_tipo_backfill_done_v1' );

	$anime_ids = get_posts( array(
		'post_type'      => 'anime',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'post_status'    => 'publish',
		'no_found_rows'  => true,
	) );

	$total         = count( $anime_ids );
	$cache_hit     = 0;
	$api_hit       = 0;
	$skipped_count = 0;
	$failed_count  = 0;
	$idioma_synced = 0;

	// Desativa buffering para streaming de saída em tempo real
	if ( ob_get_level() ) {
		ob_end_clean();
	}

	header( 'Content-Type: text/html; charset=UTF-8' );
	header( 'X-Accel-Buffering: no' ); // Desativa buffer do Nginx/proxy

	echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Backfill anime_tipo</title>';
	echo '<style>body{font-family:monospace;background:#111;color:#eee;padding:2rem;line-height:1.8}';
	echo '.ok{color:#4ade80}.cache{color:#60a5fa}.skip{color:#facc15}.fail{color:#f87171}';
	echo '.done{font-size:1.3rem;font-weight:bold;color:#a3e635}</style></head><body>';
	echo "<h2>📦 Backfill <code>anime_tipo</code> + <code>anime_idioma</code> — {$total} animes</h2><hr>";
	flush();

	foreach ( $anime_ids as $post_id ) {
		$mal_id = (int) get_post_meta( $post_id, 'anime_id_mal', true );
		$titulo = esc_html( get_the_title( $post_id ) );

		if ( $mal_id <= 0 ) {
			echo "<span class='fail'>✗ [ID:{$post_id}] {$titulo} — sem anime_id_mal</span><br>";
			$failed_count++;
			flush();
			continue;
		}

		$current_type = get_post_meta( $post_id, 'anime_tipo', true );
		$current_idioma = get_post_meta( $post_id, 'anime_idioma', true );

		if ( ! empty( $current_type ) && ! empty( $current_idioma ) && ! $force_all ) {
			echo "<span class='skip'>→ [MAL:{$mal_id}] {$titulo} — já tem tipo <strong>{$current_type}</strong> e idioma <strong>{$current_idioma}</strong></span><br>";
			$skipped_count++;
			flush();
			continue;
		}

		if ( ! empty( $current_type ) && ! $force_all && empty( $current_idioma ) ) {
			// Só falta idioma — tenta sync sem refetch de tipo.
			if ( function_exists( 'mm_sync_anime_idioma_meta' ) ) {
				$idioma_slug = mm_sync_anime_idioma_meta( $mal_id, $post_id );
				if ( $idioma_slug ) {
					echo "<span class='cache'>🗣 [MAL:{$mal_id}] {$titulo} → idioma <strong>{$idioma_slug}</strong></span><br>";
					$idioma_synced++;
					flush();
					continue;
				}
			}

			update_post_meta( $post_id, 'anime_idioma', 'legendado' );
			echo "<span class='cache'>🗣 [MAL:{$mal_id}] {$titulo} → idioma <strong>legendado</strong> (fallback)</span><br>";
			$idioma_synced++;
			flush();
			continue;
		}

		if ( ! empty( $current_type ) && ! $force_all ) {
			echo "<span class='skip'>→ [MAL:{$mal_id}] {$titulo} — já tem: <strong>{$current_type}</strong></span><br>";
			$skipped_count++;
			flush();
			continue;
		}

		// Prioridade 1: lê do transient local (zero request HTTP)
		$cached = get_transient( 'jikan_anime_full_' . $mal_id );
		if ( ! empty( $cached['type'] ) ) {
			update_post_meta( $post_id, 'anime_tipo', sanitize_text_field( $cached['type'] ) );
			$idioma_slug = function_exists( 'mm_sync_anime_idioma_meta' )
				? mm_sync_anime_idioma_meta( $mal_id, $post_id )
				: '';
			echo "<span class='cache'>⚡ [MAL:{$mal_id}] {$titulo} → <strong>{$cached['type']}</strong> (cache)";
			if ( $idioma_slug ) {
				echo " | idioma <strong>{$idioma_slug}</strong>";
				$idioma_synced++;
			}
			echo '</span><br>';
			$cache_hit++;
			flush();
			continue;
		}

		// Prioridade 2: busca na Jikan (request HTTP, 350ms de throttle)
		$data = Jikan_API::get_anime_full( $mal_id );
		if ( ! empty( $data['type'] ) ) {
			update_post_meta( $post_id, 'anime_tipo', sanitize_text_field( $data['type'] ) );
			$idioma_slug = function_exists( 'mm_sync_anime_idioma_meta' )
				? mm_sync_anime_idioma_meta( $mal_id, $post_id )
				: '';
			echo "<span class='ok'>✓ [MAL:{$mal_id}] {$titulo} → <strong>{$data['type']}</strong> (API)";
			if ( $idioma_slug ) {
				echo " | idioma <strong>{$idioma_slug}</strong>";
				$idioma_synced++;
			}
			echo '</span><br>';
			$api_hit++;
		} else {
			echo "<span class='fail'>✗ [MAL:{$mal_id}] {$titulo} — Jikan não retornou 'type'</span><br>";
			$failed_count++;
		}
		flush();
	}

	$total_updated = $cache_hit + $api_hit;
	echo '<hr>';
	echo "<p class='done'>✅ Backfill concluído!</p>";
	echo "<p>Total: <strong>{$total}</strong> | ";
	echo "Atualizados: <strong class='ok'>{$total_updated}</strong> (⚡ {$cache_hit} cache + ✓ {$api_hit} API) | ";
	echo "Idiomas: <strong class='ok'>{$idioma_synced}</strong> | ";
	echo "Pulados: <strong class='skip'>{$skipped_count}</strong> | ";
	echo "Falhas: <strong class='fail'>{$failed_count}</strong></p>";
	echo '<p>Os filtros de Tipo de Mídia e Idioma no Catálogo estão operacionais.</p>';

	if ( $failed_count > 0 ) {
		echo '<p><strong>Dica:</strong> Para os que falharam, verifique o <code>anime_id_mal</code> no painel ou aguarde e tente novamente (rate limit da Jikan).</p>';
	}

	echo '</body></html>';
	exit;
}


// =========================================================================
// DIAGNÓSTICO: Inspeciona a estrutura real do banco para posts 'anime'
// URL: /?anime_diagnostico=1  (requer login como admin)
// =========================================================================

add_action( 'init', 'mm_run_anime_diagnostico' );
function mm_run_anime_diagnostico(): void {
	if ( ! isset( $_GET['anime_diagnostico'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Acesso negado. Faça login como administrador.' );
	}

	$animes = get_posts( array(
		'post_type'      => 'anime',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );

	$total = count( $animes );

	if ( ob_get_level() ) {
		ob_end_clean();
	}
	header( 'Content-Type: text/html; charset=UTF-8' );

	echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Diagnóstico anime</title>';
	echo '<style>body{font-family:monospace;background:#111;color:#eee;padding:2rem;line-height:1.6}';
	echo 'table{border-collapse:collapse;width:100%}th,td{border:1px solid #333;padding:6px 10px;text-align:left}';
	echo 'th{background:#222}.ok{color:#4ade80}.warn{color:#facc15}.fail{color:#f87171}</style></head><body>';
	echo "<h2>📋 Diagnóstico — Posts 'anime' — Total no banco: <strong>{$total}</strong></h2>";

	if ( $total === 0 ) {
		echo "<p class='fail'>⛔ Nenhum post do CPT 'anime' encontrado (em qualquer status). O Catálogo está vazio porque não há conteúdo cadastrado.</p>";
		echo '</body></html>';
		exit;
	}

	$status_counts  = array();
	$sem_mal_id     = 0;
	$sem_tipo       = 0;
	$com_tipo       = 0;
	$sem_cache      = 0;
	$com_cache      = 0;

	echo '<table><tr><th>#</th><th>ID WP</th><th>Título</th><th>Status</th><th>anime_id_mal</th><th>anime_tipo</th><th>Transient cache (type)</th></tr>';

	foreach ( $animes as $i => $post_id ) {
		$post       = get_post( $post_id );
		$status     = $post->post_status;
		$titulo     = esc_html( $post->post_title );
		$mal_id     = get_post_meta( $post_id, 'anime_id_mal', true );
		$tipo       = get_post_meta( $post_id, 'anime_tipo', true );
		$cache      = $mal_id ? get_transient( 'jikan_anime_full_' . (int) $mal_id ) : false;
		$cache_type = ( $cache && isset( $cache['type'] ) ) ? $cache['type'] : ( $cache ? '(sem [type] no array)' : '—' );

		$status_counts[ $status ] = ( $status_counts[ $status ] ?? 0 ) + 1;

		if ( ! $mal_id ) {
			$sem_mal_id++;
		}
		if ( $tipo ) {
			$com_tipo++;
			$tipo_html = "<span class='ok'>{$tipo}</span>";
		} else {
			$sem_tipo++;
			$tipo_html = "<span class='fail'>ausente</span>";
		}
		if ( $cache ) {
			$com_cache++;
			$cache_html = "<span class='ok'>{$cache_type}</span>";
		} else {
			$sem_cache++;
			$cache_html = "<span class='warn'>sem cache</span>";
		}
		$mal_html = $mal_id
			? "<span class='ok'>{$mal_id}</span>"
			: "<span class='fail'>ausente</span>";

		echo "<tr><td>" . ( $i + 1 ) . "</td><td>{$post_id}</td><td>{$titulo}</td><td>{$status}</td><td>{$mal_html}</td><td>{$tipo_html}</td><td>{$cache_html}</td></tr>";
	}
	echo '</table><hr>';

	// Resumo
	echo '<h3>Resumo por Status</h3><p>';
	foreach ( $status_counts as $s => $c ) {
		$class = ( 'publish' === $s ) ? 'ok' : 'warn';
		echo "<span class='{$class}'>{$s}: <strong>{$c}</strong></span> &nbsp;";
	}
	echo '</p>';
	echo "<p>Com <code>anime_id_mal</code>: <span class='ok'>" . ( $total - $sem_mal_id ) . '</span> | Sem: ' . ( $sem_mal_id ? "<span class='fail'>{$sem_mal_id}</span>" : "<span class='ok'>0</span>" ) . '</p>';
	echo "<p>Com <code>anime_tipo</code>: <span class='ok'>{$com_tipo}</span> | Sem: " . ( $sem_tipo ? "<span class='fail'>{$sem_tipo}</span>" : "<span class='ok'>0</span>" ) . '</p>';
	echo "<p>Com transient de cache: <span class='ok'>{$com_cache}</span> | Sem: " . ( $sem_cache ? "<span class='warn'>{$sem_cache}</span>" : "<span class='ok'>0</span>" ) . '</p>';

	// Conclusão automatica
	echo '<h3>Conclusão</h3>';
	$publish_count = $status_counts['publish'] ?? 0;
	if ( $publish_count === 0 ) {
		echo "<p class='fail'>⛔ Existem {$total} posts mas <strong>nenhum está publicado</strong> (publish). O Catálogo mostra apenas posts publicados — o problema é de <strong>status dos conteúdos</strong>, não do filtro.</p>";
	} elseif ( $sem_mal_id === $total ) {
		echo "<p class='fail'>⛔ Todos os posts existem mas sem <code>anime_id_mal</code>. Estrutura de dados incompleta — problema de <strong>conteúdo mal cadastrado</strong>.</p>";
	} elseif ( $com_tipo === 0 && $com_cache === 0 ) {
		echo "<p class='fail'>⛔ <code>anime_tipo</code> ausente em todos E sem transient de cache. Acesse <a href='/?run_jikan_backfill=1' style='color:#60a5fa'>/?run_jikan_backfill=1</a> para popular.</p>";
	} elseif ( $com_tipo === 0 && $com_cache > 0 ) {
		echo "<p class='warn'>⚠️ <code>anime_tipo</code> ausente mas cache existe. Backfill automático (WP-Cron) ainda não rodou. Force agora: <a href='/?run_jikan_backfill=1' style='color:#60a5fa'>/?run_jikan_backfill=1</a></p>";
	} elseif ( $sem_tipo > 0 ) {
		echo "<p class='warn'>⚠️ {$sem_tipo} de {$total} animes sem <code>anime_tipo</code>. Execute <a href='/?run_jikan_backfill=1' style='color:#60a5fa'>/?run_jikan_backfill=1</a> para corrigir.</p>";
	} else {
		echo "<p class='ok'>✅ Estrutura correta. O problema está na <strong>lógica do filtro</strong>, não nos dados.</p>";
	}

	echo '</body></html>';
	exit;
}


// =========================================================================
// 3b. BACKFILL NOMES ALTERNATIVOS
// Sincroniza anime_nomes_busca / manga_nomes_busca em todos os posts existentes.
// URL: /?run_nomes_backfill=1  (requer login como admin)
// =========================================================================

add_action( 'init', 'mm_run_nomes_backfill' );
function mm_run_nomes_backfill(): void {
	if ( ! isset( $_GET['run_nomes_backfill'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Acesso negado. Faça login como administrador.' );
	}

	set_time_limit( 600 );
	@ini_set( 'memory_limit', '256M' ); // phpcs:ignore

	if ( ob_get_level() ) {
		ob_end_clean();
	}
	header( 'Content-Type: text/html; charset=UTF-8' );

	echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Backfill Nomes Alternativos</title>';
	echo '<style>body{font-family:monospace;background:#111;color:#eee;padding:2rem;line-height:1.8}';
	echo '.ok{color:#4ade80}.skip{color:#facc15}.fail{color:#f87171}';
	echo '.done{font-size:1.3rem;font-weight:bold;color:#a3e635}</style></head><body>';

	// ── Animes ──────────────────────────────────────────────────────────────
	$anime_ids = get_posts( array(
		'post_type'      => 'anime',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );

	$total_anime = count( $anime_ids );
	$updated_a   = 0;
	$skipped_a   = 0;
	$empty_a     = 0;

	echo "<h2>📛 Backfill Nomes Alternativos — {$total_anime} animes</h2><hr>";
	flush();

	foreach ( $anime_ids as $post_id ) {
		$mal_id = (int) get_post_meta( $post_id, 'anime_id_mal', true );
		$titulo = esc_html( get_the_title( $post_id ) );

		if ( $mal_id <= 0 ) {
			echo "<span class='fail'>✗ [ID:{$post_id}] {$titulo} — sem anime_id_mal</span><br>";
			$empty_a++;
			flush();
			continue;
		}

		$existing = get_post_meta( $post_id, 'anime_nomes_busca', true );
		if ( ! empty( $existing ) && ! isset( $_GET['force_all'] ) ) {
			echo "<span class='skip'>→ [ID:{$post_id}] {$titulo} — já sincronizado</span><br>";
			$skipped_a++;
			flush();
			continue;
		}

		$data = get_transient( 'jikan_anime_full_' . $mal_id );
		if ( empty( $data ) ) {
			$data = Jikan_API::get_anime_full( $mal_id );
		}

		if ( empty( $data ) ) {
			echo "<span class='fail'>✗ [MAL:{$mal_id}] {$titulo} — sem dados Jikan</span><br>";
			$empty_a++;
			flush();
			continue;
		}

		$nomes = mm_extract_nomes_busca_from_jikan( $data );
		if ( ! empty( $nomes ) ) {
			update_post_meta( $post_id, 'anime_nomes_busca', $nomes );
			echo "<span class='ok'>✓ [MAL:{$mal_id}] {$titulo} → nomes atualizados</span><br>";
			$updated_a++;
		} else {
			echo "<span class='fail'>✗ [MAL:{$mal_id}] {$titulo} — nenhum nome extraído</span><br>";
			$empty_a++;
		}
		flush();
	}

	echo '<hr>';
	echo "<p>Animes: Total <strong>{$total_anime}</strong> | Atualizados <strong class='ok'>{$updated_a}</strong> | Pulados <strong class='skip'>{$skipped_a}</strong> | Falhas <strong class='fail'>{$empty_a}</strong></p>";
	flush();

	// ── Mangás ──────────────────────────────────────────────────────────────
	$manga_ids = get_posts( array(
		'post_type'      => 'manga',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );

	$total_manga = count( $manga_ids );
	$updated_m   = 0;
	$skipped_m   = 0;
	$empty_m     = 0;

	echo "<h2>📛 Backfill Nomes Alternativos — {$total_manga} mangás</h2><hr>";
	flush();

	foreach ( $manga_ids as $post_id ) {
		$mal_id = (int) get_post_meta( $post_id, 'manga_id_mal', true );
		$titulo = esc_html( get_the_title( $post_id ) );

		if ( $mal_id <= 0 ) {
			echo "<span class='fail'>✗ [ID:{$post_id}] {$titulo} — sem manga_id_mal</span><br>";
			$empty_m++;
			flush();
			continue;
		}

		$existing = get_post_meta( $post_id, 'manga_nomes_busca', true );
		if ( ! empty( $existing ) && ! isset( $_GET['force_all'] ) ) {
			echo "<span class='skip'>→ [ID:{$post_id}] {$titulo} — já sincronizado</span><br>";
			$skipped_m++;
			flush();
			continue;
		}

		$data = get_transient( 'jikan_manga_full_' . $mal_id );
		if ( empty( $data ) ) {
			$data = Jikan_API::get_manga_full( $mal_id );
		}

		if ( empty( $data ) ) {
			echo "<span class='fail'>✗ [MAL:{$mal_id}] {$titulo} — sem dados Jikan</span><br>";
			$empty_m++;
			flush();
			continue;
		}

		$nomes = mm_extract_nomes_busca_from_jikan( $data );
		if ( ! empty( $nomes ) ) {
			update_post_meta( $post_id, 'manga_nomes_busca', $nomes );
			echo "<span class='ok'>✓ [MAL:{$mal_id}] {$titulo} → nomes atualizados</span><br>";
			$updated_m++;
		} else {
			echo "<span class='fail'>✗ [MAL:{$mal_id}] {$titulo} — nenhum nome extraído</span><br>";
			$empty_m++;
		}
		flush();
	}

	echo '<hr>';
	echo "<p class='done'>✅ Backfill concluído!</p>";
	echo "<p>Mangás: Total <strong>{$total_manga}</strong> | Atualizados <strong class='ok'>{$updated_m}</strong> | Pulados <strong class='skip'>{$skipped_m}</strong> | Falhas <strong class='fail'>{$empty_m}</strong></p>";
	echo '<p>A busca agora inclui nomes alternativos (títulos em japonês, inglês e sinônimos).</p>';
	echo '</body></html>';
	exit;
}


// =========================================================================
// 4. BACKFILL SINOPSE → post_content
// Sincroniza anime_sinopse (ACF/meta) para post_content em todos os animes.
// URL: /?run_sinopse_backfill=1  (requer login como admin)
// =========================================================================

add_action( 'init', 'mm_run_sinopse_backfill' );
function mm_run_sinopse_backfill(): void {
	if ( ! isset( $_GET['run_sinopse_backfill'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Acesso negado. Faça login como administrador.' );
	}

	set_time_limit( 600 );
	@ini_set( 'memory_limit', '256M' ); // phpcs:ignore

	$anime_ids = get_posts( array(
		'post_type'      => 'anime',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );

	$total   = count( $anime_ids );
	$updated = 0;
	$skipped = 0;
	$empty   = 0;

	if ( ob_get_level() ) {
		ob_end_clean();
	}
	header( 'Content-Type: text/html; charset=UTF-8' );

	echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Backfill Sinopse</title>';
	echo '<style>body{font-family:monospace;background:#111;color:#eee;padding:2rem;line-height:1.8}';
	echo '.ok{color:#4ade80}.skip{color:#facc15}.fail{color:#f87171}';
	echo '.done{font-size:1.3rem;font-weight:bold;color:#a3e635}</style></head><body>';
	echo "<h2>📖 Backfill Sinopse → post_content — {$total} animes</h2><hr>";
	flush();

	foreach ( $anime_ids as $post_id ) {
		$post    = get_post( $post_id );
		$titulo  = esc_html( $post->post_title );
		$sinopse = '';

		if ( function_exists( 'get_field' ) ) {
			$sinopse = get_field( 'anime_sinopse', $post_id );
		}
		if ( empty( $sinopse ) ) {
			$sinopse = get_post_meta( $post_id, 'anime_sinopse', true );
		}

		if ( empty( $sinopse ) ) {
			echo "<span class='fail'>✗ [ID:{$post_id}] {$titulo} — sinopse vazia</span><br>";
			$empty++;
			flush();
			continue;
		}

		if ( $sinopse === $post->post_content ) {
			echo "<span class='skip'>→ [ID:{$post_id}] {$titulo} — já sincronizado</span><br>";
			$skipped++;
			flush();
			continue;
		}

		wp_update_post( array(
			'ID'           => $post_id,
			'post_content' => $sinopse,
		) );

		echo "<span class='ok'>✓ [ID:{$post_id}] {$titulo} — sincronizado</span><br>";
		$updated++;
		flush();
	}

	echo '<hr>';
	echo "<p class='done'>✅ Backfill concluído!</p>";
	echo "<p>Total: <strong>{$total}</strong> | ";
	echo "Atualizados: <strong class='ok'>{$updated}</strong> | ";
	echo "Já sincronizados: <strong class='skip'>{$skipped}</strong> | ";
	echo "Sinopse vazia: <strong class='fail'>{$empty}</strong></p>";
	echo '<p>A busca textual no Catálogo agora funciona por sinopse.</p>';
	echo '</body></html>';
	exit;
}


// =========================================================================
// 5. BACKFILL SINOPSE → post_content (MANGÁS)
// Sincroniza manga_sinopse_manual (ACF/meta) para post_content em todos os mangás.
// URL: /?run_manga_sinopse_backfill=1  (requer login como admin)
// =========================================================================

add_action( 'init', 'mm_run_manga_sinopse_backfill' );
function mm_run_manga_sinopse_backfill(): void {
	if ( ! isset( $_GET['run_manga_sinopse_backfill'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Acesso negado. Faça login como administrador.' );
	}

	set_time_limit( 600 );
	@ini_set( 'memory_limit', '256M' ); // phpcs:ignore

	$manga_ids = get_posts( array(
		'post_type'      => 'manga',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );

	$total   = count( $manga_ids );
	$updated = 0;
	$skipped = 0;
	$empty   = 0;

	if ( ob_get_level() ) {
		ob_end_clean();
	}
	header( 'Content-Type: text/html; charset=UTF-8' );

	echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Backfill Sinopse Mangás</title>';
	echo '<style>body{font-family:monospace;background:#111;color:#eee;padding:2rem;line-height:1.8}';
	echo '.ok{color:#4ade80}.skip{color:#facc15}.fail{color:#f87171}';
	echo '.done{font-size:1.3rem;font-weight:bold;color:#a3e635}</style></head><body>';
	echo "<h2>📖 Backfill Sinopse Mangás → post_content — {$total} mangás</h2><hr>";
	flush();

	foreach ( $manga_ids as $post_id ) {
		$post    = get_post( $post_id );
		$titulo  = esc_html( $post->post_title );
		$sinopse = '';

		if ( function_exists( 'get_field' ) ) {
			$sinopse = get_field( 'manga_sinopse_manual', $post_id );
		}
		if ( empty( $sinopse ) ) {
			$sinopse = get_post_meta( $post_id, 'manga_sinopse_manual', true );
		}

		if ( empty( $sinopse ) ) {
			echo "<span class='fail'>✗ [ID:{$post_id}] {$titulo} — sinopse vazia</span><br>";
			$empty++;
			flush();
			continue;
		}

		if ( $sinopse === $post->post_content ) {
			echo "<span class='skip'>→ [ID:{$post_id}] {$titulo} — já sincronizado</span><br>";
			$skipped++;
			flush();
			continue;
		}

		wp_update_post( array(
			'ID'           => $post_id,
			'post_content' => $sinopse,
		) );

		echo "<span class='ok'>✓ [ID:{$post_id}] {$titulo} — sincronizado</span><br>";
		$updated++;
		flush();
	}

	echo '<hr>';
	echo "<p class='done'>✅ Backfill concluído!</p>";
	echo "<p>Total: <strong>{$total}</strong> | ";
	echo "Atualizados: <strong class='ok'>{$updated}</strong> | ";
	echo "Já sincronizados: <strong class='skip'>{$skipped}</strong> | ";
	echo "Sinopse vazia: <strong class='fail'>{$empty}</strong></p>";
	echo '<p>A busca textual no Catálogo agora funciona por sinopse de mangás.</p>';
	echo '</body></html>';
	exit;
}

