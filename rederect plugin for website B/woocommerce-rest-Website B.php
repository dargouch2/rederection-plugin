<?php
/**
 * @package Woocomerce-Rest-B
 */
/*
Plugin Name: Woocomerce-Rest-B
Plugin URI: https://codarab.com/
Description: Get Data from website a and create payment.
Version: 13.5.1
Author: CODARAB PAY
Author URI: https://codarab.com/
*/

function rest_b_ckeckout()
{
    function isSafari()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        return strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false;
    }

    // Check if the browser is Safari
    if (isSafari() && !isset($_GET['granted'])) {
        setcookie('dummy', 'dummy', [
            'expires' => 0,
            'path' => '/',
            'secure' => true,
            'httponly' => false,
            'samesite' => 'None'
        ]);
        header("Referrer-Policy: no-referrer");
        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Storage Access Required</title>
            <style>
                .container {
                    text-align: center;
                    padding: 20px;
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                }

                .button {
                    background-color: #007AFF;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 16px;
                    margin: 10px 0;
                }

                .error-message {
                    color: #FF3B30;
                    margin-top: 10px;
                    display: none;
                }

                .status-message {
                    margin-bottom: 15px;
                }

                .instructions {
                    text-align: left;
                    margin: 20px auto;
                    max-width: 500px;
                    padding: 15px;
                    background: #f8f8f8;
                    border-radius: 8px;
                    display: none;
                }

                .instructions li {
                    margin-bottom: 8px;
                }
            </style>
        </head>

        <body>
            <div class="container">
                <p class="status-message">Safari requires storage access to process your payment securely.</p>
                <button id="storageAccessButton" class="button">Click to allow storage access</button>
                <p id="errorMessage" class="error-message"></p>
                <div id="instructions" class="instructions">
                    <p><strong>To enable storage access in Safari:</strong></p>
                    <ol>
                        <li>Click on "Safari" in the top menu</li>
                        <li>Select "Settings" (or "Preferences" in older versions)</li>
                        <li>Go to the "Privacy" tab</li>
                        <li>Uncheck "Prevent cross-site tracking"</li>
                        <li>Reload this page</li>
                    </ol>
                    <p>After following these steps, try clicking the button again.</p>
                </div>
            </div>
            <script>
                const url = new URL(window.location.href);
                url.searchParams.append('granted', 'true');

                function showError(message) {
                    const errorElement = document.getElementById('errorMessage');
                    const instructions = document.getElementById('instructions');

                    errorElement.textContent = message;
                    errorElement.style.display = 'block';
                    instructions.style.display = 'block';
                }

                async function checkStorageAccess() {
                    try {
                        if (!document.hasStorageAccess) {
                            throw new Error('Storage Access API is not available');
                        }

                        const hasAccess = await document.hasStorageAccess();
                        console.log("Has access:", hasAccess);
                        if (hasAccess) {
                            window.location.href = url.href;
                        }
                    } catch (err) {
                        console.error("Error checking storage access:", err);
                        showError("Storage access check failed. Safari's cross-site tracking prevention may be enabled.");
                    }
                }

                async function requestAccess() {
                    const button = document.getElementById("storageAccessButton");
                    button.disabled = true;
                    button.textContent = "Requesting access...";

                    try {
                        if (!document.requestStorageAccess) {
                            throw new Error('Storage Access API is not available');
                        }

                        let hasAccess = false;
                        try {
                            hasAccess = await document.hasStorageAccess();
                        } catch (err) {
                            console.log("Error checking access:", err);
                        }

                        console.log("Current access status:", hasAccess);

                        if (hasAccess) {
                            window.location.href = url.href;
                            return;
                        }

                        try {
                            await document.requestStorageAccess();
                            console.log("Access granted!");
                            window.location.href = url.href;
                        } catch (err) {
                            console.log("Request access error:", err);
                            throw new Error('Storage access was denied. Please check your Safari privacy settings.');
                        }
                    } catch (err) {
                        console.error("Storage access error:", err);
                        showError(err?.message || "Storage access was denied. Please check your Safari privacy settings.");
                    } finally {
                        button.disabled = false;
                        button.textContent = "Click to allow storage access";
                    }
                }

                // Initial check
                checkStorageAccess().catch(err => {
                    console.error("Initial check failed:", err);
                });

                // Button click handler
                document.getElementById("storageAccessButton").addEventListener("click", requestAccess);
            </script>
        </body>

        </html>
        <?php
        exit;
    }

    $total_amount = floatval($_GET['total_amount']);
    $myordernow = $_GET['myordernow'];
    $secondsite = urldecode($_GET['secondsite']);

    setcookie('new_price', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => true,
        'httponly' => false,
        'samesite' => 'None'
    ]);
    setcookie('myordernow', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => true,
        'httponly' => false,
        'samesite' => 'None'
    ]);
    setcookie('secondsite', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => true,
        'httponly' => false,
        'samesite' => 'None'
    ]);

    setcookie('new_price', $total_amount, [
        'expires' => 0,
        'path' => '/',
        'secure' => true,
        'httponly' => false,
        'samesite' => 'None'
    ]);
    setcookie('myordernow', $myordernow, [
        'expires' => 0,
        'path' => '/',
        'secure' => true,
        'httponly' => false,
        'samesite' => 'None'
    ]);
    setcookie('secondsite', $secondsite, [
        'expires' => 0,
        'path' => '/',
        'secure' => true,
        'httponly' => false,
        'samesite' => 'None'
    ]);

    wc_prefilled_checkout_fields();
}

