<?php
/**
 * Masteriyo checkout form payment.
 *
 * @package Masteriyo\Templates;
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<?php do_action( 'masteriyo_checkout_before_payment_methods' ); ?>

<?php if ( masteriyo( 'cart' )->needs_payment() ) : ?>
	<div id="masteriyo-payments" class="masteriyo-checkout-summary-payment-method">
		<ul class="masteriyo-payment-methods payment-methods methods masteriyo-checkout-payment-method">
			<?php
			if ( ! empty( $available_gateways ) ) {
				foreach ( $available_gateways as $gateway ) {
					masteriyo_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
				}
			} else {
				$message = esc_html__( 'Please enable one or more payment gateways.', 'masteriyo' );

				if ( masteriyo_get_current_user()->get_billing_country() ) {
					$message = esc_html__( 'Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'masteriyo' );
				}

				echo wp_kses_post(
					sprintf(
						'<li class="masteriyo-notice masteriyo-alert masteriyo-info-msg">%s</li>',
						$message
					)
				);
			}
			?>
		</ul>
	</div>
<?php endif; ?>

<?php do_action( 'masteriyo_checkout_after_payment_methods' ); ?>

<?php do_action( 'masteriyo_checkout_summary_before_submit' ); ?>

<button
	type="submit"
	class="masteriyo-checkout--btn masteriyo-button masteriyo-btn-primary alt"
	id="masteriyo-place-order"
	name="masteriyo_checkout_place_order">
	<?php echo esc_html( $order_button_text ); ?>
</button>

<?php
	wp_nonce_field( 'masteriyo-process_checkout', 'masteriyo-process-checkout-nonce' );
	do_action( 'masteriyo_checkout_summary_after_submit' );

