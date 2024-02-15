<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://kiwop.com
 * @since      1.0.0
 *
 * @package    Kiwop_Prisma_Recurses
 * @subpackage Kiwop_Prisma_Recurses/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Kiwop_Prisma_Recurses
 * @subpackage Kiwop_Prisma_Recurses/includes
 * @author     Antonio Sanchez <antonio@kiwop.com>
 */
class Kiwop_Prisma_Recurses {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Kiwop_Prisma_Recurses_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'KIWOP_PRISMA_RECURSES_VERSION' ) ) {
			$this->version = KIWOP_PRISMA_RECURSES_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'kiwop-prisma-recurses';

        
		$this->load_dependencies();
		$this->set_locale();
		
        
		$this->define_admin_hooks();
		$this->define_public_hooks();
        
        $this->create_custom_tables();
	}
    
    private function create_custom_tables()
    {   
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

   
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                
        
        $table_name = $wpdb->prefix .'kpr_scrappeddata'; 
        $sql0 = "SHOW COLUMNS FROM $table_name";

        try {
            $result = $wpdb->get_results($sql0);
            
            if (!empty($result) && $result[0]->Field == 'id') {
                //var_dump('Tabla ya creada'); die();
                return;
            }            
        } catch (Exception $e) {
            // do nothing, we continue and try to create table
        }
        
                    
        $sql1 = "CREATE TABLE " . $table_name . " (
            id int NOT NULL AUTO_INCREMENT,
            post_id bigint unsigned,
            attachment_id bigint unsigned,
            post_type varchar(50),
            `sistema` varchar(15),
            `url_import` varchar(190) NOT NULL,
            `url_descarga` varchar(190),
            `status` ENUM('draft', 'publish', 'ignore') NOT NULL,
            `source` ENUM('toolbox', 'merli', 'apliense','alexandria','rde','jclic') NOT NULL,
            `url_img` varchar(255),
            `title` varchar(150) NOT NULL,
            `description` text,
            `tipologias_json` text,
            `price` decimal(10,2),
            `extra_data_json` text,
            created_at timestamp,
            updated_at timestamp,
            CONSTRAINT unique_url_import UNIQUE (url_import),
            CONSTRAINT unique_post_id UNIQUE (post_id),
            PRIMARY KEY (id)
        ) ENGINE=MyISAM $charset_collate;";
            
        //echo $sql1; die();

        try {
            $res = dbDelta( $sql1 );         
            foreach ($res as $key => $val) {
                error_log("Key -> $key: " . $val . "\n");
            }
        } catch (Exception $e) {
            error_log("Error al crear tabla $table_name: " . $e->getMessage() . "\n");
            return ;
        }

    }

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Kiwop_Prisma_Recurses_Loader. Orchestrates the hooks of the plugin.
	 * - Kiwop_Prisma_Recurses_i18n. Defines internationalization functionality.
	 * - Kiwop_Prisma_Recurses_Admin. Defines all hooks for the admin area.
	 * - Kiwop_Prisma_Recurses_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-kiwop-prisma-recurses-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-kiwop-prisma-recurses-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-kiwop-prisma-recurses-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-kiwop-prisma-recurses-public.php';

		$this->loader = new Kiwop_Prisma_Recurses_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Kiwop_Prisma_Recurses_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Kiwop_Prisma_Recurses_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Kiwop_Prisma_Recurses_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Kiwop_Prisma_Recurses_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Kiwop_Prisma_Recurses_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
