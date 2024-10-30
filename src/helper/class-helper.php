<?php
/**
 * Helper Class
 *
 * @package  Ecomerciar\Clip\Helper
 */

namespace Ecomerciar\Clip\Helper;

/**
 * Helper Main Class
 */
class Helper {
	use TemplatesTrait;
	use LoggerTrait;
	use DebugTrait;
	use SettingsTrait;
	use AssetsTrait;
	use DatabaseTrait;
	use HandlePaymentTrait;
	use CountryCurrencyTrait;
	use ValidationsTrait;

	const VALIDATION_OK_ICON    = '<span class="dashicons dashicons-saved" style="color:green;"></span>';
	const VALIDATION_ERROR_ICON = '<span class="dashicons dashicons-no-alt" style="color:red;"></span>';
}
