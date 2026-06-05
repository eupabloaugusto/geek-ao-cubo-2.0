<?php
/**
 * Jikan API Cache Manager
 *
 * ResponsÃ¡vel por atuar como banco de dados em tempo real para os animes do Vibe Animes.
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
			// Cache expirado. Pegamos o backup antes do WordPress apagÃ¡-lo.
			$stale_data = get_option( '_transient_' . $cache_key );
			
			// 2. Mutex Lock: Tranca Anti-Manada
			$lock_key = 'lock_' . $cache_key;
			if ( ! get_transient( $lock_key ) ) {
				set_transient( $lock_key, true, 2 * MINUTE_IN_SECONDS );
				
				// 3. Agenda a atualizaÃ§Ã£o silenciosa em Background via WP-Cron
				if ( ! wp_next_scheduled( 'mm_async_jikan_update_full', array( $mal_id ) ) ) {
					wp_schedule_single_event( time(), 'mm_async_jikan_update_full', array( $mal_id ) );
				}
			}
			
			if ( $stale_data ) {
				return $stale_data; // Entrega o cache velho pro usuÃ¡rio instantaneamente
			}
		}
		
		// Fluxo Normal (Cache vÃ¡lido)
		$cached_data = get_transient( $cache_key );
		
		if ( false !== $cached_data ) {
			return $cached_data;
		}
		
		// Sem cache. Carga inicial (irÃ¡ rodar o sleep de 350ms dentro da funÃ§Ã£o)
		return self::fetch_and_cache_anime( $mal_id );
	}

	/**
	 * Busca dados completos de um MangÃ¡ (com Shadow Cache).
	 *
	 * @param int $mal_id ID do mangÃ¡ no MAL.
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
		
		// Camada 2: Auto-TraduÃ§Ã£o via DeepL (se configurado)
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
			$expiration = DAY_IN_SECONDS; // 1 dia para lanÃ§amentos
		} else {
			$expiration = 30 * DAY_IN_SECONDS; // 30 dias para finalizados
		}
		
		set_transient( 'jikan_anime_full_' . $mal_id, $anime_data, $expiration );

		// â”€â”€ Persiste metadados locais para filtragem via WP_Query â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
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

			// Fallback imediato atÃ© sync de characters detectar dublagem PT-BR.
			if ( ! get_post_meta( $post_id, 'anime_idioma', true ) ) {
				update_post_meta( $post_id, 'anime_idioma', 'legendado' );
			}

			// Agenda fetch de characters para detectar dublagem (se ainda nÃ£o houver cache).
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
	 * Busca os episÃ³dios completos do anime
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
		$episodes = [];
		$page = 1;
		$has_next = true;

		while ( $has_next && $page <= 20 ) { // Limite de 20 pÃ¡ginas (2000 episÃ³dios) de seguranÃ§a
			usleep( 350000 );
			$url = self::$base_url . '/anime/' . $mal_id . '/episodes?page=' . $page;
			
			$response = wp_remote_get( $url, [
				'timeout' => 15
			]);
			
			if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
				if ( $page === 1 ) {
					$stale_cache = get_option( '_transient_jikan_anime_eps_' . $mal_id );
					return $stale_cache ? $stale_cache : [];
				} else {
					break; // Falhou no meio da paginaÃ§Ã£o, mantÃ©m o que jÃ¡ baixou
				}
			}
			
			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );
			
			$page_eps = $data['data'] ?? [];
			if ( ! empty( $page_eps ) ) {
				$episodes = array_merge( $episodes, $page_eps );
			}
			
			$pagination = $data['pagination'] ?? [];
			$has_next = ! empty( $pagination['has_next_page'] );
			$page++;
		}
		
		// Cache de 12 horas, para animes em lanÃ§amento atualizarem no mesmo dia
		set_transient( 'jikan_anime_eps_' . $mal_id, $episodes, 12 * HOUR_IN_SECONDS );
		
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
	 * Busca os personagens de um mangÃ¡
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
		
		// Cache de 30 dias para pessoas, pois seus dados bÃ¡sicos e fotos raramente mudam
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
		
		$lang = get_query_var('app_lang');
		if ($lang === 'en') return $genre_name;
		
		$pt_br = $map[ $genre_name ] ?? $genre_name;
		if (empty($lang) || $lang === 'pt-BR') return $pt_br;
		
		// For other languages, we map manually here to avoid modifying the extracted gettext file
		$lang_map = [
			'Ação' => ['es' => 'Acción', 'fr' => 'Action', 'de' => 'Aktion'],
			'Aventura' => ['es' => 'Aventura', 'fr' => 'Aventure', 'de' => 'Abenteuer'],
			'Comédia' => ['es' => 'Comedia', 'fr' => 'Comédie', 'de' => 'Komödie'],
			'Fantasia' => ['es' => 'Fantasía', 'fr' => 'Fantaisie', 'de' => 'Fantasie'],
			'Terror' => ['es' => 'Terror', 'fr' => 'Horreur', 'de' => 'Horror'],
			'Mistério' => ['es' => 'Misterio', 'fr' => 'Mystère', 'de' => 'Geheimnis'],
			'Romance' => ['es' => 'Romance', 'fr' => 'Romance', 'de' => 'Romantik'],
			'Ficção Científica' => ['es' => 'Ciencia Ficción', 'fr' => 'Science-Fiction', 'de' => 'Science-Fiction'],
			'Cotidiano' => ['es' => 'Recuentos de la Vida', 'fr' => 'Tranche de Vie', 'de' => 'Alltagsleben'],
			'Esportes' => ['es' => 'Deportes', 'fr' => 'Sports', 'de' => 'Sport'],
			'Sobrenatural' => ['es' => 'Sobrenatural', 'fr' => 'Surnaturel', 'de' => 'Übernatürlich'],
			'Suspense' => ['es' => 'Suspenso', 'fr' => 'Suspense', 'de' => 'Spannung'],
		];
		
		return $lang_map[$pt_br][$lang] ?? $pt_br;
	}

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
		
		$lang = get_query_var('app_lang');
		if ($lang === 'en') return $status;
		
		$pt_br = $map[ $status ] ?? $status;
		if (empty($lang) || $lang === 'pt-BR') return $pt_br;
		
		$lang_map = [
			'Em Exibição' => ['es' => 'En Emisión', 'fr' => 'En Cours de Diffusion', 'de' => 'Wird ausgestrahlt'],
			'Finalizado' => ['es' => 'Finalizado', 'fr' => 'Terminé', 'de' => 'Abgeschlossen'],
			'Em Breve' => ['es' => 'Próximamente', 'fr' => 'Bientôt', 'de' => 'Demnächst'],
			'Em Publicação' => ['es' => 'En Publicación', 'fr' => 'En Publication', 'de' => 'In Veröffentlichung'],
			'Em Hiato' => ['es' => 'En Pausa', 'fr' => 'En Pause', 'de' => 'Pausiert'],
			'Descontinuado' => ['es' => 'Descontinuado', 'fr' => 'Arrêté', 'de' => 'Eingestellt'],
		];
		
		return $lang_map[$pt_br][$lang] ?? $pt_br;
	}

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
		
		$lang = get_query_var('app_lang');
		if ($lang === 'en') return $relation;
		
		$pt_br = $map[ $relation ] ?? $relation;
		if (empty($lang) || $lang === 'pt-BR') return $pt_br;
		
		$lang_map = [
			'Sequência' => ['es' => 'Secuela', 'fr' => 'Suite', 'de' => 'Fortsetzung'],
			'Temporada Anterior' => ['es' => 'Precuela', 'fr' => 'Préquelle', 'de' => 'Prequel'],
			'Universo Alternativo' => ['es' => 'Universo Alternativo', 'fr' => 'Univers Alternatif', 'de' => 'Alternatives Universum'],
			'Versão Alternativa' => ['es' => 'Versión Alternativa', 'fr' => 'Version Alternative', 'de' => 'Alternative Version'],
			'História Paralela' => ['es' => 'Historia Paralela', 'fr' => 'Histoire Parallèle', 'de' => 'Nebengeschichte'],
			'História Principal' => ['es' => 'Historia Principale', 'fr' => 'Histoire Principale', 'de' => 'Hauptgeschichte'],
			'Resumo / Recap' => ['es' => 'Resumen', 'fr' => 'Résumé', 'de' => 'Zusammenfassung'],
			'Personagem' => ['es' => 'Personaje', 'fr' => 'Personnage', 'de' => 'Charakter'],
			'Spin-off' => ['es' => 'Spin-off', 'fr' => 'Spin-off', 'de' => 'Spin-off'],
			'Adaptação' => ['es' => 'Adaptación', 'fr' => 'Adaptation', 'de' => 'Adaption'],
		];
		
		return $lang_map[$pt_br][$lang] ?? $pt_br;
	}

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

// â”€â”€// Registra os listeners do WP-Cron para atualizar o cache assincronamente (Shadow Cache / SWR)
add_action( 'mm_async_jikan_update_full', array( 'Jikan_API', 'fetch_and_cache_anime' ) );
add_action( 'mm_async_jikan_update_eps', array( 'Jikan_API', 'fetch_and_cache_episodes' ) );
add_action( 'mm_async_jikan_update_chars', array( 'Jikan_API', 'fetch_and_cache_characters' ) );
add_action( 'mm_async_jikan_update_char_full', array( 'Jikan_API', 'fetch_and_cache_character_full' ) );
add_action( 'mm_async_jikan_update_person', array( 'Jikan_API', 'fetch_and_cache_person' ) );
add_action( 'mm_async_jikan_update_manga_full', array( 'Jikan_API', 'fetch_and_cache_manga' ) );
add_action( 'mm_async_jikan_update_manga_chars', array( 'Jikan_API', 'fetch_and_cache_manga_characters' ) );
add_action( 'mm_async_jikan_update_manga_recs', array( 'Jikan_API', 'fetch_and_cache_manga_recommendations' ) );


/**
 * ---------------------------------------------------------------------
 * TRADUÇÃO REAL-TIME (Llama 3 via Groq) NO MOMENTO DA IMPORTAÇÃO
 * ---------------------------------------------------------------------
 * Acionado apenas quando um post é criado/salvo no painel.
 */
