<?php
/**
 * @package Woocomerce-rederection website A
 */
/*
Plugin Name: CODARAB Pay Redirect
Plugin URI: https://codarab.com/
Description: Seamlessly redirect WooCommerce order data from a high-risk source (Website A) to a low-risk destination (Website B) within the same website URL, ensuring a smooth customer experience without any disruptions.
Version: 13.5.1
Author: CODARAB PAY
Author URI: https://codarab.com/
*/

if (!defined('ABSPATH')) {
    exit;
}

// Activation System from CODARAB PAY Integrated
add_action('admin_menu', function() {
    add_menu_page(
        'COARAB Pay Redirect Activate',
        'COARAB Pay Redirect Activate',
        'manage_options',
        'codarab_rest_a_activation',
        function() { ?>
            <div class="wrap">
                <h2>COARAB Pay Redirect Activate</h2>
            </div>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="codarab_pgActivekey">Activation Key</label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="codarab_pgActivekey" id="codarab_pgActivekey" type="text" value="<?php echo get_option('codarab_pgActivekey'); ?>">
                        <span style="margin-left:10px;padding: 2px 15px;border: 1px solid #007cba;font-size: 14px;font-weight: 600;color: #007cba;cursor: pointer;" id="codarab_updatePGActivationKey">Verify</span>
                        <div class="lds-dual-ring"></div>
                        <div class="check-ok"></div>
                        <div class="check-error">X</div>
                    </td>
                </tr>
                <th scope="row" class="titledesc">
                    <label for="codarab_pgActivekeyExpire">Expire Date</label>
                </th>
                <td class="forminp forminp-text">
                    <input name="codarab_pgActivekeyExpire" id="codarab_pgActivekeyExpire" type="text" value="<?php echo get_option('codarab_pgActivekeyExpire'); ?>" disabled>
                </td>
            </table>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    var updatePGActivationKey = document.getElementById("codarab_updatePGActivationKey");

                    if (updatePGActivationKey) {
                        updatePGActivationKey.addEventListener("click", function() {
                            jQuery('.lds-dual-ring').css('display', 'inline-block');
                            jQuery('.check-ok').css('display', 'none');
                            jQuery('.check-error').css('display', 'none');
                            var codarab_pgActiveKey = jQuery('#codarab_pgActivekey').val();

                            jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                                action: 'validate_codarab_pg_activation_key',
                                codarab_pg_active_key: codarab_pgActiveKey
                            }, function(response) {
                                var data = JSON.parse(response.slice(0, -1));
                                var $codarab_pgActivekey = jQuery('#codarab_pgActivekey');
                                var $codarab_pgActivekeyExpire = jQuery('#codarab_pgActivekeyExpire');

                                if (data.success) {
                                    jQuery('.lds-dual-ring').css('display', 'none');
                                    $codarab_pgActivekey.css('border-color', 'green');
                                    $codarab_pgActivekeyExpire.val(data.expire_date);
                                    $codarab_pgActivekeyExpire.css('border-color', '');
                                    $codarab_pgActivekeyExpire.css('color', '');
                                    jQuery('.check-ok').css('display', 'inline-block');
                                } else {
                                    jQuery('.lds-dual-ring').css('display', 'none');
                                    $codarab_pgActivekey.css('border-color', 'red');
                                    $codarab_pgActivekeyExpire.val(data.message);
                                    $codarab_pgActivekeyExpire.css('border-color', 'red');
                                    $codarab_pgActivekeyExpire.css('color', 'red');
                                    jQuery('.check-error').css('display', 'inline-block');
                                }
                            });
                        });
                    }
                });
            </script>
            <style>
                .lds-dual-ring {
                    display: none;
                    width: 20px;
                    height: 20px;
                }
                .lds-dual-ring:after {
                    content: " ";
                    display: block;
                    width: 20px;
                    height: 20px;
                    margin: 8px;
                    border-radius: 50%;
                    border: 6px solid #fff;
                    border-color: blue transparent blue transparent;
                    animation: lds-dual-ring 1.2s linear infinite;
                    margin-top: 2px;
                }
                @keyframes lds-dual-ring {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                .check-ok {
                    display: none;
                    transform: rotate(45deg);
                    height: 16px;
                    width: 8px;
                    border-bottom: 4px solid #78b13f;
                    border-right: 4px solid #78b13f;
                    margin-left: 5px;
                }
                .check-error {
                    display: none;
                    font-size: 18px;
                    font-weight: bold;
                    color: red;
                }
            </style>
        <?php },
        'dashicons-admin-plugins',
        90
    );
});

