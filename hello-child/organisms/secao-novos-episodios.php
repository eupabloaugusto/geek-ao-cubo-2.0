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
 * @package hello-elementor-child
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

// 2. Conversão UTC → BRT e ordenação crescente por horário
$tz_brt = new DateTimeZone( 'America/Sao_Paulo' );
$tz_utc = new DateTimeZone( 'UTC' );

foreach ( $animes as &$anime ) {
	if ( ! empty( $anime['horario'] ) ) {
		$dt = new DateTime( '2000-01-01 ' . $anime['horario'], $tz_utc );
		$dt->setTimezone( $tz_brt );
		$anime['horario'] = $dt->format( 'H:i' );
	}
}
unset( $anime );

usort( $animes, function( $a, $b ) {
	return strcmp(
		isset( $a['horario'] ) ? $a['horario'] : '99:99',
		isset( $b['horario'] ) ? $b['horario'] : '99:99'
	);
} );

// 3. Geração dinâmica do título com o dia da semana em BRT
$agora_brt   = new DateTime( 'now', $tz_brt );
$dias_semana = array(
	0 => __( 'Domingo', 'hello-elementor-child' ),
	1 => __( 'Segunda-feira', 'hello-elementor-child' ),
	2 => __( 'Terça-feira', 'hello-elementor-child' ),
	3 => __( 'Quarta-feira', 'hello-elementor-child' ),
	4 => __( 'Quinta-feira', 'hello-elementor-child' ),
	5 => __( 'Sexta-feira', 'hello-elementor-child' ),
	6 => __( 'Sábado', 'hello-elementor-child' ),
);
$dia_atual    = $dias_semana[ (int) $agora_brt->format( 'w' ) ];
$titulo_secao = sprintf( __( 'Novos Episódios — %s', 'hello-elementor-child' ), $dia_atual );

?>

<section class="secao-novos-episodios" aria-label="<?php echo esc_attr( $titulo_secao ); ?>">

	<!-- 1. Cabeçalho com título dinâmico -->
	<header class="secao-novos-episodios__header">
		<h2 class="secao-novos-episodios__title">
			<?php echo esc_html( $titulo_secao ); ?>
		</h2>
	</header>

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
