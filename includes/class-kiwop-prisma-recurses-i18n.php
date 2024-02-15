<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://kiwop.com
 * @since      1.0.0
 *
 * @package    Kiwop_Prisma_Recurses
 * @subpackage Kiwop_Prisma_Recurses/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Kiwop_Prisma_Recurses
 * @subpackage Kiwop_Prisma_Recurses/includes
 * @author     Antonio Sanchez <antonio@kiwop.com>
 */
class Kiwop_Prisma_Recurses_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'kiwop-prisma-recurses',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
