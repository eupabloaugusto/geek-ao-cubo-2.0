<?php
/**
 * Jikan API Cache Manager
 *
 * Responsável por atuar como banco de dados em tempo real para os animes do Geek ao Cubo.
 * Implementa Shadow Cache (Stale-While-Revalidate) para garantir 100% de uptime.
 */

class Jikan_API {
	private static $base_url = 'https://api.jikan.moe/v4';
	
	/**
	 * Busca dados completos de um anime (Cache ou API)
	 */
	public static function get_anime_full( $mal_id ) {
		if ( ! $mal_id ) return null;
		
		$cache_key = 'jikan_anime_full_' . $mal_id;
		
		// 1. SWR: Checa o tempo de validade diretamente no banco
		$timeout = get_option( '_transient_timeout_' . $cache_key );
		if ( $timeout && $timeout < time() ) {
			// Cache expirado. Pegamos o backup antes do WordPress apagá-lo.
			$stale_data = get_option( '_transient_' . $cache_key );
			
			// 2. Mutex Lock: Tranca Anti-Manada
			$lock_key = 'lock_' . $cache_key;
			if ( ! get_transient( $lock_key ) ) {
				set_transient( $lock_key, true, 2 * MINUTE_IN_SECONDS );
				
				// 3. Agenda a atualização silenciosa em Background via WP-Cron
				if ( ! wp_next_scheduled( 'mm_async_jikan_update_full', array( $mal_id ) ) ) {
					wp_schedule_single_event( time(), 'mm_async_jikan_update_full', array( $mal_id ) );
				}
			}
			
			if ( $stale_data ) {
				return $stale_data; // Entrega o cache velho pro usuário instantaneamente
			}
		}
		
		// Fluxo Normal (Cache válido)
		$cached_data = get_transient( $cache_key );
		
		if ( false !== $cached_data ) {
			return $cached_data;
		}
		
		// Sem cache. Carga inicial (irá rodar o sleep de 350ms dentro da função)
		return self::fetch_and_cache_anime( $mal_id );
	}

	/**
	 * Busca dados completos de um Mangá (com Shadow Cache).
	 *
	 * @param int $mal_id ID do mangá no MAL.
	 * @return array
	 */
	public static function get_manga_full( $mal_id ) {
		if ( ! $mal_id ) return null;
		
		$cache_key = 'jikan_manga_full_' . $mal_id;
		
		$timeout = get_option( '_transient_timeout_' . $cache_key );
		if ( $timeout && $timeout < time() ) {
			$stale_data = get_option( '_transient_' . $cache_key );
			
			$lock_key = 'lock_' . $cache_key;
			if ( ! get_transient( $lock_key ) ) {
				set_transient( $lock_key, true, 2 * MINUTE_IN_SECONDS );
				if ( ! wp_next_scheduled( 'mm_async_jikan_update_manga_full', array( $mal_id ) ) ) {
					wp_schedule_single_event( time(), 'mm_async_jikan_update_manga_full', array( $mal_id ) );
				}
			}
			
			if ( $stale_data ) {
				return $stale_data;
			}
		}
		
		$cached_data = get_transient( $cache_key );
		if ( false !== $cached_data ) {
			return $cached_data;
		}
		
		return self::fetch_and_cache_manga( $mal_id );
	}
	
	/**
	 * Faz o fetch da Jikan e salva no Shadow Cache
	 */
	public static function fetch_and_cache_anime( $mal_id ) {
		// Throttling: Delay de 350ms para respeitar limite da Jikan API (3/segundo)
		usleep( 350000 );
		
		$url = self::$base_url . '/anime/' . $mal_id . '/full';
		
		$response = wp_remote_get( $url, [
			'timeout' => 15
		]);
		
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			// Retorna o cache antigo se existir (Fallback)
			$stale_cache = get_option( '_transient_jikan_anime_full_' . $mal_id );
			return $stale_cache ? $stale_cache : null;
		}
		
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		
		if ( empty( $data['data'] ) ) {
			return null;
		}
		