add_action('wp_ajax_rest_b_ckeckout', 'rest_b_ckeckout');
add_action('wp_ajax_nopriv_rest_b_ckeckout', 'rest_b_ckeckout');

function wc_prefilled_checkout_query_vars($vars)
{
    $vars[] = 'total_amount';
    $vars[] = 'billing_first_name';
    $vars[] = 'billing_last_name';
    $vars[] = 'billing_email';
    $vars[] = 'billing_company';
    $vars[] = 'billing_address_1';
    $vars[] = 'billing_address_2';
    $vars[] = 'billing_city';
    $vars[] = 'billing_state';
    $vars[] = 'billing_postcode';
    $vars[] = 'billing_country';
    $vars[] = 'billing_phone';
    $vars[] = 'currency';
    $vars[] = 'myordernow';
    $vars[] = 'secondsite';
    return $vars;
}
add_filter('query_vars', 'wc_prefilled_checkout_query_vars');

function nc_get_request_id()
{
    global $nc_request_id;

    if (empty($nc_request_id)) {
        $nc_request_id = uniqid('REQ_', true);
    }

    return $nc_request_id;
}
function wc_prefilled_checkout_fields()
{
    WC()->cart->empty_cart();

    $products = get_posts(array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'fields' => 'ids',
    ));

    if ($products) {
        $random_product_id = array_rand($products);
        $product_id = $products[$random_product_id];
    } else {
        exit;
    }

    $purchased_products = isset($_GET['products']) ? json_decode(stripslashes(urldecode($_GET['products'])), true) : [];

    foreach ($purchased_products as $_product) {
        WC()->cart->add_to_cart($product_id, $_product['qty'], 0, [], [
            'rest_b_price' => floatval($_product['price']),
            'rest_b_name' => $_product['name'],
            'rest_b_qty' => intval($_product['qty']),
        ]);
    }

    if (empty($purchased_products)) {
        WC()->cart->add_to_cart($product_id, 1);
    }

    if (isset($_GET['billing_first_name']))
        WC()->customer->set_billing_first_name($_GET['billing_first_name']);
    if (isset($_GET['billing_last_name']))
        WC()->customer->set_billing_last_name($_GET['billing_last_name']);
    if (isset($_GET['billing_email']))
        WC()->customer->set_billing_email($_GET['billing_email']);
    if (isset($_GET['billing_company']))
        WC()->customer->set_billing_company($_GET['billing_company']);
    if (isset($_GET['billing_address_1']))
        WC()->customer->set_billing_address_1($_GET['billing_address_1']);
    if (isset($_GET['billing_address_2']))
        WC()->customer->set_billing_address_2($_GET['billing_address_2']);
    if (isset($_GET['billing_city']))
        WC()->customer->set_billing_city($_GET['billing_city']);
    if (isset($_GET['billing_state']))
        WC()->customer->set_billing_state($_GET['billing_state']);
    if (isset($_GET['billing_postcode']))
        WC()->customer->set_billing_postcode($_GET['billing_postcode']);
    if (isset($_GET['billing_country']))
        WC()->customer->set_billing_country($_GET['billing_country']);
    if (isset($_GET['billing_phone']))
        WC()->customer->set_billing_phone($_GET['billing_phone']);
    if (isset($_GET['currency'])) {
        global $woocommerce;
        $woocommerce->session->set('order_currency', $_GET['currency']);
    }

    $checkout_url = wc_get_checkout_url();
    header("Referrer-Policy: no-referrer");
    wp_safe_redirect($checkout_url);
    exit;
}

