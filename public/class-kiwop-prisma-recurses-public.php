<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://kiwop.com
 * @since      1.0.0
 *
 * @package    Kiwop_Prisma_Recurses
 * @subpackage Kiwop_Prisma_Recurses/public
 */


class Kiwop_Prisma_Recurses_Public {

	private $plugin_name;
	private $version;

    
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

        
        //add_action('init', array($this,'permitir_cors'));       
        //add_action('rest_api_init', array($this,'my_custom_rest_cors'), 15 );
       
	}

	public function enqueue_styles() {
		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/kiwop-prisma-recurses-public.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/kiwop-prisma-recurses-public.js', array( 'jquery' ), $this->version, false );
	}



}