		$anime_data = $data['data'];
		
		// Camada 2: Auto-Tradução via DeepL (se configurado)
		if ( defined('DEEPL_API_KEY') && ! empty(DEEPL_API_KEY) && ! empty($anime_data['synopsis']) ) {
			$deepl_url = 'https://api-free.deepl.com/v2/translate';
			$deepl_args = [
				'body' => [
					'auth_key' => DEEPL_API_KEY,
					'text' => $anime_data['synopsis'],
					'target_lang' => 'PT-BR'
				],
				'timeout' => 10
			];
			$deepl_response = wp_remote_post($deepl_url, $deepl_args);
			if ( ! is_wp_error($deepl_response) && wp_remote_retrieve_response_code($deepl_response) === 200 ) {
				$deepl_body = json_decode(wp_remote_retrieve_body($deepl_response), true);
				if ( ! empty($deepl_body['translations'][0]['text']) ) {
					$anime_data['synopsis'] = $deepl_body['translations'][0]['text'];
				}
			}
		}

		// Regra de validade do cache baseada no Status
		$status = strtolower( $anime_data['status'] ?? '' );
		if ( strpos( $status, 'currently airing' ) !== false ) {
			$expiration = DAY_IN_SECONDS; // 1 dia para lançamentos
		} else {
			$expiration = 30 * DAY_IN_SECONDS; // 30 dias para finalizados
		}
		
		set_transient( 'jikan_anime_full_' . $mal_id, $anime_data, $expiration );

		// ── Persiste metadados locais para filtragem via WP_Query ────────────────
		$post_id = function_exists( 'mm_get_anime_post_id_by_mal_id' )
			? mm_get_anime_post_id_by_mal_id( (int) $mal_id )
			: 0;

