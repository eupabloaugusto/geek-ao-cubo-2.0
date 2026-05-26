<?php
/**
 * Molecule: Card de Review (review-card)
 *
 * Card de avaliação de usuário para a seção de reviews da página de detalhe
 * do anime. Exibe avatar, nome, data, nota MAL e texto da review com
 * expand/collapse in-page via JavaScript.
 *
 * @package hello-elementor-child
 *
 * @param string $reviewer_name   Nome do revisor (obrigatório).
 * @param string $reviewer_avatar URL do avatar do revisor (opcional — fallback silhueta).
 * @param string $reviewer_url    URL do perfil do revisor (opcional).
 * @param string $review_date     Data da review ex: "25 mai. 2026" (opcional).
 * @param string $review_score    Nota ex: "8.5" — alimenta nota-mal (opcional).
 * @param string $review_text     Texto completo da review em HTML simples (obrigatório).
 * @param string $review_url      Link para a review completa (opcional).
 * @param int    $max_chars       Limite de chars antes do corte. Padrão: 300.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$reviewer_name   = isset( $args['reviewer_name'] )   ? esc_html( $args['reviewer_name'] )   : '';
$reviewer_avatar = isset( $args['reviewer_avatar'] ) ? esc_url( $args['reviewer_avatar'] )  : '';
$reviewer_url    = isset( $args['reviewer_url'] )    ? esc_url( $args['reviewer_url'] )     : '';
$review_date     = isset( $args['review_date'] )     ? esc_html( $args['review_date'] )     : '';
$review_score    = isset( $args['review_score'] )    ? $args['review_score']                : '';
$review_text     = isset( $args['review_text'] )     ? $args['review_text']                 : '';
$review_url      = isset( $args['review_url'] )      ? esc_url( $args['review_url'] )       : '';
$max_chars       = isset( $args['max_chars'] )       ? (int) $args['max_chars']             : 300;

if ( empty( $reviewer_name ) || empty( $review_text ) ) {
	return;
}

$review_text_clean = wp_kses( $review_text, array(
	'p'      => array(),
	'br'     => array(),
	'strong' => array(),
	'em'     => array(),
) );

$is_expandable = ( mb_strlen( wp_strip_all_tags( $review_text_clean ) ) > $max_chars );

static $review_card_count = 0;
$review_card_count++;
$card_id = 'review-card-' . $review_card_count;
$body_id = 'review-card-body-' . $review_card_count;
?>

<article
	class="review-card<?php echo $is_expandable ? ' review-card--expandable' : ''; ?>"
	id="<?php echo esc_attr( $card_id ); ?>"
	data-expandable="<?php echo $is_expandable ? 'true' : 'false'; ?>"
	itemscope
	itemtype="https://schema.org/Review"
>

	<!-- =====================================================
	     A. CABEÇALHO: avatar + nome/data + nota
	     ===================================================== -->
	<header class="review-card__header">

		<!-- A1. Avatar + identidade -->
		<div class="review-card__identity">
			<?php
			mm_render_component( 'atoms', 'avatar-personagem', array(
				'image_url'      => $reviewer_avatar,
				'character_name' => $reviewer_name,
				'size'           => 40,
				'class'          => 'review-card__avatar',
			) );
			?>

			<div class="review-card__meta">
				<?php if ( ! empty( $reviewer_url ) ) : ?>
					<a
						href="<?php echo $reviewer_url; ?>"
						class="review-card__author"
						rel="nofollow"
						aria-label="<?php echo esc_attr( sprintf( __( 'Ver perfil de %s', 'hello-elementor-child' ), $reviewer_name ) ); ?>"
						itemprop="author"
						itemscope
						itemtype="https://schema.org/Person"
					>
						<span itemprop="name"><?php echo $reviewer_name; ?></span>
					</a>
				<?php else : ?>
					<span
						class="review-card__author"
						itemprop="author"
						itemscope
						itemtype="https://schema.org/Person"
					>
						<span itemprop="name"><?php echo $reviewer_name; ?></span>
					</span>
				<?php endif; ?>

				<?php if ( ! empty( $review_date ) ) : ?>
					<time class="review-card__date" itemprop="datePublished"><?php echo $review_date; ?></time>
				<?php endif; ?>
			</div>
		</div>

		<!-- A2. Nota MAL -->
		<?php if ( ! empty( $review_score ) ) : ?>
			<div
				class="review-card__score"
				itemprop="reviewRating"
				itemscope
				itemtype="https://schema.org/Rating"
			>
				<meta itemprop="ratingValue" content="<?php echo esc_attr( $review_score ); ?>">
				<meta itemprop="bestRating"  content="10">
				<?php
				mm_render_component( 'atoms', 'nota-mal', array(
					'nota'  => $review_score,
					'class' => 'review-card__nota',
				) );
				?>
			</div>
		<?php endif; ?>

	</header>

	<!-- =====================================================
	     B. CORPO: texto da review com expand/collapse
	     ===================================================== -->
	<div
		class="review-card__body"
		id="<?php echo esc_attr( $body_id ); ?>"
		itemprop="reviewBody"
	>
		<div class="review-card__text">
			<?php echo $review_text_clean; ?>
		</div>

		<?php if ( $is_expandable ) : ?>
			<div class="review-card__fade" aria-hidden="true"></div>
		<?php endif; ?>
	</div>

	<!-- =====================================================
	     C. RODAPÉ: toggle expand/collapse + link externo
	     ===================================================== -->
	<?php if ( $is_expandable || ! empty( $review_url ) ) : ?>
		<footer class="review-card__footer">

			<?php if ( $is_expandable ) : ?>
				<button
					class="review-card__toggle"
					type="button"
					aria-expanded="false"
					aria-controls="<?php echo esc_attr( $body_id ); ?>"
					data-label-more="<?php echo esc_attr( __( 'Ler mais', 'hello-elementor-child' ) ); ?>"
					data-label-less="<?php echo esc_attr( __( 'Ler menos', 'hello-elementor-child' ) ); ?>"
				>
					<span class="review-card__toggle-label"><?php _e( 'Ler mais', 'hello-elementor-child' ); ?></span>
					<svg class="review-card__toggle-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
						<path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
			<?php endif; ?>

			<?php if ( ! empty( $review_url ) ) : ?>
				<a
					href="<?php echo $review_url; ?>"
					class="review-card__link"
					rel="nofollow noopener"
					target="_blank"
					aria-label="<?php echo esc_attr( sprintf( __( 'Ver review completa de %s', 'hello-elementor-child' ), $reviewer_name ) ); ?>"
				>
					<?php _e( 'Ver review completa', 'hello-elementor-child' ); ?>
					<svg class="review-card__link-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
						<path d="M3 8H13M9 4L13 8L9 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</a>
			<?php endif; ?>

		</footer>
	<?php endif; ?>

</article>
