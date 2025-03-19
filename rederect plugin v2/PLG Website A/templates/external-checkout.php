<?php

if (!defined('ABSPATH')) {
    exit;
}

$gateway = new WC_codarab_rest_api_Gateway();

try {
    $order_id = WC()->checkout()->create_order([
        'payment_method' => 'codarab_rest_api'
    ]);

    $order = wc_get_order($order_id);
    $checkout_url = $gateway->generate_checkout_url($order);
    
    // Clear any existing output
    ob_clean();
    
    // Perform direct redirect
    wp_redirect($checkout_url);
    exit;
    
} catch (Exception $e) {
    wc_add_notice($e->getMessage(), 'error');
    wp_redirect(wc_get_cart_url());
    exit;
}
