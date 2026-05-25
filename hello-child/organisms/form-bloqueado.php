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
 * @package hello-elementor-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title      = isset( $args['title'] ) ? esc_html( $args['title'] ) : 'Configurações de Perfil';
$user_email = isset( $args['user_email'] ) ? esc_attr( $args['user_email'] ) : '';
$username   = isset( $args['username'] ) ? esc_attr( $args['username'] ) : '';
?>

<section class="form-bloqueado">

	<header class="form-bloqueado__header">
		<h2 class="form-bloqueado__title"><?php echo $title; ?></h2>
		<p class="form-bloqueado__subtitle">Os campos marcados com <span class="form-bloqueado__lock-icon" aria-label="cadeado">🔒</span> não podem ser editados.</p>
	</header>

	<form class="form-bloqueado__form" action="#" method="post" novalidate>

		<!-- Linha 1: campos bloqueados pelo sistema -->
		<div class="form-bloqueado__row form-bloqueado__row--locked">

			<?php mm_render_component( 'molecules', 'form-field', array(
				'label'       => '🔒 E-mail',
				'name'        => 'user_email',
				'id'          => 'fb-email',
				'type'        => 'email',
				'value'       => $user_email,
				'placeholder' => 'seu@email.com',
				'disabled'    => true,
				'helper_text' => 'Gerenciado pelo sistema. Entre em contato com o suporte para alterar.',
			) ); ?>

			<?php mm_render_component( 'molecules', 'form-field', array(
				'label'       => '🔒 Nome de Usuário',
				'name'        => 'username',
				'id'          => 'fb-username',
				'type'        => 'text',
				'value'       => $username,
				'placeholder' => '@usuario',
				'disabled'    => true,
				'helper_text' => 'Definido na criação da conta. Não pode ser alterado.',
			) ); ?>

		</div>

		<!-- Linha 2: campos editáveis normalmente -->
		<div class="form-bloqueado__row">

			<?php mm_render_component( 'molecules', 'form-field', array(
				'label'       => 'Nome de Exibição',
				'name'        => 'display_name',
				'id'          => 'fb-display-name',
				'type'        => 'text',
				'placeholder' => 'Como quer ser chamado?',
				'helper_text' => 'Este nome será visível publicamente nos comentários.',
			) ); ?>

			<?php mm_render_component( 'molecules', 'form-field', array(
				'label'       => 'Gênero Favorito',
				'name'        => 'fav_genre',
				'id'          => 'fb-fav-genre',
				'type'        => 'select',
				'placeholder' => 'Selecione um gênero...',
				'options'     => array(
					'action'  => 'Ação',
					'romance' => 'Romance',
					'isekai'  => 'Isekai',
					'shonen'  => 'Shōnen',
					'seinen'  => 'Seinen',
				),
			) ); ?>

		</div>

		<!-- Linha 3: campo de texto longo + select bloqueado -->
		<div class="form-bloqueado__row">

			<?php mm_render_component( 'molecules', 'form-field', array(
				'label'       => 'Bio / Apresentação',
				'name'        => 'bio',
				'id'          => 'fb-bio',
				'type'        => 'text',
				'placeholder' => 'Fale um pouco sobre você...',
			) ); ?>

			<?php mm_render_component( 'molecules', 'form-field', array(
				'label'       => '🔒 Plano Atual',
				'name'        => 'plan',
				'id'          => 'fb-plan',
				'type'        => 'select',
				'placeholder' => 'Gratuito',
				'disabled'    => true,
				'options'     => array(
					'free' => 'Gratuito',
					'pro'  => 'Pro',
				),
				'helper_text' => 'Altere seu plano na página de assinatura.',
			) ); ?>

		</div>

		<!-- Rodapé: ações -->
		<div class="form-bloqueado__footer">
			<?php mm_render_component( 'atoms', 'btn-primary', array( 'label' => 'Salvar Alterações', 'type' => 'submit' ) ); ?>
			<?php mm_render_component( 'atoms', 'btn-secondary', array( 'label' => 'Cancelar', 'type' => 'button' ) ); ?>
		</div>

	</form>

</section>