add_action('wp_ajax_validate_codarab_pg_activation_key', function() {
    $codarab_pg_active_key = sanitize_text_field($_POST['codarab_pg_active_key']);
    $api_url = 'https://codarab.com/wp-json/activation-key-verify/pluginActivationkey';
    $api_data = array('api_key' => $codarab_pg_active_key, 'website_url' => home_url(), 'access_code' => 'codarab_rest_a');

    $response = wp_remote_post($api_url, array(
        'body' => $api_data,
        'timeout' => 20,
    ));

    if (is_wp_error($response)) {
        $is_valid = false;
        $expire_date = '';
    } else {
        $api_response = json_decode(wp_remote_retrieve_body($response), true);
        $is_valid = isset($api_response['status']) && $api_response['status'];
        $expire_date = $is_valid ? $api_response['data']['expiry_date'] : '';
    }
    $data = json_decode($response['body']);

    if ($data->status == true) {
        update_option('codarab_pgActivekey', $codarab_pg_active_key);
        update_option('codarab_pgActivekeyExpire', $data->expiry_date);
        echo json_encode(array('success' => true, 'expire_date' => $data->expiry_date));
    } else {
        update_option('codarab_pgActivekey', $codarab_pg_active_key);
        update_option('codarab_pgActivekeyExpire', '');
        echo json_encode(array('success' => false, 'message' =>  $data->message));
    }
});

function codarab_plugin_activation_success() {
    $codarab_pg_active_key = get_option('codarab_pgActivekey');
    $api_url = 'https://codarab.com/wp-json/activation-key-verify/pluginActivationkey';
    $api_data = array('api_key' => $codarab_pg_active_key, 'website_url' => home_url(), 'access_code' => 'codarab_rest_a');

    $response = wp_remote_post($api_url, array(
        'body' => $api_data,
        'timeout' => 20,
    ));

    if (is_wp_error($response)) {
        return false;
    } else {
        $api_response = json_decode(wp_remote_retrieve_body($response), true);
        return isset($api_response['status']) && $api_response['status'];
    }
}

add_action('admin_init', function() {
    global $pagenow;

    if ($pagenow === 'admin.php' && isset($_GET['page']) && $_GET['page'] === 'wc-settings' && isset($_GET['tab']) && $_GET['tab'] === 'checkout' && isset($_GET['section']) && $_GET['section'] === 'codarab_rest_api') {
        if (!codarab_plugin_activation_success()) {
            wp_safe_redirect(admin_url('admin.php?page=codarab_rest_a_activation'));
            exit;
        }
    }
});

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    if (!codarab_plugin_activation_success()) {
        array_unshift($links, sprintf(
            '<a href="%1$s">%2$s</a>',
            admin_url('admin.php?page=codarab_rest_a_activation'),
            __('Activate', 'woocommerce')
        ));
    } else {
        array_unshift($links, sprintf(
            '<a href="%1$s">%2$s</a>',
            admin_url('admin.php?page=wc-settings&tab=checkout&section=codarab_rest_api'),
            __('Settings', 'woocommerce')
        ));
    }
    return $links;
});

add_action('wp_head', function() {
    if (!codarab_plugin_activation_success()) {
        echo '<style>li.wc_payment_method.payment_method_codarab_rest_api { display: none !important; }</style>';
    }
});

add_action('plugins_loaded', 'init_codarab_rest_api_gateway');