add_action('woocommerce_before_calculate_totals', 'add_custom_price_to_cart_item', 99, 1);

function add_custom_price_to_cart_item($cart)
{
    $has_products = false;

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item['rest_b_name']) && isset($cart_item['rest_b_price'])) {
            $cart_item['data']->set_name($cart_item['rest_b_name']);
            $cart_item['data']->set_price($cart_item['rest_b_price']);

            $has_products = true;
        }
    }

    if ($has_products) {
        return;
    }

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($_COOKIE['new_price'])) {
            $new_price = floatval($_COOKIE['new_price']);
            $cart_item['data']->set_price($new_price);
        }
    }
}

add_action('woocommerce_checkout_order_processed', 'update_external_order_id_and_url_meta', 10, 3);

function update_external_order_id_and_url_meta($order_id, $posted_data, $order)
{
    if (isset($_COOKIE['myordernow']))
        update_post_meta($order_id, 'external_order_id', $_COOKIE['myordernow']);
    if (isset($_COOKIE['secondsite']))
        update_post_meta($order_id, 'hook_url', $_COOKIE['secondsite']);
}

function request_call_to_a($order_id, $endpoint_path)
{
    $hook_url = rtrim(get_post_meta($order_id, 'hook_url', true), '/');
    $external_order_id = get_post_meta($order_id, 'external_order_id', true);

    if (empty($hook_url) || empty($external_order_id))
        return;

    $order = wc_get_order($order_id);

    $url = $hook_url . '/wp-json/api/v2/' . $endpoint_path;
    $fields = array(
        'order_id' => $external_order_id,
        'customer_details' => array(
            'billing_first_name' => $order->get_billing_first_name(),
            'billing_last_name' => $order->get_billing_last_name(),
            'billing_email' => $order->get_billing_email(),
            'billing_company' => $order->get_billing_company(),
            'billing_address_1' => $order->get_billing_address_1(),
            'billing_address_2' => $order->get_billing_address_2(),
            'billing_city' => $order->get_billing_city(),
            'billing_state' => $order->get_billing_state(),
            'billing_postcode' => $order->get_billing_postcode(),
            'billing_country' => $order->get_billing_country(),
            'billing_phone' => $order->get_billing_phone(),
        ),
    );

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => http_build_query($fields)
    ));

    $response = curl_exec($ch);
    curl_close($ch);
    echo $response;
}

function hook_request_call_to_a($order_id, $endpoint_path)
{
    request_call_to_a($order_id, $endpoint_path);
}

add_action('woocommerce_order_status_completed', function ($order_id) {
    hook_request_call_to_a($order_id, 'wcwcwcwcwcw837378373773-order-completed');
}, 10, 1);

