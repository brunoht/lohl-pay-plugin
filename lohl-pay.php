<?php

require_once 'custom-wc-shortcode-checkout.php'; // Adjust the path accordingly.

/**
 * Plugin Name: Lohl Pay
 * Plugin URI: https://lohl.com.br/
 * Description: Lohl Payment Plugin
 * Version: 0.0.1
 * Author: Lohl
 * Author URI: https://lohl.com.br
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

// Allow WooCommerce existing customers to checkout without being logged in (allow orders from existing customers in WooCommerce without logging in)
function your_custom_function_name( $allcaps, $caps, $args ) {
    if ( isset( $caps[0] ) ) {
        switch ( $caps[0] ) {
            case 'pay_for_order' :
                $order_id = isset( $args[2] ) ? $args[2] : null;
                $order = wc_get_order( $order_id );
                $user = $order->get_user();
                $user_id = $user->ID;
                if ( ! $order_id ) {
                    $allcaps['pay_for_order'] = true;
                    break;
                }

                $order = wc_get_order( $order_id );

                if ( $order && ( $user_id == $order->get_user_id() || ! $order->get_user_id() ) ) {
                    $allcaps['pay_for_order'] = true;
                }
                break;
        }
    }
    return $allcaps;
}
add_filter( 'user_has_cap', 'your_custom_function_name', 10, 3 );

add_filter( 'woocommerce_checkout_posted_data', 'ftm_filter_checkout_posted_data', 10, 1 );
function ftm_filter_checkout_posted_data( $data ) {
    $email = $data['billing_email'];
    if ( is_user_logged_in() ) {
    } else {
        if (email_exists( $email)){
            $user = get_user_by( 'email', $email );
            if ($user){
                $user_id = $user->ID;
                wc_set_customer_auth_cookie($user_id);
                session_start();
                $_SESSION['p33'] = "133";
                $_SESSION['u'] = $user_id;

            } else {
                $user_id = false;
            }
        }
    }
    return $data;
}

add_action( 'woocommerce_new_order', 'clearuser' );
function clearuser($data) {

    if ($_SESSION['p33']==133){
        //WC()->session->set('pp1',"0");
        nocache_headers();
        wp_clear_auth_cookie();

        $yourSession= WP_Session_Tokens::get_instance($_SESSION['u']);
        $yourSession->destroy_all();

        $_SESSION['p33']='';
        $_SESSION['u']='';
    }
}

//End Allow Woocommerce Order Pay Without LogIn


// Include your custom class file.


function override_wc_shortcodes() {
    remove_shortcode( 'woocommerce_checkout', array( 'WC_Shortcode_Checkout', 'output' ) );
    add_shortcode( 'woocommerce_checkout', array( 'Custom_WC_Shortcode_Checkout', 'output' ) );
}

add_action( 'init', 'override_wc_shortcodes' );

