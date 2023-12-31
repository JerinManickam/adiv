<?php
/**
 * User password reset email
 *
 * This template can be overridden by copying it to yourtheme/masteriyo/emails/customer-reset-password.php.
 *
 * HOWEVER, on occasion Masteriyo will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @package Masteriyo\Templates\Emails
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.


do_action( 'masteriyo_email_header', $email_heading, $email );

?>

<p class="email-template--info">
	<?php /* translators: %s: Username */ ?>
	<?php printf( esc_html__( 'Hi %s,', 'masteriyo' ), esc_html( $user->get_username() ) ); ?>
</p>

<p class="email-template--info">
	<?php /* translators: %s: Site name */ ?>
	<?php printf( esc_html__( 'Someone has requested a new password for the following account on %s:', 'masteriyo' ), esc_html( $blogname ) ); ?>
</p>

<p class="email-template--info email-text--bold">
	<?php /* translators: %s: Username */ ?>
	<?php printf( esc_html__( 'Username: %s', 'masteriyo' ), esc_html( $user->get_username() ) ); ?>
</p>

<p class="email-template--info">
	<?php esc_html_e( 'If you didn\'t make this request, just ignore this email. If you\'d like to proceed:', 'masteriyo' ); ?>
</p>

<a
	class="email-template--button"
	href="<?php echo esc_url( masteriyo_get_password_reset_link( $reset_key, $user->get_id() ) ); ?>"
>
	<?php esc_html_e( 'Click here to reset your password.', 'masteriyo' ); ?>
</a>

<?php

/**
 * Show user-defined additional content.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

do_action( 'masteriyo_email_footer', $email );
