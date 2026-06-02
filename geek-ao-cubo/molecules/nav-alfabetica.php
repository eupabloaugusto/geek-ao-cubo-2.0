<?php
/**
 * Molecule: Navegação Alfabética (nav-alfabetica)
 *
 * Barra de navegação com # e todas as letras do alfabeto (A–Z).
 * Cada letra é um link GET (?letra=M) para filtrar o catálogo por letra inicial.
 * Letras sem animes cadastrados ficam desabilitadas visualmente.
 * Scroll horizontal com snap no mobile.
 *
 * @package geek-ao-cubo
 *
 * @param string $letra_atual   Letra atualmente selecionada (ex: 'M'). Vazio = nenhuma.
 * @param array  $letras_ativas Array de letras que possuem animes (ex: ['A','B','N']). Vazio = todas ativas.
 * @param string $base_url      URL base do catálogo. Default: archive do CPT 'anime'.
 * @param string $class         Classes CSS adicionais.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$letra_atual   = isset( $args['letra_atual'] )   ? strtoupper( sanitize_text_field( $args['letra_atual'] ) ) : '';
$letras_ativas = isset( $args['letras_ativas'] ) ? array_map( 'strtoupper', (array) $args['letras_ativas'] ) : array();
$base_url      = isset( $args['base_url'] )      ? esc_url( $args['base_url'] )                              : esc_url( get_post_type_archive_link( 'anime' ) ?: home_url( '/' ) );
$class         = isset( $args['class'] )         ? esc_attr( $args['class'] )                                : '';

// URL base sem filtro de letra (preserva demais query params como busca/gênero)
$base_url_sem_letra = remove_query_arg( 'letra', $base_url );

// Conjunto completo: # + A–Z
$todas_letras = array_merge( array( '#' ), range( 'A', 'Z' ) );

// Se nenhuma letra_ativa for passada, considera todas como ativas
$todas_ativas = empty( $letras_ativas );
?>
<nav
	class="nav-alfabetica <?php echo $class; ?>"
	aria-label="<?php esc_attr_e( 'Navegar por letra inicial', 'geek-ao-cubo' ); ?>"
>
	<ul class="nav-alfabetica__lista" role="list">
		<!-- Opção "Todos" (limpa o filtro de letra) -->
		<li class="nav-alfabetica__item">
			<a
				href="<?php echo esc_url( $base_url_sem_letra ); ?>"
				class="nav-alfabetica__link<?php echo ( '' === $letra_atual ) ? ' nav-alfabetica__link--ativo' : ''; ?>"
				aria-label="<?php esc_attr_e( 'Ver todos os animes', 'geek-ao-cubo' ); ?>"
				<?php echo ( '' === $letra_atual ) ? 'aria-current="page"' : ''; ?>
			>Todos</a>
		</li>

		<?php foreach ( $todas_letras as $letra ) :
			$is_ativa  = $todas_ativas || in_array( $letra, $letras_ativas, true );
			$is_atual  = ( $letra === $letra_atual );
			$link_url  = esc_url( add_query_arg( 'letra', rawurlencode( $letra ), $base_url_sem_letra ) );

			$item_class = 'nav-alfabetica__link';
			if ( $is_atual ) {
				$item_class .= ' nav-alfabetica__link--ativo';
			}
			if ( ! $is_ativa ) {
				$item_class .= ' nav-alfabetica__link--inativo';
			}
		?>
			<li class="nav-alfabetica__item">
				<?php if ( $is_ativa && ! $is_atual ) : ?>
					<a
						href="<?php echo $link_url; ?>"
						class="<?php echo $item_class; ?>"
						aria-label="<?php echo esc_attr( sprintf( __( 'Animes com letra %s', 'geek-ao-cubo' ), $letra ) ); ?>"
					><?php echo esc_html( $letra ); ?></a>
				<?php else : ?>
					<span
						class="<?php echo $item_class; ?>"
						<?php echo $is_atual ? 'aria-current="page"' : 'aria-disabled="true"'; ?>
					><?php echo esc_html( $letra ); ?></span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