function vibe_translate_on_import($post_id, $post, $update) {
    // Evita loop infinito e revisões
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }

    // Apenas nossos CPTs
    if (!in_array($post->post_type, array('anime', 'manga', 'dublador'))) {
        return;
    }

    // Pega o ID MAL
    $mal_id = get_post_meta($post_id, $post->post_type . '_id_mal', true);
    if (!$mal_id) {
        return;
    }

    // Verifica se já foi traduzido para não gastar API a cada 'Update'
    $check_en = get_post_meta($post_id, $post->post_type . '_sinopse_en', true);
    if ($post->post_type === 'dublador') {
        $check_en = get_post_meta($post_id, 'dublador_biografia_en', true);
    }
    
    if (!empty($check_en)) {
        return; // Já foi traduzido!
    }

    // 1. Busca os dados na Jikan API
    $original_text = '';
    $field_base = $post->post_type . '_sinopse';

    if ($post->post_type === 'anime') {
        $jikan = Jikan_API::get_anime_full($mal_id);
        $original_text = $jikan['synopsis'] ?? '';
    } elseif ($post->post_type === 'manga') {
        $jikan = Jikan_API::get_manga_full($mal_id);
        $original_text = $jikan['synopsis'] ?? '';
    } elseif ($post->post_type === 'dublador') {
        $jikan = Jikan_API::get_person_full($mal_id);
        $original_text = $jikan['about'] ?? '';
        $field_base = 'dublador_biografia';
    }

    if (empty(trim($original_text))) {
        return;
    }

    // 2. Dispara requisições para a Groq API (Tradutor Llama 3)
    // Usamos a chave diretamente do .env local
    $env_file = get_template_directory() . '/../../../../Geek ao Cubo v2.2.2/Pipeline Traducao/.env';
    $groq_key = '';
    if (file_exists($env_file)) {
        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, 'GROQ_KEY_1=') === 0) {
                $groq_key = str_replace('GROQ_KEY_1=', '', $line);
                break;
            }
        }
    }

    if (empty($groq_key)) {
        return; // Sem chave, sem tradução
    }

    $languages = array('en' => 'English', 'es' => 'Spanish', 'fr' => 'French', 'de' => 'German');

    foreach ($languages as $code => $lang_name) {
        $prompt = "Translate the following synopsis to " . $lang_name . ". Return ONLY the translated text, no pleasantries, no conversational fillers. Text: " . $original_text;

        $response = wp_remote_post("https://api.groq.com/openai/v1/chat/completions", array(
            'headers' => array(
                'Authorization' => 'Bearer ' . trim($groq_key),
                'Content-Type'  => 'application/json',
            ),
            'body' => wp_json_encode(array(
                'model' => 'llama-3.3-70b-versatile',
                'messages' => array(
                    array('role' => 'system', 'content' => 'You are a professional translator for an anime database.'),
                    array('role' => 'user', 'content' => $prompt)
                ),
                'temperature' => 0.3
            )),
            'timeout' => 15
        ));

        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $translated = $body['choices'][0]['message']['content'] ?? '';
            
            if (!empty($translated)) {
                // Salva no ACF silenciosamente
                update_post_meta($post_id, $field_base . '_' . $code, trim($translated));
            }
        }
        
        // Throttling mínimo
        usleep(300000); 
    }
}
add_action('save_post', 'vibe_translate_on_import', 99, 3);

