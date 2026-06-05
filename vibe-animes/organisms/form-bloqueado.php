<?php
/**
 * Organism: Formulário com Campos Desabilitados (form-bloqueado)
 *
 * Exibe um formulário misto com campos editáveis e bloqueados.
 * Caso de uso típico: página de perfil de usuário onde e-mail
 * e apelido são definidos pelo sistema e não podem ser alterados.
 *
 * Props disponíveis via $args:
 *  - title       (string) Título do painel.                     Default: 'Configurações de Perfil'
 *  - user_email  (string) E-mail do usuário (exibido, bloqueado).
 *  - username    (string) Nome de usuário (exibido, bloqueado).
 *
 * @package geek-ao-cubo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title      = isset( $args['title'] ) ? esc_html( $args['title'] ) : __( 'Configurações de Perfil', 'geek-ao-cubo' );
$user_email = isset( $args['user_email'] ) ? esc_attr( $args['user_email'] ) : '';
$username   = isset( $args['username'] ) ? esc_attr( $args['username'] ) : '';
?>

<section class="form-bloqueado">

	<header class="form-bloqueado__header">
		<h2 class="form-bloqueado__title"><?php echo $title; ?></h2>
		<p class="form-bloqueado__subtitle"><?php printf( __( 'Os campos marcados com %s não podem ser editados.', 'geek-ao-cubo' ), '<span class="form-bloqueado__lock-icon" aria-label="' . esc_attr__( 'cadeado', 'geek-ao-cubo' ) . '">🔒</span>' ); ?></p>
	</header>

	<form class="form-bloqueado__form" action="#" method="post" novalidate>

		<!-- Linha 1: campos bloqueados pelo sistema -->
		<div class="form-bloqueado__row form-bloqueado__row--locked">

			<?php mm_render_component( 'molecules', 'form-field', array(
				'label'       => '🔒 ' . __( 'E-mail', 'geek-ao-cubo' ),
				'name'        => 'user_email',
				'id'          => 'fb-email',
				'type'        => 'email',
				'value'       => $user_email,
				'placeholder' => __( 'seu@email.com', 'geek-ao-cubo' ),
				'disabled'    => true,
				'helper_text' => __( 'Gerenciado pelo sistema. Entre em contato com o suporte para alterar.', 'geek-ao-cubo' ),
			) ); ?>

			<?php mm_render_component( 'molecules', 'form-field', array(
				'label'       => '🔒 ' . __( 'Nome de Usuário', 'geek-ao-cubo' ),
				'name'        => 'username',
				'id'          => 'fb-username',
				'type'        => 'text',
				'value'       => $username,
				'placeholder' => __( '@usuario', 'geek-ao-cubo' ),
				'disabled'    => true,
				'helper_text' => __( 'Definido na criação da conta. Não pode ser alterado.', 'geek-ao-cubo' ),
			) ); ?>

		</div>

		<!-- Linha 2: campos editáveis normalmente -->
		<div class="form-bloqueado__row">

			<?php mm_render_component( 'molecules', 'form-field', array(
				'label'       => __( 'Nome de Exibição', 'geek-ao-cubo' ),
				'name'        => 'display_name',
				'id'          => 'fb-display-name',
				'type'        => 'text',
				'placeholder' => __( 'Como quer ser chamado?', 'geek-ao-cubo' ),
				'helper_text' => __( 'Este nome será visível publicamente nos comentários.', 'geek-ao-cubo' ),
			) ); ?>

			<?php mm_render_component( 'molecules', 'form-field', array(
				'label'       => __( 'Gênero Favorito', 'geek-ao-cubo' ),
				'name'        => 'fav_genre',
				'id'          => 'fb-fav-genre',
				'type'        => 'select',
				'placeholder' => __( 'Selecione um gênero...', 'geek-ao-cubo' ),
				'options'     => array(
					'action'  => __( 'Ação', 'geek-ao-cubo' ),
					'romance' => __( 'Romance', 'geek-ao-cubo' ),
					'isekai'  => __( 'Isekai', 'geek-ao-cubo' ),
					'shonen'  => __( 'Shōnen', 'geek-ao-cubo' ),
					'seinen'  => __( 'Seinen', 'geek-ao-cubo' ),
				),
			) ); ?>

		</div>

		<!-- Linha 3: campo de texto longo + select bloqueado -->
		<div class="form-bloqueado__row">

			<?php mm_render_component( 'molecules', 'form-field', array(
				'label'       => __( 'Bio / Apresentação', 'geek-ao-cubo' ),
				'name'        => 'bio',
				'id'          => 'fb-bio',
				'type'        => 'text',
				'placeholder' => __( 'Fale um pouco sobre você...', 'geek-ao-cubo' ),
			) ); ?>

			<?php mm_render_component( 'molecules', 'form-field', array(
				'label'       => '🔒 ' . __( 'Plano Atual', 'geek-ao-cubo' ),
				'name'        => 'plan',
				'id'          => 'fb-plan',
				'type'        => 'select',
				'placeholder' => __( 'Gratuito', 'geek-ao-cubo' ),
				'disabled'    => true,
				'options'     => array(
					'free' => __( 'Gratuito', 'geek-ao-cubo' ),
					'pro'  => __( 'Pro', 'geek-ao-cubo' ),
				),
				'helper_text' => __( 'Altere seu plano na página de assinatura.', 'geek-ao-cubo' ),
			) ); ?>

		</div>

		<!-- Rodapé: ações -->
		<div class="form-bloqueado__footer">
			<?php mm_render_component( 'atoms', 'btn-primary', array( 'label' => __( 'Salvar Alterações', 'geek-ao-cubo' ), 'type' => 'submit' ) ); ?>
			<?php mm_render_component( 'atoms', 'btn-secondary', array( 'label' => __( 'Cancelar', 'geek-ao-cubo' ), 'type' => 'button' ) ); ?>
		</div>

	</form>

</section>
