<?php
/**
 * MangaDex API Cache Manager
 *
 * Responsável por buscar dados de mangás na MangaDex API v5 que não estão
 * disponíveis na Jikan/MAL, especialmente a estrutura de volumes e capítulos.
 *
 * Implementa Shadow Cache (Stale-While-Revalidate) idêntico ao padrão Jikan.
 *
 * Fluxo de dados:
 *  MAL ID → GET /manga?title={t} → cruzar links.mal → UUID MangaDex
 *  UUID → GET /manga/{uuid}/aggregate → volumes + capítulos normalizados
 *
 * @package geek-ao-cubo
 * @since   2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MangaDex_API {

	private static $base_url = 'https://api.mangadex.org';

	// =========================================================================
	// LOOKUP: MAL ID → UUID MangaDex
	// =========================================================================

	/**
	 * Retorna o UUID MangaDex de um mangá a partir do MAL ID.
	 *
	 * Estratégia de cache (em ordem de prioridade):
	 *  1. postmeta 'manga_mangadex_uuid' (permanente, salvo no WP)
	 *  2. transient 'mdx_uuid_{mal_id}' (30 dias)
	 *  3. fetch da API (busca por título + cruzamento com links.mal)
	 *
	 * @param int    $mal_id   ID do mangá no MyAnimeList.
	 * @param string $title    Título do post (usado na busca por título).
	 * @param int    $post_id  ID do post WP (para salvar UUID no postmeta).
	 * @return string|false UUID ou false se não encontrado.
	 */
	public static function get_manga_uuid( int $mal_id, string $title, int $post_id = 0 ) {
		if ( ! $mal_id || ! $title ) {
			return false;
		}

		// 1. Postmeta — cache mais duradouro (definido manualmente ou auto-populado)
		if ( $post_id > 0 ) {
			$uuid = get_post_meta( $post_id, 'manga_mangadex_uuid', true );
			if ( $uuid ) {
				return $uuid;
			}
		}

		// 2. Transient
		$transient_key = 'mdx_uuid_' . (int) $mal_id;
		$cached        = get_transient( $transient_key );

		if ( false !== $cached ) {
			return $cached ?: false; // '' salvo = não encontrado anteriormente
		}

		// 3. Fetch da API
		return self::fetch_and_cache_uuid( $mal_id, $title, $post_id );
	}

	/**
	 * Busca o UUID no MangaDex via título e valida pelo MAL ID nos links.
	 *
	 * @param int    $mal_id   MAL ID.
	 * @param string $title    Título para busca.
	 * @param int    $post_id  Post ID para salvar o UUID no postmeta.
	 * @return string|false UUID ou false.
	 */
	private static function fetch_and_cache_uuid( int $mal_id, string $title, int $post_id = 0 ) {
		usleep( 350000 ); // Throttle: 350ms para respeitar rate limits

		$url = add_query_arg( array(
			'title' => $title,
			'limit' => 10,
		), self::$base_url . '/manga' );

		$response = wp_remote_get( $url, array(
			'timeout'    => 15,
			'user-agent' => 'GeekAoCubo/2.1.0 (https://geekao3.com)',
		) );

		$transient_key = 'mdx_uuid_' . $mal_id;

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			set_transient( $transient_key, '', 10 * MINUTE_IN_SECONDS );
			return false;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $data['data'] ) ) {
			set_transient( $transient_key, '', HOUR_IN_SECONDS );
			return false;
		}

		// Cruzar pelo links.mal nos resultados
		$uuid = false;
		foreach ( $data['data'] as $manga ) {
			$links = $manga['attributes']['links'] ?? array();
			if ( isset( $links['mal'] ) && (int) $links['mal'] === $mal_id ) {
				$uuid = $manga['id'];
				break;
			}
		}

		if ( $uuid ) {
			// Cache positivo: 30 dias
			set_transient( $transient_key, $uuid, 30 * DAY_IN_SECONDS );

			// Persiste no postmeta para cache permanente
			if ( $post_id > 0 ) {
				update_post_meta( $post_id, 'manga_mangadex_uuid', sanitize_text_field( $uuid ) );
			}
		} else {
			// Cache negativo: 1 hora (evita refetch constante para títulos não encontrados)
			set_transient( $transient_key, '', HOUR_IN_SECONDS );
		}

		return $uuid ?: false;
	}


	// =========================================================================
	// AGGREGATE: UUID → Volumes + Capítulos
	// =========================================================================

	/**
	 * Retorna os volumes e capítulos de um mangá normalizados.
	 *
	 * @param string $uuid UUID MangaDex do mangá.
	 * @return array Dados normalizados (veja self::normalize_aggregate).
	 */
	public static function get_manga_aggregate( string $uuid ) {
		if ( ! $uuid ) {
			return array();
		}

		$transient_key = 'mdx_aggregate_' . sanitize_key( $uuid );

		// Shadow Cache (SWR)
		$timeout = get_option( '_transient_timeout_' . $transient_key );
		if ( $timeout && $timeout < time() ) {
			$stale = get_option( '_transient_' . $transient_key );

			$lock_key = 'lock_' . $transient_key;
			if ( ! get_transient( $lock_key ) ) {
				set_transient( $lock_key, true, 2 * MINUTE_IN_SECONDS );
				if ( ! wp_next_scheduled( 'mm_async_mdx_update_aggregate', array( $uuid ) ) ) {
					wp_schedule_single_event( time(), 'mm_async_mdx_update_aggregate', array( $uuid ) );
				}
			}

			if ( $stale ) {
				return $stale;
			}
		}

		$cached = get_transient( $transient_key );
		if ( false !== $cached ) {
			return $cached;
		}

		return self::fetch_and_cache_aggregate( $uuid );
	}

	/**
	 * Faz o fetch do aggregate e salva no cache.
	 *
	 * @param string $uuid UUID MangaDex.
	 * @return array Dados normalizados ou array vazio.
	 */
	public static function fetch_and_cache_aggregate( string $uuid ) {
		usleep( 350000 );

		$url = self::$base_url . '/manga/' . $uuid . '/aggregate';

		$response = wp_remote_get( $url, array(
			'timeout'    => 15,
			'user-agent' => 'GeekAoCubo/2.1.0 (https://geekao3.com)',
		) );

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return array();
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $data['volumes'] ) ) {
			return array();
		}

		$normalized = self::normalize_aggregate( $data['volumes'] );

		set_transient( 'mdx_aggregate_' . sanitize_key( $uuid ), $normalized, 7 * DAY_IN_SECONDS );

		return $normalized;
	}


	// =========================================================================
	// HELPERS DE NORMALIZAÇÃO
	// =========================================================================

	/**
	 * Normaliza o `volumes` bruto da API em um array limpo e ordenado.
	 *
	 * Deduplica capítulos com mesmo número (diferentes grupos de tradução).
	 * Ordena volumes e capítulos numericamente.
	 * Move volumes "none" (sem volume atribuído) para o final.
	 *
	 * @param array $raw_volumes Raw `volumes` da resposta da API.
	 * @return array {
	 *     @type int   $total_chapters Número total de capítulos únicos.
	 *     @type int   $total_volumes  Número de volumes numerados.
	 *     @type array $volumes        Array ordenado de volumes.
	 * }
	 */
	private static function normalize_aggregate( array $raw_volumes ) : array {
		$volumes        = array();
		$total_chapters = 0;

		foreach ( $raw_volumes as $vol_key => $vol_data ) {
			$chapters     = array();
			$seen_numbers = array(); // Deduplica capítulos com mesmo número

			if ( ! empty( $vol_data['chapters'] ) ) {
				foreach ( $vol_data['chapters'] as $chap_data ) {
					$chap_number = $chap_data['chapter'] ?? '';

					// Pula duplicatas (mesmo capítulo, grupo de tradução diferente)
					if ( isset( $seen_numbers[ $chap_number ] ) ) {
						continue;
					}
					$seen_numbers[ $chap_number ] = true;

					$chapters[] = array(
						'number' => $chap_number,
						'id'     => $chap_data['id'],
						'url'    => 'https://mangadex.org/chapter/' . $chap_data['id'],
					);
				}

				// Ordena capítulos numericamente
				usort( $chapters, function ( $a, $b ) {
					return (float) $a['number'] <=> (float) $b['number'];
				} );
			}

			$total_chapters += count( $chapters );

			$volumes[] = array(
				'volume'   => 'none' === $vol_key ? '' : $vol_key,
				'is_none'  => 'none' === $vol_key,
				'count'    => count( $chapters ),
				'chapters' => $chapters,
			);
		}

		// Ordena volumes numericamente; "none" vai para o final
		usort( $volumes, function ( $a, $b ) {
			if ( $a['is_none'] ) return 1;
			if ( $b['is_none'] ) return -1;
			return (float) $a['volume'] <=> (float) $b['volume'];
		} );

		$total_volumes = count( array_filter( $volumes, fn( $v ) => ! $v['is_none'] ) );

		return array(
			'total_chapters' => $total_chapters,
			'total_volumes'  => $total_volumes,
			'volumes'        => $volumes,
		);
	}

	/**
	 * Extrai todos os capítulos achatados (sem divisão por volume) do aggregate.
	 *
	 * @param array $aggregate Retorno de get_manga_aggregate().
	 * @return array Array de capítulos ordenados numericamente.
	 */
	public static function get_all_chapters( array $aggregate ) : array {
		$chapters = array();

		if ( empty( $aggregate['volumes'] ) ) {
			return $chapters;
		}

		foreach ( $aggregate['volumes'] as $vol ) {
			foreach ( $vol['chapters'] as $chap ) {
				$chap['volume'] = $vol['volume'];
				$chapters[]     = $chap;
			}
		}

		usort( $chapters, function ( $a, $b ) {
			return (float) $a['number'] <=> (float) $b['number'];
		} );

		return $chapters;
	}
}

// Listener WP-Cron para revalidação assíncrona do aggregate (Shadow Cache)
add_action( 'mm_async_mdx_update_aggregate', array( 'MangaDex_API', 'fetch_and_cache_aggregate' ) );
