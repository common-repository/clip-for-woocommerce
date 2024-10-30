<?php
/**
 * Hooks
 *
 * @package  Ecomerciar\Clip\
 */

defined( 'ABSPATH' ) || exit();

// --- Settings
add_filter(
	'plugin_action_links_' . plugin_basename( \Clip::MAIN_FILE ),
	array(
		'Clip',
		'create_settings_link',
	)
);

// --- Payment Method
add_filter( 'woocommerce_payment_gateways', array( 'Clip', 'add_payment_method' ) );
add_filter(
	'woocommerce_available_payment_gateways',
	array(
		'\Ecomerciar\Clip\Gateway\WC_Clip',
		'available_payment_method',
	)
);

// --- Onboarding
add_action( 'admin_menu', array( '\Ecomerciar\Clip\Onboarding\Main', 'register_onboarding_page' ) );

// --- Frontend buttons
add_action(
	'woocommerce_receipt_' . \Clip::GATEWAY_ID,
	array( '\Ecomerciar\Clip\Gateway\PostCheckout', 'render' ),
	90
);

// --- Endpoints Settings for Onboarding
// --- Webhook
add_action(
	'wp_ajax_clip_save_settings',
	array(
		'\Ecomerciar\Clip\Onboarding\SaveSettingsAction',
		'ajax_callback_wp',
	)
);

add_action( 'wp_enqueue_scripts', array( 'Clip', 'register_front_scripts' ) );

// --- Order Ajax Actions
add_action(
	'wp_ajax_clip_request_deposit_action',
	array(
		'\Ecomerciar\Clip\Gateway\RequestDepositAction',
		'ajax_callback_wp',
	)
);

// --- Webhook
add_action(
	'woocommerce_api_wc-clip',
	array(
		'\Ecomerciar\Clip\Orders\Webhooks',
		'listener',
	)
);

add_action(
	'wp_ajax_nopriv_clip_request_deposit_action',
	array(
		'\Ecomerciar\Clip\Gateway\RequestDepositAction',
		'ajax_callback_wp',
	)
);

