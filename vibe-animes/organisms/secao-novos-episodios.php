<?php
/**
 * Organism: Seção de Novos Episódios (secao-novos-episodios)
 *
 * Carrossel horizontal de animes com novos episódios do dia.
 * Usa a molécula trilho-infinito (setas + scroll infinito) e card-anime
 * com o badge de horário visível em cada capa.
 *
 * O título é gerado dinamicamente pelo PHP com o dia da semana atual.
 *
 * @package geek-ao-cubo
 *
 * @param array  $args['animes'] Lista de animes (titulo, url, imagem_url, nota, horario, generos).
 *                              O campo `horario` deve ser fornecido em UTC (ex: "21:00").
 *                              O componente converte automaticamente para BRT (America/Sao_Paulo)
 *                              e ordena os cards de forma crescente pelo horário convertido.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Higienização e Validação dos Argumentos
$animes = isset( $args['animes'] ) ? $args['animes'] : array();

// Impede a renderização se a lista de animes estiver vazia
if ( empty( $animes ) || ! is_array( $animes ) ) {
	return;
}

// 2. Ordenação crescente por horário (usando a string UTC)
usort( $animes, function( $a, $b ) {
	$val_a = ! empty( $a['horario_utc'] ) ? $a['horario_utc'] : ( isset( $a['horario'] ) ? $a['horario'] : '99:99' );
	$val_b = ! empty( $b['horario_utc'] ) ? $b['horario_utc'] : ( isset( $b['horario'] ) ? $b['horario'] : '99:99' );
	return strcmp( $val_a, $val_b );
} );

// 3. Geração dinâmica do título com o dia da semana em BRT (apenas para o título da seção)
$tz_brt      = new DateTimeZone( 'America/Sao_Paulo' );
$agora_brt   = new DateTime( 'now', $tz_brt );
$dias_semana = array(
	0 => __( 'Domingo', 'geek-ao-cubo' ),
	1 => __( 'Segunda-feira', 'geek-ao-cubo' ),
	2 => __( 'Terça-feira', 'geek-ao-cubo' ),
	3 => __( 'Quarta-feira', 'geek-ao-cubo' ),
	4 => __( 'Quinta-feira', 'geek-ao-cubo' ),
	5 => __( 'Sexta-feira', 'geek-ao-cubo' ),
	6 => __( 'Sábado', 'geek-ao-cubo' ),
);
$dia_atual    = $dias_semana[ (int) $agora_brt->format( 'w' ) ];
$titulo_secao = sprintf( __( 'Novos Episódios — %s', 'geek-ao-cubo' ), $dia_atual );

?>

<section class="secao-novos-episodios" aria-label="<?php echo esc_attr( $titulo_secao ); ?>">

	<!-- 1. Cabeçalho com título dinâmico -->
	<?php mm_render_component( 'organisms', 'secao-titulo', array(
		'titulo'    => $titulo_secao,
		'sub_badge' => __( 'HOJE', 'geek-ao-cubo' ),
	) ); ?>

	<!-- 2. Trilho com setas e scroll infinito (molécula trilho-infinito) -->
	<?php
	ob_start();
	foreach ( $animes as $anime_args ) :
		echo '<div class="secao-novos-episodios__slide js-trilho__slide">';
		mm_render_component( 'molecules', 'card-anime', $anime_args );
		echo '</div>';
	endforeach;
	$track_html = ob_get_clean();

	mm_render_component( 'molecules', 'trilho-infinito', array(
		'track_html'  => $track_html,
		'class'       => 'secao-novos-episodios__wrapper',
		'track_class' => 'secao-novos-episodios__track',
	) );
	?>

</section>