add_action('woocommerce_order_status_refunded', function ($order_id) {
    hook_request_call_to_a($order_id, 'wcwcwcwcwcw837378373773-order-refunded');
}, 10, 1);

add_action('woocommerce_order_status_cancelled', function ($order_id) {
    hook_request_call_to_a($order_id, 'wcwcwcwcwcw837378373773-order-cancelled');
}, 10, 1);

add_action('woocommerce_order_status_processing', function ($order_id) {
    hook_request_call_to_a($order_id, 'wcwcwcwcwcw837378373773-order-processing');
}, 10, 1);

add_action('woocommerce_order_status_on-hold', function ($order_id) {
    hook_request_call_to_a($order_id, 'wcwcwcwcwcw837378373773-order-hold');
}, 10, 1);

add_action('woocommerce_order_status_pending', function ($order_id) {
    hook_request_call_to_a($order_id, 'wcwcwcwcwcw837378373773-order-pending');
}, 10, 1);

add_action('woocommerce_thankyou', function ($order_id) {
    hook_request_call_to_a($order_id, 'wcwcwcwcwcw837378373773-order-zeftprocessing');
}, 10, 1);

add_action('wp_footer', 'send_scroll_height_to_parent');

function send_scroll_height_to_parent()
{
    if (!is_checkout())
    // return;
    ?>
    <script type="text/javascript">
        var prevHeight = 0;
        var repeat = 0;
        // document.body.style.overflow = 'hidden';
        function sendScrollHeight() {
            var scrollHeight = window.document.body.scrollHeight;

            if (scrollHeight === prevHeight)
                return;

            if (repeat > 3)
                return;

            repeat++;

            prevHeight = scrollHeight;

            window.parent.postMessage({ scrollHeight: scrollHeight }, '*');
        }
        sendScrollHeight();
        setInterval(sendScrollHeight, 1000);
    </script>
    <?php
}

function wp_ajax_nopriv_flush_rewrites()
{
    /**
     * @var WP_Rewrite $wp_rewrite
     */
    global $wp_rewrite;

    $wp_rewrite->flush_rules(true);
    save_mod_rewrite_rules();

    // $rules = explode("\n", $wp_rewrite->mod_rewrite_rules());

    // read .htaccess file and return its content
    // $htaccess_path = ABSPATH . '.htaccess';
    // if (file_exists($htaccess_path)) {
    //     insert_with_markers($htaccess_path, 'WordPress', $rules);
    //     $htaccess = file_get_contents($htaccess_path);
    //     echo $htaccess;
    // } else {
    //     echo 'File does not exist';
    // }
    die;
}
add_action('wp_ajax_nopriv_flush_rewrites', 'wp_ajax_nopriv_flush_rewrites');
add_action('wp_ajax_flush_rewrites', 'wp_ajax_nopriv_flush_rewrites');

register_activation_hook(__FILE__, function () {
    /**
     * @var WP_Rewrite $wp_rewrite
     */
    global $wp_rewrite;

    $wp_rewrite->flush_rules(true);

    save_mod_rewrite_rules();
});

add_filter('insert_with_markers_inline_instructions', function ($instructions, $markers) {
    $instructions[] = "<IfModule mod_headers.c>";
    $instructions[] = "  Header set Content-Security-Policy \"frame-ancestors 'self' *; frame-src 'self' *; child-src 'self' *\"";
    $instructions[] = "  Header set Access-Control-Allow-Origin \"*\"";
    $instructions[] = "  Header set Access-Control-Allow-Credentials \"true\"";
    // $instructions[] = "  Header set Permissions-Policy \"storage-access=*\"";
    // $instructions[] = "  Header set X-Frame-Options \"ALLOW-FROM *\"";
    $instructions[] = "</IfModule>";

    return $instructions;
}, 10, 2);

add_filter('woocommerce_set_cookie_options', function ($options) {
    $options['samesite'] = 'None';
    return $options;
});

add_filter('woocommerce_checkout_update_order_review_expired', '__return_false');
