<?php
/**
 * Class ClipBlocks
 *
 * @package  Ecomerciar\Clip\Blocks\ClipBlocks
 */

 namespace Ecomerciar\Clip\Blocks;

use Ecomerciar\Clip\Helper\Helper;
use Ecomerciar\Clip\Gateway\WC_Clip;
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class ClipBlocks extends AbstractPaymentMethodType {

    private $gateway;
    protected $name = 'wc_clip';// your payment gateway name

    public function initialize() {
        $this->settings = get_option( 'woocommerce_wc_clip_settings', [] );
        $this->gateway = new WC_Clip();
    }

    public function is_active() {
        return $this->gateway->is_available();
    }

    public function get_payment_method_script_handles() {

        wp_register_script(
            'wc_clip-blocks-integration',
            Helper::get_assets_folder_url() . '/js/checkout-blocks.js',
            [
                'react',
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ],
            '1.0.1',
            true
        );
        if( function_exists( 'wp_set_script_translations' ) ) {            
            wp_set_script_translations( 'wc_clip-blocks-integration');
            
        }
        return [ 'wc_clip-blocks-integration' ];
    }

    public function get_payment_method_data() {
        return [
            'title'       => $this->gateway->title,
            'description' => $this->gateway->description,
            'icon_clip'       => $this->gateway->icon,
            'banner_clip'       => $this->gateway->banner_clip,
            'banner_enabled'       => $this->gateway->banner_enabled,
        ];
    }

}
?>