function init_codarab_rest_api_gateway()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    class WC_codarab_rest_api_Gateway extends WC_Payment_Gateway
    {
        public $lowrisk_shop_url;
        public $redirect_timing;

        public function __construct()
        {
            $this->id = 'codarab_rest_api';
            $this->has_fields = false;
            $this->method_title = 'CODARAB PAY WC Rest API Payment Gateways';
            $this->method_description = 'This payment method will redirect the customer to your front shop, allowing payment via another WooCommerce installation using REST API';
            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->lowrisk_shop_url = $this->get_option('lowrisk_shop_url');
            $this->redirect_timing = $this->get_option('redirect_timing', 'payment');

            $this->icon = plugin_dir_url(__FILE__) . 'credit-cards.png';

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_api_codarab_rest_api_callback', array($this, 'check_callback_response'));
        }

        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => 'Enable/Disable',
                    'type' => 'checkbox',
                    'label' => 'Enable codarab_rest_api Gateway',
                    'default' => 'yes',
                ),
                'title' => array(
                    'title' => 'Title',
                    'type' => 'text',
                    'description' => 'This controls the title which the user sees during checkout.',
                    'default' => 'Credit Card',
                    'desc_tip' => true,
                ),
                'description' => array(
                    'title' => 'Description',
                    'type' => 'textarea',
                    'description' => 'This controls the description which the user sees during checkout.',
                    'default' => 'Pay by your credit or debit card',
                ),
                'lowrisk_shop_url' => array(
                    'title' => 'Low Risk Shop URL',
                    'type' => 'text',
                    'description' => 'Insert low risk shop URL',
                    'default' => '',
                    'desc_tip' => true,
                ),
                'redirect_timing' => array(
                    'title' => 'Redirect Timing',
                    'type' => 'select',
                    'description' => 'Choose when to redirect the customer to Site B',
                    'default' => 'payment',
                    'options' => array(
                        'add_to_cart' => 'When clicking Add to Cart',
                        'checkout' => 'When clicking Proceed to Checkout',
                        'payment' => 'When selecting payment method',
                    ),
                ),
            );
        }

        public function generate_checkout_url(WC_Order $order)
        {
            $myordernow = $order->get_id();
            $secondsite = home_url();
            $products = array_reduce($order->get_items(), function ($carry, $item) {
                /**
                 * @var WC_Order_Item_Product $item
                 */
                $product = $item->get_product();
                $carry[] = array(
                    'name' => $product->get_name(),
                    'qty' => $item->get_quantity(),
                    'price' => $product->get_price(),
                );
                return $carry;
            }, []);

            $params = array(
                'total_amount' => urlencode($order->get_total()),
                'billing_first_name' => urlencode($order->get_billing_first_name()),
                'billing_last_name' => urlencode($order->get_billing_last_name()),
                'billing_email' => urlencode($order->get_billing_email()),
                'billing_company' => urlencode($order->get_billing_company()),
                'billing_address_1' => urlencode($order->get_billing_address_1()),
                'billing_address_2' => urlencode($order->get_billing_address_2()),
                'billing_city' => urlencode($order->get_billing_city()),
                'billing_state' => urlencode($order->get_billing_state()),
                'billing_postcode' => urlencode($order->get_billing_postcode()),
                'billing_country' => urlencode($order->get_billing_country()),
                'billing_phone' => urlencode($order->get_billing_phone()),
                'currency' => urlencode($order->get_currency()),
                'myordernow' => urlencode($myordernow),
                'secondsite' => urlencode($secondsite),
                'products' => urlencode(json_encode($products)),
            );

            $base_url = rtrim($this->lowrisk_shop_url, '/');
            // $redirect_url = $base_url . '/wp-content/plugins/woocommerce-rest-b/paynow-checkout.php';
            $redirect_url = $base_url . '/wp-admin/admin-ajax.php?action=rest_b_ckeckout';

            return add_query_arg($params, $redirect_url);
        }

        public function process_payment($order_id)
        {
            $order = wc_get_order($order_id);
            $url = $this->generate_checkout_url($order);

            if ($this->redirect_timing === 'payment') {
                // set redirect_to_external_checkout
                WC()->session->set('redirect_to_external_checkout', true);

                return array(
                    'result' => 'success',
                    'redirect' => wc_get_checkout_url(),
                    // 'checkout_url' => $url,
                );
            }

            return array(
                'result' => 'success',
                'redirect' => $url,
            );
        }
    }

    function add_codarab_rest_api_gateway($methods)
    {
        $methods[] = 'WC_codarab_rest_api_Gateway';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_codarab_rest_api_gateway');
}

