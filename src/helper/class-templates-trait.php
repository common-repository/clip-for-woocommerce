<?php
/**
 * Class Templates Trait
 *
 * @package  Ecomerciar\Clip\Helper\TemplatesTrait
 */

namespace Ecomerciar\CLip\Helper;

/**
 * Templates Trait
 */
trait TemplatesTrait {

	/**
	 * Get Template Part
	 *
	 * @param string $slug Slug name.
	 * @param string $name Template name.
	 * @param Array  $args Other Arguments to pass to template.
	 */
	public static function get_template_part( $slug, $name = null, $args = array() ) {

		/**
		 * Clip Get Template Part.
		 *
		 * Action to let administrators change template parts in case it's necessary.
		 *
		 * @since 2023.05.01
		 * @param string $slug slug
		 * @param string $name name
		 * @param array $args {
		 *     Array of settings
		 * }
		 */
		do_action( "clip_get_template_part_{$slug}", $slug, $name, $args );
		$templates = array();
		if ( isset( $name ) ) {
			$templates[] = "{$slug}-{$name}.php";
		}

		$templates[] = "{$slug}.php";

		self::get_template_path( $templates, true, false, $args );
	}

	/**
	 * Get Template Path & Load
	 *
	 * @param string $template_names Templates Names.
	 * @param bool   $load Load.
	 * @param bool   $require_once Require Once.
	 * @param Array  $args Other Arguments to pass to template.
	 */
	public static function get_template_path( $template_names, $load = false, $require_once = true, $args = array() ) {
		$located = '';
		foreach ( (array) $template_names as $template_name ) {
			if ( ! $template_name ) {
				continue;
			}
			// Search file within the PLUGIN_DIR_PATH only.
			if ( file_exists( \Clip::MAIN_DIR . '/templates/' . $template_name ) ) {
				$located = \CLip::MAIN_DIR . '/templates/' . $template_name;
				break;
			}
		}

		if ( $load && '' !== $located ) {
			load_template( $located, $require_once, $args );
		}
		return $located;
	}
}
