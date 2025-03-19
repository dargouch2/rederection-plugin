<?php

if (!defined('ABSPATH')) {
    exit;
}

$gateway = new WC_codarab_rest_api_Gateway();

$order_id = WC()->checkout()->create_order([
    'payment_method' => 'codarab_rest_api'
]);

$order = wc_get_order($order_id);

$checkout_url = $gateway->generate_checkout_url($order);

?>

<?php wp_head(); ?>

<iframe src="<?php echo esc_url($checkout_url); ?>" width="100%" style="height: 100vh;" frameborder="0" scrolling="yes"></iframe>

<?php wp_footer(); ?>