class WC_Rest_API
{
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'init_rest_api'));
    }

    public function init_rest_api()
    {
        register_rest_route('api/v2', '/wcwcwcwcwcw837378373773-order-completed', array(
            'methods' => 'POST',
            'callback' => array($this, 'wcwcwcwcwcwcw82828282844_change_order_to_completed'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('api/v2', '/wcwcwcwcwcw837378373773-order-refunded', array(
            'methods' => 'POST',
            'callback' => array($this, 'wcwcwcwcwcwcw82828282844_change_order_to_refunded'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('api/v2', '/wcwcwcwcwcw837378373773-order-cancelled', array(
            'methods' => 'POST',
            'callback' => array($this, 'wcwcwcwcwcwcw82828282844_change_order_to_cancelled'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('api/v2', '/wcwcwcwcwcw837378373773-order-processing', array(
            'methods' => 'POST',
            'callback' => array($this, 'wcwcwcwcwcwcw82828282844_change_order_to_processing'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('api/v2', '/wcwcwcwcwcw837378373773-order-zeftprocessing', array(
            'methods' => 'POST',
            'callback' => array($this, 'wcwcwcwcwcwcw82828282844_change_order_to_zeftprocessing'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('api/v2', '/wcwcwcwcwcw837378373773-order-hold', array(
            'methods' => 'POST',
            'callback' => array($this, 'wcwcwcwcwcwcw82828282844_change_order_to_hold'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('api/v2', '/wcwcwcwcwcw837378373773-order-pending', array(
            'methods' => 'POST',
            'callback' => array($this, 'wcwcwcwcwcwcw82828282844_change_order_to_pending'),
            'permission_callback' => '__return_true',
        ));
    }

    public function wcwcwcwcwcwcw82828282844_change_order_to_completed($data)
    {
        $order_id = $data['order_id'];
        $order = new WC_Order($order_id);
        if (!empty($order)) {
            $this->update_customer_details($order, $data);
            $order->update_status('completed');
        }
    }

    public function wcwcwcwcwcwcw82828282844_change_order_to_refunded($data)
    {
        $order_id = $data['order_id'];
        $order = new WC_Order($order_id);
        if (!empty($order)) {
            $this->update_customer_details($order, $data);
            $order->update_status('refunded');
        }
    }

    public function wcwcwcwcwcwcw82828282844_change_order_to_cancelled($data)
    {
        $order_id = $data['order_id'];
        $order = new WC_Order($order_id);
        if (!empty($order)) {
            $this->update_customer_details($order, $data);
            $order->update_status('cancelled');
        }
    }

    public function wcwcwcwcwcwcw82828282844_change_order_to_zeftprocessing($data)
    {
        $order_id = $data['order_id'];
        $order = new WC_Order($order_id);
        if (!empty($order) && $order->has_status('pending')) {
            $this->update_customer_details($order, $data);
            $order->payment_complete();
            $order->update_status('processing');
        }
    }

    public function wcwcwcwcwcwcw82828282844_change_order_to_processing($data)
    {
        $order_id = $data['order_id'];
        $order = new WC_Order($order_id);
        if (!empty($order)) {
            $this->update_customer_details($order, $data);
            if ($order->has_status('pending'))
                $order->payment_complete();
            $order->update_status('processing');
        }
    }

    public function wcwcwcwcwcwcw82828282844_change_order_to_hold($data)
    {
        $order_id = $data['order_id'];
        $order = new WC_Order($order_id);
        if (!empty($order)) {
            $this->update_customer_details($order, $data);
            $order->update_status('on-hold');
        }
    }

    public function wcwcwcwcwcwcw82828282844_change_order_to_pending($data)
    {
        $order_id = $data['order_id'];
        $order = new WC_Order($order_id);
        if (!empty($order)) {
            $this->update_customer_details($order, $data);
            $order->update_status('pending');
        }
    }

    private function update_customer_details($order, $data)
    {
        if (isset($data['customer_details'])) {
            $customer_details = $data['customer_details'];
            if (isset($customer_details['billing_first_name']))
                $order->set_billing_first_name($customer_details['billing_first_name']);
            if (isset($customer_details['billing_last_name']))
                $order->set_billing_last_name($customer_details['billing_last_name']);
            if (isset($customer_details['billing_email']))
                $order->set_billing_email($customer_details['billing_email']);
            if (isset($customer_details['billing_company']))
                $order->set_billing_company($customer_details['billing_company']);
            if (isset($customer_details['billing_address_1']))
                $order->set_billing_address_1($customer_details['billing_address_1']);
            if (isset($customer_details['billing_address_2']))
                $order->set_billing_address_2($customer_details['billing_address_2']);
            if (isset($customer_details['billing_city']))
                $order->set_billing_city($customer_details['billing_city']);
            if (isset($customer_details['billing_state']))
                $order->set_billing_state($customer_details['billing_state']);
            if (isset($customer_details['billing_postcode']))
                $order->set_billing_postcode($customer_details['billing_postcode']);
            if (isset($customer_details['billing_country']))
                $order->set_billing_country($customer_details['billing_country']);
            if (isset($customer_details['billing_phone']))
                $order->set_billing_phone($customer_details['billing_phone']);
        }
    }
}

new WC_Rest_API();

add_action('wp_footer', function () {
    codarab_add_iframe_script();
});

function codarab_add_iframe_script()
{
    $gateway = new WC_codarab_rest_api_Gateway();
    ?>
    <script type="text/javascript" id="codarab-payment-iframe-script">
        jQuery(function ($) {
            var instances = [];
            var type = '<?php echo $gateway->redirect_timing; ?>';

            var app = {
                newDiv: null,
                iframe: null,
                loadingIndicator: null,
                spinner: null,
                prevHeight: 0,
                id: '',
                mainContent: null,

                create: function (id = null) {
                    const instance = instances.find(i => i.id === id);

                    if (instance) {
                        return instance;
                    }

                    if (!id) {
                        id = Math.random().toString(36).substring(7);
                    }

                    this.id = id;
                    this.mainContent = $($('.woocommerce').get(0));
                    this.handleProceed = this.handleProceed.bind(this);

                    if (id.startsWith('product_')) {
                        this.handleProductAddToCart.call(this, id);
                    }

                    instances.push(this);

                    return this;
                },

                render: function (event, data) {
                    if (event && 'preventDefault' in event) {
                        event.preventDefault();
                    }

                    console.log({ event, data });

                    this.newDiv = $('<div>', {
                        class: 'woocommerce',
                        'data-id': 'site-b'
                    }).insertAfter(this.mainContent);

                    this.mainContent.hide();

                    this.iframe = $('<iframe>', {
                        src: data.checkout_url,
                        id: 'codarab-payment-iframe',
                        frameborder: 0,
                        scrolling: 'yes',
                        width: '100%',
                        height: '600px',
                        allow: "storage-access",
                        sandbox: "allow-storage-access-by-user-activation allow-scripts allow-same-origin allow-forms"
                    }).appendTo(this.newDiv);

                    this.loadingIndicator = $('<div>', {
                        class: 'loading-indicator',
                        css: {
                            position: 'absolute',
                            top: 0,
                            left: 0,
                            width: '100%',
                            height: '100%',
                            background: 'rgba(255, 255, 255, 0.8)',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            zIndex: 9999
                        }
                    }).appendTo(this.newDiv);

                    this.spinner = $('<div>', {
                        class: 'spinner',
                        css: {
                            border: '4px solid rgba(0, 0, 0, 0.1)',
                            borderRadius: '50%',
                            borderTop: '4px solid #000',
                            width: '40px',
                            height: '40px',
                            animation: 'spin 1s linear infinite'
                        }
                    }).appendTo(this.loadingIndicator);

                    this.iframe.on('load', this.hideLoadingIndicator.bind(this));

                    $('<style>')
                        .prop('type', 'text/css')
                        .html('@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }')
                        .appendTo('head');

                    $('html, body').animate({
                        scrollTop: this.newDiv.offset().top
                    }, 1000);

                    this.listenForHeightChange.call(this);
                },

                generateCheckoutUrl: async function () {
                    return await $.ajax({
                        type: 'POST',
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        data: {
                            action: 'codarab_process_cart_checkout',
                            _wpnonce: '<?php echo wp_create_nonce('woocommerce-process_checkout'); ?>',
                            payment_method: 'codarab_rest_api'
                        },
                        dataType: 'json'
                    });
                },

                handleProceed: async function (event) {
                    event.preventDefault();
                    const button = $(event.target);

                    try {
                        button.prop('disabled', true);
                        const response = await this.generateCheckoutUrl();
                        this.render(event, {
                            checkout_url: response.redirect
                        });
                    }
                    catch (e) {
                        console.error(e);
                        alert(e.responseJSON.message || 'An error occurred');
                    }
                    finally {
                        button.prop('disabled', false);
                    }
                },

                handleAddToCart: async function (event) {
                    event.preventDefault();

                    try {
                        const button = $(event.target);
                        const originalButtonText = button.html();
                        const $form = button.closest('form'); // Ensure the correct form element is selected

                        // Show loading state
                        button.html('Adding...').prop('disabled', true);

                        // Get all form data
                        const formData = new FormData($form[0]); // Pass the correct form element
                        formData.append('add-to-cart', button.val());

                        $.ajax({
                            type: 'POST',
                            url: '<?php echo WC_AJAX::get_endpoint('add_to_cart'); ?>',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: async function () {
                                const response = await this.generateCheckoutUrl();
                                this.render(event, {
                                    checkout_url: response.redirect
                                });
                            }.bind(this),
                            error: function (xhr, status, error) {
                                console.error('Error:', error);
                                alert('Error adding product to cart. Please try again.');
                            },
                            complete: function () {
                                // Reset button state
                                button.html(originalButtonText).prop('disabled', false);
                            }
                        });
                    }
                    catch (e) {
                        // alert(e.responseJSON.message || 'An error occurred');
                        console.error(e);
                    }
                },

                handleProductAddToCart: async function (id) {
                    if (type === 'add_to_cart') {
                        window.location = '<?php echo wc_get_checkout_url(); ?>';
                    }
                },

                listenForHeightChange: function () {
                    window.addEventListener('message', function (event) {
                        if (event.data.scrollHeight) {
                            if (event.data.scrollHeight === this.prevHeight)
                                return;

                            this.prevHeight = event.data.scrollHeight;

                            this.iframe.height(event.data.scrollHeight);
                        }
                    }.bind(this));
                },

                hideLoadingIndicator: function () {
                    this.loadingIndicator.hide();
                }
            };

            $(document.body).on('added_to_cart', function (event, fragments, cart_hash, $button) {
                Object.create(app).create('product_' + cart_hash);
            });

        });
    </script>
    <?php
}

function codarab_process_cart_checkout()
{
    try {
        // clear all notices
        wc_clear_notices();

        // Initialize the payment gateway
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        $gateway = isset($available_gateways['codarab_rest_api'])
            ? $available_gateways['codarab_rest_api']
            : null;

        if (!$gateway) {
            throw new Exception('Payment gateway not available');
        }

        add_action('woocommerce_after_checkout_validation', function ($data, WP_Error $errors) use ($gateway) {
            foreach ($errors->get_error_codes() as $code) {
                $errors->remove($code);
            }
        }, 10, 2);

        add_filter('woocommerce_checkout_posted_data', function ($data) use ($gateway) {
            $data['payment_method'] = 'codarab_rest_api';
            return $data;
        });

        // remove session
        WC()->session->__unset('redirect_to_external_checkout');

        WC()->checkout()->process_checkout();
    } catch (Throwable $e) {
        wp_send_json_error([
            'message' => $e->getMessage()
        ], 500);
    }
}

add_action('wp_ajax_codarab_process_cart_checkout', 'codarab_process_cart_checkout');
add_action('wp_ajax_nopriv_codarab_process_cart_checkout', 'codarab_process_cart_checkout');

add_action('woocommerce_add_to_cart', 'set_redirect_to_external_checkout_session', 10, 6);

function set_redirect_to_external_checkout_session($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
{
    $gateway = new WC_codarab_rest_api_Gateway();

    if ($gateway->redirect_timing === 'add_to_cart') {
        WC()->session->set('redirect_to_external_checkout', true);
    }
}

add_filter('template_include', 'check_redirect_to_external_checkout', 99);

function check_redirect_to_external_checkout($template)
{
    if (WC()->cart->is_empty()) {
        return $template;
    }

    $gateway = new WC_codarab_rest_api_Gateway();
    $should_redirect = WC()->session->get('redirect_to_external_checkout')
        || (is_checkout() && $gateway->redirect_timing === 'checkout');

    if ($should_redirect) {
        try {
            // Remove the session variable if it exists
            WC()->session->__unset('redirect_to_external_checkout');

            // remove notices
            wc_clear_notices();

            $template = __DIR__ . '/templates/external-checkout.php';
        } catch (Throwable $e) {
            die('An error occurred: ' . $e->getMessage());
        }
    }

    return $template;
}

add_filter('woocommerce_add_to_cart_redirect', 'add_to_cart_redirect_to_external_checkout');

function add_to_cart_redirect_to_external_checkout($url)
{
    $gateway = new WC_codarab_rest_api_Gateway();

    if ($gateway->redirect_timing === 'add_to_cart' && WC()->session->get('redirect_to_external_checkout')) {
        $url = wc_get_checkout_url();

        // remove notices
        wc_clear_notices();
    }

    return $url;
}

add_filter('woocommerce_payment_gateways', function($methods) {
    if (!codarab_plugin_activation_success()) {
        return array_filter($methods, function($method) {
            return $method !== 'WC_codarab_rest_api_Gateway';
        });
    }
    return $methods;
}, 20);


add_action('wp_head', function () {
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">';
});