		if ( $post_id <= 0 ) {
			$local = get_posts( array(
				'post_type'      => 'anime',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_query'     => array(
					array(
						'key'     => 'anime_id_mal',
						'value'   => $mal_id,
						'compare' => '=',
						'type'    => 'NUMERIC',
					),
				),
			) );
			$post_id = ! empty( $local ) ? (int) $local[0] : 0;
		}

		if ( $post_id > 0 ) {
			if ( ! empty( $anime_data['type'] ) ) {
				update_post_meta( $post_id, 'anime_tipo', sanitize_text_field( $anime_data['type'] ) );
			}

			// Fallback imediato até sync de characters detectar dublagem PT-BR.
			if ( ! get_post_meta( $post_id, 'anime_idioma', true ) ) {
				update_post_meta( $post_id, 'anime_idioma', 'legendado' );
			}

			// Agenda fetch de characters para detectar dublagem (se ainda não houver cache).
			if ( ! get_transient( 'jikan_anime_chars_' . $mal_id )
				&& ! wp_next_scheduled( 'mm_async_jikan_update_chars', array( $mal_id ) ) ) {
				wp_schedule_single_event( time() + 10, 'mm_async_jikan_update_chars', array( $mal_id ) );
			}
		}

		return $anime_data;
	}

	public static function fetch_and_cache_manga( $mal_id ) {
		usleep( 350000 );
		$url = self::$base_url . '/manga/' . $mal_id . '/full';
		$response = wp_remote_get( $url, ['timeout' => 15]);
		
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$stale_cache = get_option( '_transient_jikan_manga_full_' . $mal_id );
			return $stale_cache ? $stale_cache : null;
		}
		
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $data['data'] ) ) return null;
		
		set_transient( 'jikan_manga_full_' . $mal_id, $data['data'], 30 * DAY_IN_SECONDS );
		return $data['data'];
	}
	
	/**
	 * Busca dados completos de um personagem (Cache ou API)
	 */
	public static function get_character_full( $mal_id ) {
		if ( ! $mal_id ) return null;
		
		$cache_key = 'jikan_char_full_' . $mal_id;
		
		$timeout = get_option( '_transient_timeout_' . $cache_key );
		if ( $timeout && $timeout < time() ) {
			$stale_data = get_option( '_transient_' . $cache_key );
			$lock_key = 'lock_' . $cache_key;
			
			if ( ! get_transient( $lock_key ) ) {
				set_transient( $lock_key, true, 2 * MINUTE_IN_SECONDS );
				if ( ! wp_next_scheduled( 'mm_async_jikan_update_char_full', array( $mal_id ) ) ) {
					wp_schedule_single_event( time(), 'mm_async_jikan_update_char_full', array( $mal_id ) );
				}
			}
			
			if ( $stale_data ) {
				return $stale_data;
			}
		}
		
		$cached_data = get_transient( $cache_key );
		if ( false !== $cached_data ) {
			return $cached_data;
		}
		
		return self::fetch_and_cache_character_full( $mal_id );
	}
	
	/**
	 * Faz o fetch da Jikan para o Personagem Completo e salva no Cache
	 */
	public static function fetch_and_cache_character_full( $mal_id ) {
		usleep( 350000 );
		$url = self::$base_url . '/characters/' . $mal_id . '/full';
		
		$response = wp_remote_get( $url, [
			'timeout' => 15
		]);
		
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$stale_cache = get_option( '_transient_jikan_char_full_' . $mal_id );
			return $stale_cache ? $stale_cache : null;
		}
		
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		
		if ( empty( $data['data'] ) ) {
			return null;
		}
		
		$char_data = $data['data'];
		
		// 30 dias de cache para dados de personagens
		$expiration = 30 * DAY_IN_SECONDS; 
		set_transient( 'jikan_char_full_' . $mal_id, $char_data, $expiration );
		
		return $char_data;
	}

	/**
	 * Busca os episódios completos do anime
	 */
	public static function get_anime_episodes( $mal_id ) {
		if ( ! $mal_id ) return null;
		
		$cache_key = 'jikan_anime_eps_' . $mal_id;
		
		$timeout = get_option( '_transient_timeout_' . $cache_key );
		if ( $timeout && $timeout < time() ) {
			$stale_data = get_option( '_transient_' . $cache_key );
			$lock_key = 'lock_' . $cache_key;
			
			if ( ! get_transient( $lock_key ) ) {
				set_transient( $lock_key, true, 2 * MINUTE_IN_SECONDS );
				if ( ! wp_next_scheduled( 'mm_async_jikan_update_eps', array( $mal_id ) ) ) {
					wp_schedule_single_event( time(), 'mm_async_jikan_update_eps', array( $mal_id ) );
				}
			}
			
			if ( $stale_data ) {
				return $stale_data;
			}
		}
		
		$cached_data = get_transient( $cache_key );
		
		if ( false !== $cached_data ) {
			return $cached_data;
		}
		
		return self::fetch_and_cache_episodes( $mal_id );
	}
	
	public static function fetch_and_cache_episodes( $mal_id ) {
		usleep( 350000 );
		$url = self::$base_url . '/anime/' . $mal_id . '/episodes';
		
		$response = wp_remote_get( $url, [
			'timeout' => 15
		]);
		
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$stale_cache = get_option( '_transient_jikan_anime_eps_' . $mal_id );
			return $stale_cache ? $stale_cache : [];
		}
		
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		
		$episodes = $data['data'] ?? [];
		
		// Cache fixo de 1 dia, pois o anime que ganha episódio sempre precisará de renovação frequente
		set_transient( 'jikan_anime_eps_' . $mal_id, $episodes, DAY_IN_SECONDS );
		
		return $episodes;
	}

	/**
	 * Busca os personagens de um anime
	 */
	public static function get_anime_characters( $mal_id ) {
		if ( ! $mal_id ) return null;
		
		$cache_key = 'jikan_anime_chars_' . $mal_id;
		
		$timeout = get_option( '_transient_timeout_' . $cache_key );
		if ( $timeout && $timeout < time() ) {
			$stale_data = get_option( '_transient_' . $cache_key );
			$lock_key = 'lock_' . $cache_key;
			
			if ( ! get_transient( $lock_key ) ) {
				set_transient( $lock_key, true, 2 * MINUTE_IN_SECONDS );
				if ( ! wp_next_scheduled( 'mm_async_jikan_update_chars', array( $mal_id ) ) ) {
					wp_schedule_single_event( time(), 'mm_async_jikan_update_chars', array( $mal_id ) );
				}
			}
			
			if ( $stale_data ) {
				return $stale_data;
			}
		}
		
		$cached_data = get_transient( $cache_key );
		if ( false !== $cached_data ) {
			return $cached_data;
		}
		
		return self::fetch_and_cache_characters( $mal_id );
	}
	
	public static function fetch_and_cache_characters( $mal_id ) {
		usleep( 350000 );
		$url = self::$base_url . '/anime/' . $mal_id . '/characters';
		
		$response = wp_remote_get( $url, [
			'timeout' => 15
		]);
		
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$stale_cache = get_option( '_transient_jikan_anime_chars_' . $mal_id );
			return $stale_cache ? $stale_cache : [];
		}
		
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		
		$characters = $data['data'] ?? [];
		
		set_transient( 'jikan_anime_chars_' . $mal_id, $characters, 30 * DAY_IN_SECONDS );

		if ( function_exists( 'mm_sync_anime_idioma_meta' ) ) {
			mm_sync_anime_idioma_meta( (int) $mal_id, 0, $characters );
		}

		return $characters;
	}

	/**
	 * Busca os personagens de um mangá
	 */
	public static function get_manga_characters( $mal_id ) {
		if ( ! $mal_id ) return null;
		
		$cache_key = 'jikan_manga_chars_' . $mal_id;
		
		$timeout = get_option( '_transient_timeout_' . $cache_key );
		if ( $timeout && $timeout < time() ) {
			$stale_data = get_option( '_transient_' . $cache_key );
			$lock_key = 'lock_' . $cache_key;
			
			if ( ! get_transient( $lock_key ) ) {
				set_transient( $lock_key, true, 2 * MINUTE_IN_SECONDS );
				if ( ! wp_next_scheduled( 'mm_async_jikan_update_manga_chars', array( $mal_id ) ) ) {
					wp_schedule_single_event( time(), 'mm_async_jikan_update_manga_chars', array( $mal_id ) );
				}
			}
			
			if ( $stale_data ) {
				return $stale_data;
			}
		}
		
		$cached_data = get_transient( $cache_key );
		if ( false !== $cached_data ) {
			return $cached_data;
		}
		
		return self::fetch_and_cache_manga_characters( $mal_id );
	}
	
	public static function fetch_and_cache_manga_characters( $mal_id ) {
		usleep( 350000 );
		$url = self::$base_url . '/manga/' . $mal_id . '/characters';
		
		$response = wp_remote_get( $url, [
			'timeout' => 15
		]);
		
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$stale_cache = get_option( '_transient_jikan_manga_chars_' . $mal_id );
			return $stale_cache ? $stale_cache : [];
		}
		
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		
		$characters = $data['data'] ?? [];
		
		set_transient( 'jikan_manga_chars_' . $mal_id, $characters, 30 * DAY_IN_SECONDS );
		
		return $characters;
	}

	/**
	 * Busca dados completos de uma pessoa (Dublador/Staff)
	 */
	public static function get_person_full( $mal_id ) {
		if ( ! $mal_id ) return null;
		
		$cache_key = 'jikan_person_full_' . $mal_id;
		
		$timeout = get_option( '_transient_timeout_' . $cache_key );
		if ( $timeout && $timeout < time() ) {
			$stale_data = get_option( '_transient_' . $cache_key );
			$lock_key = 'lock_' . $cache_key;
			
			if ( ! get_transient( $lock_key ) ) {
				set_transient( $lock_key, true, 2 * MINUTE_IN_SECONDS );
				if ( ! wp_next_scheduled( 'mm_async_jikan_update_person', array( $mal_id ) ) ) {
					wp_schedule_single_event( time(), 'mm_async_jikan_update_person', array( $mal_id ) );
				}
			}
			
			if ( $stale_data ) {
				return $stale_data;
			}
		}
		
		$cached_data = get_transient( $cache_key );
		
		if ( false !== $cached_data ) {
			return $cached_data;
		}
		
		return self::fetch_and_cache_person( $mal_id );
	}
	
	public static function fetch_and_cache_person( $mal_id ) {
		usleep( 350000 );
		$url = self::$base_url . '/people/' . $mal_id . '/full';
		
		$response = wp_remote_get( $url, [
			'timeout' => 15
		]);
		
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$stale_cache = get_option( '_transient_jikan_person_full_' . $mal_id );
			return $stale_cache ? $stale_cache : null;
		}
		
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		
		if ( empty( $data['data'] ) ) {
			return null;
		}
		
		$person_data = $data['data'];
		
		// Cache de 30 dias para pessoas, pois seus dados básicos e fotos raramente mudam
		set_transient( 'jikan_person_full_' . $mal_id, $person_data, 30 * DAY_IN_SECONDS );
		
		return $person_data;
	}

	/**
	 * Busca o cronograma de animes de hoje
	 */
	public static function get_schedules_today() {
		$days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
		$today = $days[ date('w') ]; // 0 = sunday, 6 = saturday
		
		$cache_key = 'jikan_schedule_' . $today;
		$cached_data = get_transient( $cache_key );
		
		if ( false !== $cached_data ) {
			return $cached_data;
		}
		
		$url = self::$base_url . '/schedules?filter=' . $today . '&sfw=true';
		$response = wp_remote_get( $url, ['timeout' => 15] );
		
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$stale_cache = get_option( '_transient_' . $cache_key );
			return $stale_cache ? $stale_cache : [];
		}
		
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		$schedule = $data['data'] ?? [];
		
		// Filtro secundário SFW: bloqueia material adulto explícito via PHP
		if ( ! empty( $schedule ) ) {
			$schedule = array_filter( $schedule, function( $anime ) {
				$all_genres = array_merge( $anime['genres'] ?? [], $anime['explicit_genres'] ?? [] );
				foreach ( $all_genres as $genre ) {
					$genre_name = strtolower( $genre['name'] ?? '' );
					if ( in_array( $genre_name, [ 'hentai', 'erotica', 'rx', 'adult' ], true ) ) {
						return false; // Bloqueia!
					}
				}
				return true; // SFW
			});
			// Reindexa as chaves do array após o filtro
			$schedule = array_values( $schedule );
		}
		
		set_transient( $cache_key, $schedule, DAY_IN_SECONDS );
		
		return $schedule;
	}

	/**
	 * Camada 1: Dicionário Estático para Gêneros
	 */
	public static function translate_genre( $genre_name ) {
		$map = [
			'Action' => 'Ação',
			'Adventure' => 'Aventura',
			'Comedy' => 'Comédia',
			'Drama' => 'Drama',
			'Fantasy' => 'Fantasia',
			'Horror' => 'Terror',
			'Mystery' => 'Mistério',
			'Romance' => 'Romance',
			'Sci-Fi' => 'Ficção Científica',
			'Slice of Life' => 'Cotidiano',
			'Sports' => 'Esportes',
			'Supernatural' => 'Sobrenatural',
			'Suspense' => 'Suspense',
			'Boys Love' => 'Yaoi',
			'Girls Love' => 'Yuri',
			'Gourmet' => 'Culinária',
			'Award Winning' => 'Premiados',
			'Ecchi' => 'Ecchi',
			'Avant Garde' => 'Avant Garde',
		];
		return $map[ $genre_name ] ?? $genre_name;
	}

	/**
	 * Camada 1: Dicionário Estático para Status
	 */
	public static function translate_status( $status ) {
		$map = [
			'Currently Airing' => 'Em Exibição',
			'Finished Airing' => 'Finalizado',
			'Not yet aired' => 'Em Breve',
			'Publishing' => 'Em Publicação',
			'Finished' => 'Finalizado',
			'On Hiatus' => 'Em Hiato',
			'Discontinued' => 'Descontinuado',
		];
		return $map[ $status ] ?? $status;
	}

	/**
	 * Camada 1: Dicionário Estático para Relações de Franquia
	 */
	public static function translate_relation( $relation ) {
		$map = [
			'Sequel' => 'Sequência',
			'Prequel' => 'Temporada Anterior',
			'Alternative setting' => 'Universo Alternativo',
			'Alternative version' => 'Versão Alternativa',
			'Side story' => 'História Paralela',
			'Parent story' => 'História Principal',
			'Summary' => 'Resumo / Recap',
			'Character' => 'Personagem',
			'Spin-off' => 'Spin-off',
			'Adaptation' => 'Adaptação',
		];
		return $map[ $relation ] ?? $relation;
	}

	/**
	 * Busca recomendações de mangás
	 */
	public static function get_manga_recommendations( $mal_id ) {
		if ( ! $mal_id ) return [];
		
		$cache_key = 'jikan_manga_recs_' . $mal_id;
		
		$timeout = get_option( '_transient_timeout_' . $cache_key );
		if ( $timeout && $timeout < time() ) {
			$stale_data = get_option( '_transient_' . $cache_key );
			$lock_key = 'lock_' . $cache_key;
			
			if ( ! get_transient( $lock_key ) ) {
				set_transient( $lock_key, true, 2 * MINUTE_IN_SECONDS );
				if ( ! wp_next_scheduled( 'mm_async_jikan_update_manga_recs', array( $mal_id ) ) ) {
					wp_schedule_single_event( time(), 'mm_async_jikan_update_manga_recs', array( $mal_id ) );
				}
			}
			
			if ( $stale_data ) {
				return $stale_data;
			}
		}
		
		$cached_data = get_transient( $cache_key );
		if ( false !== $cached_data ) {
			return $cached_data;
		}
		
		return self::fetch_and_cache_manga_recommendations( $mal_id );
	}

	public static function fetch_and_cache_manga_recommendations( $mal_id ) {
		usleep( 350000 );
		$url = self::$base_url . '/manga/' . $mal_id . '/recommendations';
		$response = wp_remote_get( $url, ['timeout' => 15]);
		
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$stale_cache = get_option( '_transient_jikan_manga_recs_' . $mal_id );
			return $stale_cache ? $stale_cache : [];
		}
		
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $data['data'] ) ) return [];
		
		set_transient( 'jikan_manga_recs_' . $mal_id, $data['data'], 30 * DAY_IN_SECONDS );
		return $data['data'];
	}
}

// ──// Registra os listeners do WP-Cron para atualizar o cache assincronamente (Shadow Cache / SWR)
add_action( 'mm_async_jikan_update_full', array( 'Jikan_API', 'fetch_and_cache_anime' ) );
add_action( 'mm_async_jikan_update_eps', array( 'Jikan_API', 'fetch_and_cache_episodes' ) );
add_action( 'mm_async_jikan_update_chars', array( 'Jikan_API', 'fetch_and_cache_characters' ) );
add_action( 'mm_async_jikan_update_char_full', array( 'Jikan_API', 'fetch_and_cache_character_full' ) );
add_action( 'mm_async_jikan_update_person', array( 'Jikan_API', 'fetch_and_cache_person' ) );
add_action( 'mm_async_jikan_update_manga_full', array( 'Jikan_API', 'fetch_and_cache_manga' ) );
add_action( 'mm_async_jikan_update_manga_chars', array( 'Jikan_API', 'fetch_and_cache_manga_characters' ) );
add_action( 'mm_async_jikan_update_manga_recs', array( 'Jikan_API', 'fetch_and_cache_manga_recommendations' ) );
