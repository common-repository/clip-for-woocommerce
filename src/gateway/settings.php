<?php
/**
 * Settings.php
 *
 * @package  Ecomerciar\Clip\Gateway\
 */

namespace Ecomerciar\Clip\Gateway;

use Ecomerciar\Clip\Helper\Helper;

/**
 * Clip Form Fields.
 *
 * Filter to Edit Clip Settings Fields.
 *
 * @since 2023.05.01
 *
 * @param array $args {
 *     Array of settings fields for clip payment gateway
 * }
 * @param array $args Clip Settings.
 */
return apply_filters(
	'wc_clip_form_fields',
	array(
		'enabled'                      => array(
			'title'   => __( 'Enable/Disable', 'clip' ),
			'type'    => 'checkbox',
			'label'   => __( 'Enable Clip Payment Gateway', 'clip' ),
			'default' => 'yes',
		),

		'wc_clip_credentials_subtitle' => array(
			'title'       => '',
			'type'        => 'title',
			'description' => __( "If you still do not have your credentials to operate with Clip, click <a href='https://dashboard.clip.mx/md/users/sign_in?pathname=%2Fusers%2Fsign_in' target='_bank'>here</a>.", 'clip' ),
		),


		'wc_clip_api_key'              => array(
			'title' => __( 'Client Api Key', 'clip' ),
			'type'  => 'text',
		),

		'wc_clip_api_secret'           => array(
			'title' => __( 'Client Api Secret', 'clip' ),
			'type'  => 'password',
		),


		'wc_clip_validations_section'  => array(
			'title'       => __( 'Validation', 'clip' ),
			'type'        => 'title',
			'description' => Helper::validate_all_html(),
		),

		'wc_clip_payment_options'      => array(
			'title'   => __( 'Payment Options', 'clip' ),
			'type'    => 'select',
			'default' => 'BOTH',
			'options' => array(
				'BOTH' => __( 'Both', 'clip' ),
				'CASH' => __( 'Only Cash', 'clip' ),
				'CARD' => __( 'Only Card', 'clip' ),

			),
			'default' => 'BOTH',
		),

		'wc_clip_banner_enabled'          => array(
			'title'       => __( 'Enable/Disable', 'clip' ),
			'type'        => 'checkbox',
			'label'       => __( 'Activate Banner', 'clip' ),
		),

		'wc_clip_expiration_hours' => array(
			'title'       => __( 'Checkout expiration time (hours)', 'clip' ),
			'type'        => 'number',
			'description' => sprintf(
				/* translators: %s: System Flag */
				__(
					'This field will determine the maximum checkout time to receive your payment with Clip while the order remains pending payment and only one integer value is allowed. If no value is added, the default maximum time will be 72 hours.',
					'clip'
				),
			),
			'sanitize_callback' => function($input) {
				// Limitar el valor a un rango específico, por ejemplo, de 0 a 72 horas
				$input = intval($input);
				$input = max(0, min(72, $input));
				return $input;
			},
		),

		'wc_clip_log_enabled'          => array(
			'title'       => __( 'Enable/Disable', 'clip' ),
			'type'        => 'checkbox',
			'label'       => __( 'Activate Logs', 'clip' ),
			'description' => sprintf(
			/* translators: %s: System Flag */
				__(
					'You can enable plugin debugging to track communication between the plugin and Clip API. You will be able to view the record from the <a href="%s">WooCommerce > Status > Records</a> menu.',
					'clip'
				),
				esc_url( get_admin_url( null, 'admin.php?page=wc-status&tab=logs' ) )
			),
			'default'     => 'yes',
		),

		'wc_clip_msi' => array(
			'title'       => '',
			'type'        => 'title',
			'description' => __( "If you want to activate MSI you can configure them in the <a href='https://dashboard.clip.mx/md/marketing_tools' target='_bank'>Clip panel</a>.", 'clip' ),
		),
	)
);


