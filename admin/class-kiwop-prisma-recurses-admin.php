<?php


use simplehtmldom\HtmlWeb;
use simplehtmldom\HtmlDocument;
use guzzlehttp\guzzle;
use ForceUTF8\Encoding;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://kiwop.com
 * @since      1.0.0
 *
 * @package    Kiwop_Prisma_Recurses
 * @subpackage Kiwop_Prisma_Recurses/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Kiwop_Prisma_Recurses
 * @subpackage Kiwop_Prisma_Recurses/admin
 * @author     Antonio Sanchez <antonio@kiwop.com>
 */
class Kiwop_Prisma_Recurses_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;
	private $wpdb;
	private $columns_def_scr;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
    public function __construct( $plugin_name, $version ) {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->plugin_name = $plugin_name;
		$this->version = $version;

        $this->columns_def_scr = self::getColumnsDefScrapping();

        

        add_action('admin_menu', array($this, 'admin_menu'));

        add_action('wp_ajax_find_resources', array($this, 'find_resources') );            
        add_action('wp_ajax_getStadisticsHome', array($this, 'getStadisticsHome') );            
        add_action('wp_ajax_getHTMLScrappings', array($this, 'getHTMLScrappings') );            
        add_action('wp_ajax_doSelectedAction', array($this, 'doSelectedAction') );            
        add_action('wp_ajax_createCategoriesFromToolBox', array($this, 'createCategoriesFromToolBox') );            



        add_action('before_delete_post', array($this,'kiwop_prisma_before_delete_post'));
	}


    function kiwop_prisma_before_delete_post($post_id) 
    {
        $tabla = $this->wpdb->prefix.'kpr_scrappeddata';
        $sql = ' update '.$tabla.' set post_id = null where post_id = %d ';
        $result = $this->wpdb->query($this->wpdb->prepare($sql, $post_id));
        if ($result === false) {
            throw new Exception('Error al actualizar '.$tabla.', no se pudo actualizar el post_id a null.');
        }
    }


    public function setCustomCookies($nombre_cookie, $valor_cookie) {
        // Define la duración de la cookie en segundos (aquí, 30 días)
        $duracion = 365 * DAY_IN_SECONDS;

        // Establece la cookie con los parámetros proporcionados
        setcookie($nombre_cookie, $valor_cookie, time() + $duracion, COOKIEPATH, COOKIE_DOMAIN);
    }


    
    // Hook para llamar a la función al inicializar WordPress

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Kiwop_Prisma_Recurses_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Kiwop_Prisma_Recurses_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
        $dir = plugin_dir_url( __FILE__ );
        $dir = trim($dir, 'admin/');
        $script_path = plugin_dir_path(__FILE__) . 'css/kiwop-prisma-recurses-admin.css';

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/kiwop-prisma-recurses-admin.css', array(), filemtime($script_path), 'all' );

        // Enqueue Font Awesome
        wp_enqueue_style('font-awesome',  $dir . '/vendor/fortawesome/font-awesome/css/all.min.css', array(), false, 'all');

        wp_enqueue_style('bootstrap', $dir . '/vendor/twbs/bootstrap/dist/css/bootstrap.min.css', array(), false, 'all');

        //wp_enqueue_style('jquery-ui', plugin_dir_url( __FILE__ ) . '/css/jquery-ui.min.css', array(), false, 'all');

    }

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

        $script_path = plugin_dir_path(__FILE__) . 'js/kiwop-prisma-recurses-admin.js';
        $dir = plugin_dir_url( __FILE__ );
        $dir = trim($dir, 'admin/');

        wp_enqueue_script('canvasjs', $dir .  '/node_modules/@canvasjs/charts/canvasjs.min.js', array(), '1.0', true);
        //wp_enqueue_script('js-cookie', $dir .  '/node_modules/js-cookie/dist/js.cookie.min', array(), '1.0', true);

              
        // Enqueue admin.js
		wp_enqueue_script( 
            $this->plugin_name, 
            plugin_dir_url( __FILE__ ) . 'js/kiwop-prisma-recurses-admin.js', 
            array( 'jquery','jquery-ui-core', 'jquery-ui-dialog' ), 
            filemtime($script_path), 
            false
        );


        wp_enqueue_script('bootstrap',  $dir . '/vendor/twbs/bootstrap/dist/js/bootstrap.min.js', array('jquery'), null, true);

        // link ajax calls with functions and vars por JS
        wp_localize_script(
            $this->plugin_name, 
            'kiwop_prisma_globals', 
            array(
                'ajax_url'  => admin_url('admin-ajax.php'),
                'admin_url' => admin_url( 'admin.php' ),
                
                // aqui todos los nonce del backend                
                'getStadisticsHome' => wp_create_nonce('getStadisticsHome_nonce' ),
                'find_resources' => wp_create_nonce('find_resources_nonce' ),
                'getHTMLScrappings' => wp_create_nonce('getHTMLScrappings_nonce' ),
                'doSelectedAction' => wp_create_nonce('doSelectedAction_nonce' ),
                'createCategoriesFromToolBox' => wp_create_nonce('createCategoriesFromToolBox_nonce' ),
                'loader' => plugin_dir_url("") . '/prisma-scrapper/admin/img/ajax-loader-mini.gif',
                'loader_xl' => plugin_dir_url("") . '/prisma-scrapper/admin/img/ajax-loader.gif',
      
            )
        );
	}

    
    public function admin_menu() {
        
       // echo "mierda"; die();
        
        add_menu_page(
            'Prisma scrapper by Kiwop',
            'Prisma scrapper',
            'manage_options',
            'kiwop-prisma-recursos',
            array($this, 'render_admin_page'),
            'dashicons-admin-generic', // Puedes cambiar el icono
            30 // Posición en el menú
        );
        
    }


    public function render_admin_page() {     
        $resultScrappings = $this->getHTMLScrappings(false);
        
        $post_types = get_post_types();

        // Excluye los tipos de contenido internos de WordPress
        $excluded_post_types = array(
            'post',
            'page', 
            'attachment', 
            'revision', 
            'nav_menu_item',
            'custom_css',
            'customize_changeset',
            'oembed_cache',
            'user_request',
            'wp_block',
            'wp_template',
            'wp_template_part',
            'wp_global_styles',
            'wp_navigation',
            'acf-taxonomy',
            'acf-post-type',
            'acf-ui-options-page',
            'acf-field-group',
            'acf-field',
            'wpcf7_contact_form',
            'astra-advanced-hook',

        );
        foreach ($post_types as $key => $value) {
            if (in_array($value, $excluded_post_types)) {
                unset($post_types[$key]);
            }
        }
        foreach ($post_types as $key => $value) {
            $post_types[$key] = $value;
        }       

        include_once('partials/kiwop-prisma-recurses-admin-display.php');
    }
    

    public function getStadisticsHome() 
    {
        check_ajax_referer( 'getStadisticsHome_nonce' );
          
        $response = [];

        ////////////////////////////////////////////////////////////////////////////

        $sql = "SELECT COUNT(ID) as total_by_source, `source` 
        from `".$this->wpdb->prefix."kpr_scrappeddata`
        group by `source`
        order by `source`";

        $data = $this->wpdb->get_results($sql);   
       
        $dataChartBySource = [];
        $total1 = 0;
        foreach ($data as $row) {
            $elem = [];
            $elem['label'] = $row->source;
            $elem['y'] = (int) $row->total_by_source;
            $total1 += $row->total_by_source;
            $dataChartBySource[] = $elem;
        }

        ////////////////////////////////////////////////////////////////////////////
        $sql = "SELECT COUNT(ID) as total, `post_type` 
        from `".$this->wpdb->prefix."kpr_scrappeddata`
        group by `post_type`
        order by `post_type`";

        $data = $this->wpdb->get_results($sql);   
       
        $dataChartByPostType = [];
        $total2 = 0;
        foreach ($data as $row) {
            $elem = [];
            $elem['label'] = is_null($row->post_type) ? "Sense asignar" : $row->post_type;
            $elem['y'] = (int) $row->total;
            $total2 += $row->total;
            $dataChartByPostType[] = $elem;
        }

        $response = json_encode( [
            'dataChartBySource' => [
                "data"  => $dataChartBySource,
                'title' => "Continguts per `source` (Total: $total1)",                
            ],
            'dataChartByPostType' => [
                "data" => $dataChartByPostType,
                'title' => "Continguts per `post_type` (Total: $total2)",
            ],  
        ]);

        echo $response;
        die();  

    }

    public function find_resources() 
    {
        set_time_limit(0); 

        check_ajax_referer( 'find_resources_nonce' );
        
        $response = [];
                
        try {

            $arr_sources = [
                'toolbox.mobileworldcapital.com',
                'merli.xtec.cat',
                'apliense.xtec.cat',
                'alexandria.xtec.cat',
                'repositori.educacio.gencat.cat',
                'clic.xtec.cat',
            ];

            $url = null;
            $source = $_POST['source'];
            # comprobamos si es una direccion de una ficha
            if ($source=='other') {
                $url = $_POST['other_source'];
                foreach($arr_sources as $part_source) {
                    if (strpos($url,$part_source) !== false) {
                        $source = $part_source;
                        break;
                    }

                }
            }

            //var_dump($source,$url); die();
            
            switch($source) {
                case 'toolbox.mobileworldcapital.com':
                    $res = $this->findInToolbox($url);
                    break;
                case 'merli.xtec.cat':
                    $res = $this->findInMerli($url);
                    break;
                case 'apliense.xtec.cat':
                    $res = $this->findInApliense($url);
                    break;
                case 'alexandria.xtec.cat':
                    $res = $this->findInAlexandria($url);
                    break;
                case 'repositori.educacio.gencat.cat':
                    $res = $this->findInRepositoriEducacio($url);
                    break;                
                case 'clic.xtec.cat':
                    $res = $this->findInJclic($url);
                    break;                
            }
            
        } catch (\Exception $e) {
            $response['error'] = true;
            $response['message'] = $e->getMessage();    
            echo json_encode($response);
            die();                
        }
        
        $response['error'] = false;
        $response['data'] = $res;
       
        $response = json_encode( $response );
        echo $response;
        die();    
    }

    
    /*

             ██  ██████ ██      ██  ██████ 
             ██ ██      ██      ██ ██      
             ██ ██      ██      ██ ██      
        ██   ██ ██      ██      ██ ██      
         █████   ██████ ███████ ██  ██████ 
                                                                                  
    */





    private function findInJclic($url_ficha = null) {
        set_time_limit(1500); // 25 minutos

        $error_messages = [];

        $counters = [
            'jclics_not_valids' => 0,
            'counter' => 0,
            'updates' => 0,
            'total_processed' => 0,
        ];

        $arr_projects = [];

        if ($url_ficha) {
            // https://clic.xtec.cat/projects/stem_en_femeni/project.json
            $arr = explode("/",$url_ficha);
            $path = $arr[count($arr)-2];
            $arr_projects[$path] = true;            
        } else {

            $source_projects_json = file_get_contents('https://clic.xtec.cat/projects/projects.json');
    
            $source_projects = json_decode($source_projects_json);
    
            
            foreach($source_projects as $key => $jclic) {
                if (is_numeric($jclic->path)) {
                    //var_dump($jclic); die();
                }
                $arr_projects[$jclic->path] = true;
            }       
            
            //$arr = array_keys($arr_projects);
            //file_put_contents('arr_paths_not_valid.txt',implode("\n",$arr),FILE_APPEND);
            //var_dump($arr_projects); die();
            
            $sql = '  SELECT url_import FROM '.$this->wpdb->prefix.'kpr_scrappeddata WHERE source = "jclic" ';
            $urls = $this->wpdb->get_results($sql);
            $arr_jclic_existentes = [];
            foreach($urls as $url) {
                $arr_jclic_existentes[$url->url_import] = true;
            }
            
            
            $updateMode = isset($_POST['updateMode']) ? $_POST['updateMode'] : false;
    
    
            $arr_jclics_to_update = [];
            $arr_jclics_to_insert = [];
            foreach($arr_projects as $path => $v) {
                if (array_key_exists($path,$arr_jclic_existentes)) {
                    $arr_jclics_to_update[$path] = true;
                } else {
                    $arr_jclics_to_insert[$path] = true;
                }
            }

        }

        $inicio_tiempo = microtime(true);

        
        /*
        var_dump(
            $updateMode,
            count($arr_jclics_to_insert),
            count($arr_jclics_to_update),
            count($arr_projects)); 
            die();
        */

        if ($updateMode == 'insert') {               
            $this->upsertJclics($arr_jclics_to_insert,$counters,$error_messages);                
        } else if ($updateMode == 'update') {
            $this->upsertJclics($arr_jclics_to_update,$counters,$error_messages);
        } else {             
            $this->upsertJclics($arr_projects,$counters,$error_messages);
        }

        $fin_tiempo = microtime(true);
        $tiempo_transcurrido = $fin_tiempo - $inicio_tiempo;

        $info = "S'han inserit %d noves jclics.
                    S'han actulitzat %d jclics.
                    S'han processat %d urls.
                    Urls de jclic not valids: %d.
                    Temps transcorregut: %d segons.";
                    

        $resumen = sprintf(
            $info, 
            $counters['counter'], 
            $counters['updates'],
            $counters['total_processed'],
            $counters['jclics_not_valids'],
            $tiempo_transcurrido 
        );

        
      
        

        $response['resumen'] = nl2br($resumen);
        $response['messages'] = $error_messages;            

        return $response;
    }
        
    private function upsertJclics($arr_jclics_to_insert,&$counters,&$error_messages)
    {
        //$max_import = 10;
        //$cont = 0;

        $path_not_valids = [];
        foreach($arr_jclics_to_insert as $path => $v ) {
            $fullurl = "https://clic.xtec.cat/projects/".$path."/project.json";
            $base_url = "https://clic.xtec.cat/projects/".$path."/";
            try {
                $jclic_json = file_get_contents($fullurl);
            } catch (\Exception $e) {
                $path_not_valids[] = $path;
                $counters['total_processed']++;
                $counters['jclics_not_valids']++;                
                continue;
            }
            if ($jclic_json!==false) {                
                $jclic = json_decode($jclic_json);
                $row = [];
                $extra_data_json = [];

                $areas = [];
                foreach($jclic->areas as $area) {
                    $areas[] = $area;
                }
                $levels = [];
                foreach($jclic->levels as $level) {
                    $levels[] = $level;
                }

                if (isset($jclic->author)) {
                    $extra_data_json['author'] = $jclic->author;
                }
                $extra_data_json['language'] = $jclic->languages->ca;
                $extra_data_json['areas'] = implode("<br />",$areas);
                if (isset($jclic->areaCodes)) {
                    $extra_data_json['areaCodes'] = $jclic->areaCodes;
                }
                $extra_data_json['levels'] = implode("<br />",$levels);
                
                if (isset($jclic->levelCodes)) {
                    $extra_data_json['levelCodes'] = $jclic->levelCodes;
                }
                $extra_data_json['license'] = $jclic->license->ca;
                $extra_data_json['date'] = $this->convertDate($jclic->date);
                $extra_data_json['mainFile'] = $jclic->mainFile;

                $row['title'] = $jclic->title;
                $row['description'] = $jclic->description->ca;
                $row['url_img'] = $base_url . $jclic->cover;
                $row['url_import'] = $path;
                $row['url_descarga'] = "https://clic.xtec.cat/projects/".$path."/jclic.js/index.html";
                $row['xtra-data-json'] = $extra_data_json;
                
                try {
                    $this->saveCommonResource('jclic',$counters,$error_messages,$row,'jclic');
                } catch (\Exception $e) {
                    $error_messages[] = $e->getMessage();
                }                
                                
            } else{
                $error_messages["Error al obtener el fichero json de jclic, ver log en paths_not_valid.txt"] = true;
            }

            $counters['total_processed']++;

            //if ($cont > $max_import) break;
            //$cont++;
        }
        $path_recurses_json = str_replace('admin/','',plugin_dir_path(__FILE__)) . 'paths_not_valid.txt';
        file_put_contents($path_recurses_json,implode("\n",$path_not_valids));
    }

    private function convertDate($date) {
        $arr = explode("/",$date);
        $year = (int) $arr[2];
        if ($year > 90) {
            $year = 1900 + $year;
        } else {
            $year = 2000 + $year;
        }
        return $year."-".$arr[1]."-".$arr[0];
    }



    /*

        ████████  ██████   ██████  ██      ██████   ██████  ██   ██ 
           ██    ██    ██ ██    ██ ██      ██   ██ ██    ██  ██ ██  
           ██    ██    ██ ██    ██ ██      ██████  ██    ██   ███   
           ██    ██    ██ ██    ██ ██      ██   ██ ██    ██  ██ ██  
           ██     ██████   ██████  ███████ ██████   ██████  ██   ██ 
                                                                    
    */

    
    private function findInToolbox($url_ficha = null) {
        
        $error_messages = [];

        $counters = [
            'apps' => 0,
            'updates' => 0,
            'counter' => 0,
        ];

        if ($url_ficha) {
            $counters['duplicados'] = 0;  
            $this->findInToolboxSO(null,null,$counters,$error_messages,$url_ficha);
            $info = "S'han inserit %d noves aplicacions.<br />
                     S'han actulitzat %d aplicacions.";
            $resumen = sprintf($info, $counters['counter'], $counters['updates']);                     
            

        } else {

    
            $urls = [
                'ios'       => "https://toolbox.mobileworldcapital.com/apps/ios/pagina:",
                'android'   => "https://toolbox.mobileworldcapital.com/apps/android/pagina:",
                'chrome'    => "https://toolbox.mobileworldcapital.com/apps/chrome/pagina:",
            ];
    
            foreach($urls as $SO => $url) {
                $counters['duplicados'] = 0;                
                $this->findInToolboxSO($SO,$url,$counters,$error_messages);           
            }
            
            $info = "S'han inserit %d noves aplicacions.
                     S'han actulitzat %d aplicacions.";
                     
            if ($counters['duplicados']) {
                $info .= "\nEl procés ha trobat %d aplicacions duplicades que no s'han inserit.";
            }
            $resumen = sprintf($info, $counters['counter'], $counters['updates'],$counters['duplicados']);

        }


        $response['resumen'] = nl2br($resumen);
        $response['messages'] = $error_messages;            

        return $response;
    }

    private function findInToolboxSO($SO=null,$url=null,&$counters=null,&$error_messages=null,$url_ficha = null)
    {
        # Toolbox pagina sus contenidos por ajax, pero vemos que esa paginacion por ajax 
        # es muy sencilla y podemos hacerla nosotros mismos mediante llamadas request GET
        
        # URL paginacion
        
        # URL apps
        $url_app = "https://toolbox.mobileworldcapital.com/app";
        $img_base_url = "https://toolbox.mobileworldcapital.com";

        $dir = plugin_dir_url( __FILE__ );
        $dir = trim($dir, 'admin/');
        $img_temp_dir = plugin_dir_path(__FILE__); //str_replace('admin','tmp_img',$img_temp_dir);
        $img_temp_dir = str_replace('admin/','',$img_temp_dir);

        $client = new \GuzzleHttp\Client();

        $apps = [];

       if (empty($url_ficha)) {

           for($i=0; $i<=99; $i++) {
               $url_pag = $url . $i;
               $res = $client->request('GET', $url_pag ,['verify' => false] );
               $body = $res->getBody()->getContents();
               
               if (strpos($body, "No s'han trobat registres") !== false) {
                   break;
               }
    
               $doc = new HtmlDocument($body);
               foreach ($doc->find('div.app-box') as $div) {
                   foreach ($div->find('div.text') as $h3) {
                       foreach ($h3->find('div.h3, a') as $a) {                                                
                           if ($a->hasAttribute('href') && strpos($a->getAttribute('href'), $url_app) !== false) {
                               $app = [];
                               $app['href'] = $a->getAttribute('href');
                               $apps[] = $app;
                           }
                       }
                   }
               }
           }
       } else {
           $app = [];
           $app['href'] = $url_ficha;
           $apps[] = $app;
       }

       //var_dump($apps); die();
        
        # ahora que tenemos todas las urls de las fichas de las apps, vamos a escrapear contenido
        $apps_data = [];
        foreach ($apps as $app) {
            $data = [];
            $res = $client->request('GET', $app['href'] ,['verify' => false] );
            $body = $res->getBody()->getContents();            
            $doc = new HtmlDocument($body);

            $data['url_import'] = $app['href'];
            # SO
            if (!$SO) {
                foreach ($doc->find('h3.page-nav-info') as $h3) {
                    foreach ($h3->find('a') as $a) {
                        $SO = explode(' ',strtolower($a->innertext))[0];
                    }
                }

            }

            # titulo e imagen
            foreach ($doc->find('#app_detail') as $div) {
                foreach ($div->find('img') as $img) {
                    if (is_object($img) && !isset($data['url_img'])) {
                        $data['url_img'] = $img_base_url . $img->getAttribute('src');
                    }
                }
                foreach ($div->find('span.app_name') as $span) {
                    foreach ($span->find('a') as $a) {
                        $data['title'] = trim($a->innertext);
                    }
                }
                foreach ($div->find('span.store-button') as $span) {
                    foreach ($span->find('a') as $a) {
                        $data['url_descarga'] = $a->getAttribute('href');
                    }
                }
                foreach ($div->find('span.app_price') as $span) {
                    $content =  $span->innertext;
                    $content =  trim(strip_tags($content));
                    if ($content == 'gratuita') {
                        $data['price'] = 0;
                    } else {
                        $data['price'] = (float) str_replace(',','.',$content);
                    }                    
                }
            }

            # descripcion
            foreach ($doc->find('#brief, p.lead') as $elem) {
                $data['description'] = $elem->innertext;
            }
            $data['xtra-data-json'] = [];
            $data['xtra-data-json']['app-education-level'] = [];
            $data['xtra-data-json']['app-area'] = [];
            $data['xtra-data-json']['app-lang'] = [];
            $data['xtra-data-json']['app-competence'] = [];
            $data['xtra-data-json']['app-resource-type'] = [];
            $data['xtra-data-json']['app-accesibility'] = [];
            $data['xtra-data-json']['app-privacy'] = [];
            $data['xtra-data-json']['app-ads'] = [];

            foreach ($doc->find('#caract') as $div) {                
                foreach ($div->find('div.span3') as $child) {
                    foreach ($child->find('h5, a') as $tipo) {
                        $tipologia = $tipo->getAttribute('name');
                        if (isset($data['xtra-data-json'][$tipologia])) {
                            $this->setTagsToolbox($data,$tipologia,$child);                             
                        }
                    }
                }
            }
            
            $data['xtra-data-json']['edat'] = [];
            foreach ($doc->find('#caract') as $div) {       
                foreach ($div->find('span.brief_edat') as $span) {                
                    foreach ($span->find('span') as $edat) {                
                        foreach ($edat->find('strong') as $s) {                
                            $data['xtra-data-json']['edat'][] = $s->innertext;
                        }         
                    }         
                }         
            }         

            $apps_data[] = $data;
        }

        $messages = [];

        foreach($apps_data as $row) {
            $this->saveCommonResource('toolbox',$counters,$messages,$row);                        
        }
        
        
        foreach($messages as $msg => $v) {
            if (is_numeric($msg)) {
                $error_messages[] = $v;
            } else {
                if (strpos($msg,'Duplicate entry')===false) {
                    $error_messages[] = $msg;
                } else {
                    $counters['duplicados']++;
                }
            }
        }
    }


    private function setTagsToolbox(&$data,$tipologia,$child) {
        foreach ($child->find('a.tag') as $tag) {
            $data['xtra-data-json'][$tipologia][] = $tag->innertext;
        }
    }

    /*

         █████  ██      ███████ ██   ██  █████  ███    ██ ██████  ██████  ██  █████  
        ██   ██ ██      ██       ██ ██  ██   ██ ████   ██ ██   ██ ██   ██ ██ ██   ██ 
        ███████ ██      █████     ███   ███████ ██ ██  ██ ██   ██ ██████  ██ ███████ 
        ██   ██ ██      ██       ██ ██  ██   ██ ██  ██ ██ ██   ██ ██   ██ ██ ██   ██ 
        ██   ██ ███████ ███████ ██   ██ ██   ██ ██   ████ ██████  ██   ██ ██ ██   ██ 
                                                                                                                                                                                                                                                                                                                                                         
    */
    private function findInAlexandria($url_ficha = null)
    {
        
        if ($url_ficha) {
            $data = [
                'updates' => 0,
                'counter' => 0,
            ];
            $error_messages = [];
            $this->findByTypeInAlexandria(null,$data,$error_messages,$url_ficha);
            $info = sprintf("S'han inserit %d noves registres.
                S'han actulitzat %d registres | URLS procesats: %d urls ", 
                $data['counter'], 
                $data['updates'],
                $data['total_urls']
            );

        } else {
                
            $urls = [
                'scorm'    => [
                    'updates' => 0,
                    'counter' => 0,
                    'url' => "https://alexandria.xtec.cat/mod/data/view.php?d=77",
                ],            
                'pdis'      => [
                    'updates' => 0,
                    'counter' => 0,
                    'url' => "https://alexandria.xtec.cat/mod/data/view.php?d=4",
                ],
                'curs_moodle'   => [
                    'updates' => 0,
                    'counter' => 0,
                    'url' => "https://alexandria.xtec.cat/mod/data/view.php?d=2",
                ],
            ];

            $error_messages = [];
            foreach($urls as $post_type => $data) {
                $this->findByTypeInAlexandria($post_type,$data,$error_messages);           
                $urls[$post_type]['resumen'] = sprintf("S'han inserit %d noves registres.
                    S'han actulitzat %d registres | URLS procesats: %d urls ", 
                    $data['counter'], 
                    $data['updates'],
                    $data['total_urls']
                );
            }

            $info = '';
            foreach($urls as $post_type => $data) {
                $info .= "Post type: <strong>$post_type</strong>: " . $data['resumen'] . "<br />";
            }                 
            
        }

        //var_dump($urls); die();
        

        $response['resumen'] = $info;
        $response['messages'] = $error_messages;   
        
        //var_dump($response); die();

        return $response;
    
    }

    private function findByTypeInAlexandria($post_type=null,&$data,&$messages,$url_ficha = null)
    {
    
        $url = $data['url'];
       


        # img temp dir
        $dir = plugin_dir_url( __FILE__ );
        $dir = trim($dir, 'admin/');
        $img_temp_dir = plugin_dir_path(__FILE__); //str_replace('admin','tmp_img',$img_temp_dir);
        $img_temp_dir = str_replace('admin/','',$img_temp_dir);

        $client = new \GuzzleHttp\Client();

        if (empty($url_ficha)) {

            #obtencion de total de paginas de cada seccion
            $res = $client->request('GET', $url ,['verify' => false] );
            $body = $res->getBody()->getContents();
    
            // Detectar la codificación actual
            $encoding = mb_detect_encoding($body);
    
            // Convertir a UTF-8 si la detección de codificación no es UTF-8
            if ($encoding !== 'UTF-8') {
                $body = mb_convert_encoding($body, 'UTF-8', $encoding);
            }        
    
            $doc = new HtmlDocument($body);
            $maxpage = 1;
            foreach ($doc->find('nav.pagination') as $nav) {
                foreach ($nav->find('ul') as $ul) {
                    foreach ($ul->find('li.page-item') as $li) {                                                
                        if ($li->hasAttribute('data-page-number') ) {                        
                            $new_maxpage = $li->getAttribute('data-page-number');                        
                            if ($maxpage < $new_maxpage) {
                                $maxpage = $new_maxpage;
                            }
                        }
                    }
                    if ($maxpage > 1) break;
                }
                if ($maxpage > 1) break;
            }        
    
            if ($maxpage == 1) $maxpage = 99;
    
            $recurses = [];
    
            $total_urls = 0;
            //for($i=1; $i<=1; $i++) {
            for($i=1; $i<=$maxpage; $i++) {
                 
                $add_url = "&advanced=0&paging&page=";        
            
                $url_pag = $url . $add_url . $i;
                $res = $client->request('GET', $url_pag ,['verify' => false] );
                $body = $res->getBody()->getContents();
                
                // Detectar la codificación actual
                $encoding = mb_detect_encoding($body);
    
                // Convertir a UTF-8 si la detección de codificación no es UTF-8
                if ($encoding !== 'UTF-8') {
                    $body = mb_convert_encoding($body, 'UTF-8', $encoding);
                }        
    
    
                if (strpos($body, "No s'han trobat registres") !== false) {
                    break;
                }
    
                $doc = new HtmlDocument($body);
                foreach ($doc->find('div.database-entry') as $div) {
                    foreach ($div->find('div.subject') as $sub) {
                        foreach ($sub->find('a') as $a) {                                                
                            if ($a->hasAttribute('href')) {
                                $recurs = [];
                                $recurs['href'] = $a->getAttribute('href');
                                $recurses[] = $recurs;
                                $total_urls++;
                            }
                        }
                    }
                }
            }
        } else {
            $recurs = [];
            $recurs['href'] = $url_ficha;
            $recurses[] = $recurs;
            $total_urls = 1;   
        }


        //var_dump($recurses); die();

        $data['total_urls'] = $total_urls;

        $arr_recurses = [];
        foreach ($recurses as $recurs) {
            $row = [];
            $res = $client->request('GET', $recurs['href'] ,['verify' => false] );
            $body = $res->getBody()->getContents();    
            
            // Detectar la codificación actual
            $encoding = mb_detect_encoding($body);

            // Convertir a UTF-8 si la detección de codificación no es UTF-8
            if ($encoding !== 'UTF-8') {
                $body = mb_convert_encoding($body, 'UTF-8', $encoding);
            }    

            $doc = new HtmlDocument($body);

            $row['url_import'] = $recurs['href'];
            # titulo e imagen
            foreach ($doc->find('div.database-entry-info') as $div) {
                foreach ($div->find('h3') as $h3) {
                    $row['title'] = trim($h3->innertext);
                }

                $row['xtra-data-json'] = [];

                foreach ($div->find('tbody') as $tbody) {
                    foreach ($tbody->find('tr') as $tr) {
                        $titulo_tr = '';
                        if (!empty($tr->firstChild())) {
                            $titulo_tr =  trim(strip_tags($tr->firstChild()->innertext));
                        }            
                        if ($titulo_tr == 'Descripció:') {            
                            $content = $tr->children(1);
                            if (!empty($content)) {
                                $descripcion = strip_tags($content,["<ul>","<b>","<p>","<li>","<strong>","<br>",'<a>']);
                                
                                $row['description'] = $descripcion;

                                foreach ($content->find('a') as $a) {
                                    foreach ($a->find('img') as $img) {
                                        //var_dump($img->getAttribute('src')); die();
                                        if (is_object($img) && !isset($row['url_img'])) {
                                            $row['url_img'] = $img->getAttribute('src');
                                        }
                                    }    
                                }    
                            }
                        } else {
                            $multivalues = [
                                "Nivell/s educatiu/s:",
                                "Àmbit/s competencial/s:",
                                "Àrea curricular:",
                                "Idioma/es:",
                            ];
                            
                            if (in_array($titulo_tr,$multivalues)) {
                                $row['xtra-data-json'][$titulo_tr] = [];
                                $content = $tr->children(1);
                                if (!empty($content)) {
                                    $arr_descripcion = explode("<br>",$content->innertext);
                                    foreach ($arr_descripcion as $elem) {
                                        $elem = trim($elem);
                                        $row['xtra-data-json'][$titulo_tr][] = $arr_descripcion;        
                                    }                                    
                                }                                
                            } else {
                                $content = $tr->children(1);
                                if (!empty($content)) {
                                    $row['xtra-data-json'][$titulo_tr] = trim(strip_tags($content->innertext,['<a>']));
                                }
                            }
                        }
                    }
                }


            }
            

            $arr_recurses[] = $row;
           
        }

        //var_dump($arr_recurses); die();

        $messages = [];

        foreach($arr_recurses as $row) {
            $this->saveCommonResource('alexandria',$data,$messages,$row);           
        }
        
        
        
    }
    

    /*



        ██████  ███████ ██████   ██████  ███████ ██ ████████  ██████  ██████  ██ 
        ██   ██ ██      ██   ██ ██    ██ ██      ██    ██    ██    ██ ██   ██ ██ 
        ██████  █████   ██████  ██    ██ ███████ ██    ██    ██    ██ ██████  ██ 
        ██   ██ ██      ██      ██    ██      ██ ██    ██    ██    ██ ██   ██ ██ 
        ██   ██ ███████ ██       ██████  ███████ ██    ██     ██████  ██   ██ ██ 
                                                                                 
                                                                                 
        ██████      ███████ ██████  ██    ██  ██████  █████   ██████ ██  ██████  
        ██   ██     ██      ██   ██ ██    ██ ██      ██   ██ ██      ██ ██    ██ 
        ██   ██     █████   ██   ██ ██    ██ ██      ███████ ██      ██ ██    ██ 
        ██   ██     ██      ██   ██ ██    ██ ██      ██   ██ ██      ██ ██    ██ 
        ██████      ███████ ██████   ██████   ██████ ██   ██  ██████ ██  ██████  
                                                                                                                                                                  

  
    */


    private function findInRepositoriEducacio($url_ficha = null)
    {
        $ambits = "https://repositori.educacio.gencat.cat/community-list";
  
        if ($url_ficha) {
            $data = [
                'updates' => 0,
                'counter' => 0,
            ];
            $error_messages = [];
            $this->findInRDE(null,$data,$error_messages,$url_ficha);
            $info = sprintf("S'han inserit %d noves registres.
                S'han actulitzat %d registres | URLS procesats: %d urls ", 
                $data['counter'], 
                $data['updates'],
                $data['total_urls']
            );

        } else {
                
            $ambits = "https://repositori.educacio.gencat.cat/community-list";

            $data = [
                'updates' => 0,
                'counter' => 0,
            ];

            $error_messages = [];
            $this->findInRDE($ambits,$data,$error_messages);           
            $data['resumen'] = sprintf("S'han inserit %d noves registres.
                S'han actulitzat %d registres | URLS procesats: %d urls ", 
                $data['counter'], 
                $data['updates'],
                $data['total_urls']
            );            

            $info = $data['resumen'] . "<br />";                                         
        }

        //var_dump($urls); die();
        

        $response['resumen'] = $info;
        $response['messages'] = $error_messages;   
        
        //var_dump($response); die();

        return $response;        
    }

    
    private function findInRDE($ambito_url=null,&$data,&$messages,$url_ficha = null)
    {
            
       
        # img temp dir
        $dir = plugin_dir_url( __FILE__ );
        $dir = trim($dir, 'admin/');
        $img_temp_dir = plugin_dir_path(__FILE__); //str_replace('admin','tmp_img',$img_temp_dir);
        $img_temp_dir = str_replace('admin/','',$img_temp_dir);

        $client = new \GuzzleHttp\Client();

        if (empty($url_ficha)) {

            #obtencion de la pagina de las colecciones
            $res = $client->request('GET', $ambito_url ,['verify' => false] );
            $body = $res->getBody()->getContents();  
    
            $doc = new HtmlDocument($body);
            $maxpage = 1;

            $ambits = [];
            $arr_colecciones = [];

            foreach ($doc->find('#aspect_artifactbrowser_CommunityBrowser_referenceSet_community-browser') as $div) {
                foreach ($div->find('div.community-browser-row') as $div_title) {
                    foreach ($div_title->find('div.col-sm-11') as $subdiv) {   
                        //var_dump($subdiv); die();
                        foreach ($subdiv->find('a') as $a) {                                                
                            $title = $a->innertext;
                            $ambits[] = $title;
                        }
                    }
                }
                //var_dump($ambits); die();
                $i=0;
                foreach ($div->find('div.sub-tree-wrapper') as $subtree) {
                    $title = $ambits[$i];
                    $coleccion = [];
                    foreach ($subtree->find('div.row') as $row) {
                        foreach ($row->find('div.col-xs-offset-2') as $linkdiv) {
                            $elements = strip_tags($linkdiv->innertext);
                            $elements = explode("[",$elements)[1];
                            $elements = (int) explode("]",$elements)[0];
                            foreach ($linkdiv->find('a') as $a) {
                                $datacol = [
                                    'title' => $a->innertext,
                                    'href' => 'https://repositori.educacio.gencat.cat' . $a->getAttribute('href'),
                                    'elements' => $elements,
                                    'urls' => [],
                                ];
                                $coleccion[] = $datacol;
                            }
                        
                        }
                    }
                    $arr_colecciones[$title] = $coleccion;
                    $i++;
                }
                break;
            }        
            
            $index = 0;

            foreach($arr_colecciones as $ambit => $coleccionesAmbit) {
                
                foreach($coleccionesAmbit as $index => $coleccion) {

                    $urls = [];
                    # hacemos consultas paginadas, ya tenemos cuantos elementos hay y podemos calcular
                    # cuantas paginas pueden haber
                    $offset = 0;                
                    $ipp = 20;        
                    
                    
                    $paginas = floor( intval($coleccion['elements']) / $ipp);
                    
                    for($i=0; $i<=$paginas; $i++) {
                        
                        $url = $coleccion['href'] . '/recent-submissions?offset='.$offset;
                        $res = $client->request('GET', $url ,['verify' => false] );
                    
                        $status_code = $res->getStatusCode();
                        if ($status_code >= 200 && $status_code < 300) {
                            $body = $res->getBody()->getContents();  
                        } else {
                            $messages[] = "Error en la peticion a la url: $url";
                            continue;
                        }

                    
                        $doc = new HtmlDocument($body);
                        foreach ($doc->find('#aspect_discovery_recentSubmissions_RecentSubmissionTransformer_div_recent-submissions') as $div) {
                            foreach ($div->find('ul') as $ul) {
                                foreach ($ul->find('li') as $li) {   
                                    foreach ($li->find('div') as $subdiv) {                                                
                                        foreach ($subdiv->find('h4') as $h4) {                                                
                                            foreach ($h4->find('a') as $a) {   
                                                $urls[] = $a->getAttribute('href');                                                
                                            }
                                        }
                                    }
                                }
                            }                        
                        }

                        $offset += $ipp;
                    }   
                    
                    //$coleccion['urls'] = $urls;
                    $arr_colecciones[$ambit][$index]['urls'] = $urls;
                }
                break;
            }

            //var_dump("mierda",$arr_colecciones); die();
    
            $recurses = [];
    
            $total_urls = 0;
            //for($i=1; $i<=1; $i++) {
            //$arr_colecciones[$ambit][$index]['urls'] = $urls;

            foreach($arr_colecciones as $ambit => $coleccionesAmbit) {
                foreach($coleccionesAmbit as $index => $coleccion) {
                    foreach($coleccion['urls'] as $url) {
                        $recurs = [];
                        $recurs['href'] = $url;
                        $recurs['ambit'] = $ambit;
                        $recurs['coleccion'] = $coleccion['title'];
                        $recurses[] = $recurs;
                        $total_urls++;
                    }
                }
            }

        } else {
            $recurs = [];
            $recurs['href'] = $url_ficha;
            $recurses[] = $recurs;
            $total_urls = 1;   
        }

        
        //var_dump("RECURSOS!!!!",$recurses); die();

        $data['total_urls'] = $total_urls;

        $arr_recurses = [];
        foreach ($recurses as $recurs) {
            $row = [];
            $res = $client->request('GET', $recurs['href'] ,['verify' => false] );
            $body = $res->getBody()->getContents();    
            
            $doc = new HtmlDocument($body);

            $row['url_import'] = $recurs['href'];
            # titulo e imagen
            foreach ($doc->find('div.database-entry-info') as $div) {
                foreach ($div->find('h3') as $h3) {
                    $row['title'] = trim($h3->innertext);
                }

                $row['xtra-data-json'] = [];

                foreach ($div->find('tbody') as $tbody) {
                    foreach ($tbody->find('tr') as $tr) {
                        $titulo_tr = '';
                        if (!empty($tr->firstChild())) {
                            $titulo_tr =  trim(strip_tags($tr->firstChild()->innertext));
                        }            
                        if ($titulo_tr == 'Descripció:') {            
                            $content = $tr->children(1);
                            if (!empty($content)) {
                                $descripcion = strip_tags($content,["<ul>","<b>","<p>","<li>","<strong>","<br>",'<a>']);
                                
                                $row['description'] = $descripcion;

                                foreach ($content->find('a') as $a) {
                                    foreach ($a->find('img') as $img) {
                                        //var_dump($img->getAttribute('src')); die();
                                        if (is_object($img) && !isset($row['url_img'])) {
                                            $row['url_img'] = $img->getAttribute('src');
                                        }
                                    }    
                                }    
                            }
                        } else {
                            $multivalues = [
                                "Nivell/s educatiu/s:",
                                "Àmbit/s competencial/s:",
                                "Àrea curricular:",
                                "Idioma/es:",
                            ];
                            
                            if (in_array($titulo_tr,$multivalues)) {
                                $row['xtra-data-json'][$titulo_tr] = [];
                                $content = $tr->children(1);
                                if (!empty($content)) {
                                    $arr_descripcion = explode("<br>",$content->innertext);
                                    foreach ($arr_descripcion as $elem) {
                                        $elem = trim($elem);
                                        $row['xtra-data-json'][$titulo_tr][] = $arr_descripcion;        
                                    }                                    
                                }                                
                            } else {
                                $content = $tr->children(1);
                                if (!empty($content)) {
                                    $row['xtra-data-json'][$titulo_tr] = trim(strip_tags($content->innertext,['<a>']));
                                }
                            }
                        }
                    }
                }


            }
            

            $arr_recurses[] = $row;
           
        }

        //var_dump($arr_recurses); die();

        $messages = [];

        foreach($arr_recurses as $row) {
            $this->saveCommonResource('rde',$data,$messages,$row);            
        }
        
        
        
    }
    
    /*


        ███    ███ ███████ ██████  ██      ██ 
        ████  ████ ██      ██   ██ ██      ██ 
        ██ ████ ██ █████   ██████  ██      ██ 
        ██  ██  ██ ██      ██   ██ ██      ██ 
        ██      ██ ███████ ██   ██ ███████ ██ 
                                                                                          
    */

    /*

    Log Errores
    [01-Jan-2024 11:24:49 UTC] PHP Warning:  file_get_contents(): SSL: Se ha forzado la interrupción de una conexión existente por el host remoto 
    [01-Jan-2024 13:08:41 UTC] PHP Fatal error:  Allowed memory size of 134217728 bytes exhausted (tried to allocate 40960 bytes) in C:\Users\fuent\Documents\kiwop\Prisma\wordpress\wp-content\plugins\kiwop-prisma-recurses\vendor\simplehtmldom\simplehtmldom\HtmlDocument.php on line 931
    [01-Jan-2024 13:25:07 UTC] PHP Fatal error:  Allowed memory size of 134217728 bytes exhausted (tried to allocate 49152 bytes) in C:\Users\fuent\Documents\kiwop\Prisma\wordpress\wp-content\plugins\kiwop-prisma-recurses\vendor\guzzlehttp\psr7\src\Utils.php on line 418
    
    cURL error 28: Failed to connect to merli.xtec.cat port 443: Timed out (see https://curl.haxx.se/libcurl/c/libcurl-errors.html) for https://merli.xtec.cat/merli/cerca/fitxaRecurs.jsp?idRecurs=/10768&sheetId=null&nomUsuari=null&inxtec=0
    "cURL error 28: Failed to connect to merli.xtec.cat port 443: 
    Timed out (see https://curl.haxx.se/libcurl/c/libcurl-errors.html) 
    for https://merli.xtec.cat/merli/ServletCerca"
    
    cURL error 28: Failed to connect to merli.xtec.cat port 443: 
    Timed out (see https://curl.haxx.se/libcurl/c/libcurl-errors.html) 
    for https://merli.xtec.cat/merli/cerca/fitxaRecurs.jsp?idRecurs=/7434&sheetId=null&nomUsuari=null&inxtec=0
    */

    private function findInMerli($url_ficha)
    {

        $error_messages = [];
        $info = '';

        if ($url_ficha) {
            $data = [
                'counter' => 0,
                'updates' => 0,
                'total_urls' => 1,
                'url_duplicadas' => 0,
            ];
            $this->scrapInMerli(null,$data,$error_messages,$url_ficha);
            //var_dump($data['total_urls']);    die();

            $info = sprintf("S'han inserit %d noves registres.
                S'han actulitzat %d registres | URLS procesats: %d urls ", 
                $data['counter'], 
                $data['updates'],
                $data['total_urls']
            );

        } else {
                
            $url_cercador = "https://merli.xtec.cat/merli/ServletCerca";

            $data = [
                'counter' => 0,
                'updates' => 0,
                'total_urls' => 0,
                'total_requests' => 0,
                'url_duplicadas' => 0,
            ];
        
            $this->scrapInMerli($url_cercador,$data,$error_messages);    

            $data['resumen'] = sprintf("S'han inserit %d noves registres.
                S'han actulitzat %d registres | URLS procesats: %d urls | Requests done: %d requests | Duplicated URLs: %d urls ", 
                $data['counter'], 
                $data['updates'],
                $data['total_urls'],
                $data['total_requests'],
                $data['url_duplicadas']
            );            

            $info = $data['resumen'] . "<br />";                                         
        }

        //var_dump($urls); die();
        

        $response['resumen'] = $info;
        $response['messages'] = $error_messages;   
        
        //var_dump($response); die();

        return $response;              
    }
    


    private function scrapInMerli($url_cercador=null,&$data,&$messages,$url_ficha = null)
    {
            
               
        # img temp dir
        $dir = plugin_dir_url( __FILE__ );
        $dir = trim($dir, 'admin/');
        $img_temp_dir = plugin_dir_path(__FILE__); //str_replace('admin','tmp_img',$img_temp_dir);
        $img_temp_dir = str_replace('admin/','',$img_temp_dir);

        $overwrite_file = false;
        

        if (empty($url_ficha)) {

            # init 
            $recurses = [];
            
            $max_months_cached_ = 3; // 1 messes
            
            
            ########################################
            ####### cacheo #########################
            ########################################

            # merlin_recurses.json -> buscamos la ultima busqueda de paginas realizada, si tiene mas de X meses se vuelve a hacer, 
            # sino pasamos directamente a la carga de recursos con 

            $path_recurses_json = str_replace('admin/','',plugin_dir_path(__FILE__)) . 'merlin_recurses.json';

            if (file_exists($path_recurses_json)) {
                $fechaCreacion = filectime($path_recurses_json);
                $fechaActual = new DateTime();
                $fechaCreacionObj = new DateTime();
                $fechaCreacionObj->setTimestamp($fechaCreacion);
                
                $diferencia = $fechaActual->diff($fechaCreacionObj);
                
                if ($diferencia->m > $max_months_cached_ || ($diferencia->m == $max_months_cached_ && $diferencia->d > 0)) {          
                    // refrescar el json
                    $overwrite_file = true;
                } else {
                    $recurses = json_decode(file_get_contents($path_recurses_json),true);
                }
                
                
            } 
            //var_dump($recurses,$path_recurses_json,file_exists($path_recurses_json)); die();
            ####### end cache #########################
            
            if (!count($recurses)) {
                                
                $recurses = $this->prepareAndSearchMerli($url_cercador,$data,$messages);
                
                if ($overwrite_file) {
                    file_put_contents($path_recurses_json,json_encode($recurses));
                }
            }
            
            $total_urls = 0;
            foreach ($recurses as $recurs) {
                $total_urls += count($recurs['urls']);
            }
            $data['total_urls'] = $total_urls;
        } else {
            $recurs = [];
            $recurs['urls'] = [];
            $recurs['urls'][] = $url_ficha;
            $recurses[] = $recurs;
        }

        $this->getAndUpsertRemoteResourcesMerli($recurses,$data,$messages); 
    }
    
    private function getAndUpsertRemoteResourcesMerli($recurses,&$data,&$messages)
    {
        
        $sql =  "
            SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(url_import, '/', -1), '&', 1) AS id_recurs 
            FROM wp_kpr_scrappeddata 
            WHERE source = 'merli'
            AND updated_at > '2024-01-01 00:00:00' 
            ORDER BY updated_at desc            
        ";
        
        
        $qdata = $this->wpdb->get_results( $sql );
        $arr_cache_updates = [];
        foreach ($qdata as $row) {
            $arr_cache_updates[$row->id_recurs] = true;
        }
        
        
        $url_base = 'https://merli.xtec.cat/merli/cerca/fitxaRecurs.jsp';

        $max_import = 0;
        $arr_recurses = [];

        foreach ($recurses as $recurs) {

            // Tamaño del chunk (número de elementos por chunk)
            $tamano_chunk = 100; 

            // Dividir el array en chunks
            $chunks = array_chunk($recurs['urls'], $tamano_chunk);

            // Iterar sobre cada chunk
            foreach ($chunks as $chunk) {


                foreach ($chunk as $idRecurs) {   

                    # si se actualizó hace poco pasamos a la siguiente
                    if ( array_key_exists(str_replace('/','',$idRecurs),$arr_cache_updates)===true ) {
                        continue;
                    }
                    
                    $row = [];
                    $xtradatajson = [];
    
                    if (strpos($idRecurs, 'https://') === false) {
                        $url = $url_base . "?idRecurs=" . $idRecurs;
                        if (strpos($url, '&sheetId=null&nomUsuari=null&inxtec=0') === false) {
                            $url = $url . '&sheetId=null&nomUsuari=null&inxtec=0';
                        }                    
                    } else {
                        if (strpos($idRecurs, $url_base) === false) {
                            $messages["Error en la url:"] = true;
                            continue;
                        }
                        $url = $idRecurs;
                    }
    
    
                    $data['total_requests']++;
        
                    $client = new \GuzzleHttp\Client();
        
                    try {
                        $res = $client->request('GET', $url ,['verify' => false] );
                    } catch (\Exception $e) {
                        $messages[$e->getMessage()] = true;
                        continue;
                    }


                    $body = $res->getBody()->getContents(); 
                    
                    # libero memoria
                    unset($res);
                    unset($client);
                    
                    $doc = new HtmlDocument($body);
                    
                    $row['url_import'] = $url;
                    
    
                    foreach ($doc->find('#fitxa') as $fitxa) {
                        foreach ($fitxa->find('div') as $div) {
                            foreach ($div->find('h1') as $h1) {                               
                                $row['title'] = strip_tags($h1->innertext);
                                break;
                            }
                        }
                    }
    
                   // var_dump($row['title']); die();
                    
    
                    foreach ($doc->find('#cos_blau') as $div) {
                        foreach ($div->find('dl') as $dl) {
                            $descripcion = '';
                            foreach ($dl->find('p') as $p) {
                                if ( strlen(trim($p->innertext)) > 0 ) {                                    
                                    $descripcion .= trim($p->innertext) . "<br />";
                                } else {
                                    break;
                                }
                            }   
                            $row['description'] = $descripcion;
                            foreach ($dl->find('dt') as $dt) {
                                $xtradatajson[trim($dt->innertext)] = $dt->nextSibling()->innertext;
                            }
                        }
                    }    
                    
                    
                    
                    
                    //$xtradatajson['area'] = $recurs['area'];
                    $xtradatajson['nivel'] = $recurs['nivel'];
                    $xtradatajson['tipo_recurso'] = $recurs['tipo_recurso'];
                    $row['xtra-data-json'] = $xtradatajson;
                    $row['url_img'] = null;
                    
                    //var_dump($row); die();
                    if (!isset($row['title']) || !strlen(trim($row['title']))) {
                        if (!isset($messages["Title not reached in URL"])) {
                            $messages["Title not reached in URL"] = 0;
                        }
                        $messages["Title not reached in URL"]++;
                        continue;
                    }
    
                    $max_import++;
                    //$arr_recurses[] = $row;
                    $this->saveCommonResource('merli',$data,$messages,$row);
                    
                    unset($doc);                    
                }                
            }            

           
        }
       
        
    }

    private function prepareAndSearchMerli($url_cercador,&$data,&$messages)
    {

        $nivell_educatiu = [
            '2215' => 'Educació infantil',
            '2219' => 'Educació primària',
            '2221' => 'Educació secundària obligatòria',
            '2234' => 'Batxillerat',
            '9337' => 'FP específica de grau superior',
            '9698' => 'FP específica de grau mitjà',
            '37817' => 'Docents',
            '39645' => 'Ensenyament d\'idiomes',
            '41509' => 'Ensenyaments artístics',
            '43780' => 'Ensenyaments esportius',
            '46122' => 'Educació d\'adults',
            '46481' => 'Programes de qualificació professional inicial(PQPI)',
        ];
        
        
        $recursos_en_linea = [
            "Interactiu" => 7,
            "Aplicació" => 6,
            "Imatge" => 5,
            "Àudio" => 4,
            "Vídeo" => 3,
            "Document" => 2,
            "Pàgina web" => 1,
        ];

        $recursos_fisicos = [
            "Vídeo" => 9,
            "Escrit" => 8,
            "Maleta pedagògica" => 14,
            "Recurs electrònic" => 13,
            "Objecte" => 12,
            "Àudio" => 11,
            "Material visual" => 10,
        ];        

        $recurses = [];

        foreach($nivell_educatiu as $id_nivel => $title_nivel) {
            //foreach($area_curricular as $id_area => $title_area) {
            foreach($recursos_en_linea as $title_recurso_en_linea => $id_tipo_recurs ) {
                $tipo_recurs = 'en_linea';                       
                $datacol = [
                    'tipo_recurso' => $tipo_recurs,
                    'title_tipo_recurso' => $title_recurso_en_linea,
                    'tipo_recurso_id' => $id_tipo_recurs,
                    'nivel' => $title_nivel,
                    'nivel_id' => $id_nivel,
                    //'area' => $title_area,
                    //'area_id' => $id_area,
                    'urls' => [],
                ];
                $this->buscaRecursosMerli($datacol,$url_cercador,$data,$messages);
                // Filtrar el array por el valor deseado


                $recurses[] = $datacol;
                //break;
            }
            //break;
            foreach($recursos_fisicos as $title_recurso_fisico => $id_tipo_recurs ) {
                $tipo_recurs = 'fisico';
                $datacol = [
                    'cat_tipo_recurso' => $tipo_recurs,
                    'tipo_recurso' => $title_recurso_fisico,
                    'tipo_recurso_id' => $id_tipo_recurs,
                    'nivel' => $title_nivel,
                    'nivel_id' => $id_nivel,
                    //'area' => $title_area,
                    //'area_id' => $id_area,
                    'urls' => [],
                ];
                $this->buscaRecursosMerli($datacol,$url_cercador,$data,$messages);
                $recurses[] = $datacol;
            }                                    
            //}
            //break;
        }
        return $recurses;
    }


    private function buscaRecursosMerli(&$datacol,$url_cercador,&$data,&$messages)
    {   

        $params = [
            "agrega" => 0,
            "sheetId" => null,
            "tipus" => "simple",
            "nivell" => 0,
            "ordenacio" => "",
            "direccio" => "",
            "novaCerca" => "no",
            "filtreRecurs" => $datacol['tipo_recurso_id'],
            "formatRecurs" => "",
            "inxtec" => 0,
            "cataleg" => "si",
            "userGeneric" => "",
            "nomUsuari" => null,
            "textCercaHidden" => "",
            "textCerca" => "",
            "nivell_educatiu" => $datacol['nivel_id'],
            "area_curricular" => -1,
        ];

        $client = new \GuzzleHttp\Client();        
        $hasContent = true;
        $pagina = 0;

        $urls = [];
        
        while($hasContent) {
            

            $params['nivell'] = $pagina;
            
            //var_dump($url_cercador,$params); die();

            $data['total_requests']++;

            try {
                $res = $client->request('POST', $url_cercador ,[
                    'verify' => false,
                    'form_params' => $params,
                ]);
            } catch (\Exception $e) {
                $msg  = "Buscando enlaces en paginas del buscador:buscaRecursosMerli -> Error POST a la url:" . $url_cercador . "\n ";
                $msg .= $e->getMessage() . "\n";
                $msg .= "Nº Pagina solicitado: $pagina\n";
                $msg .= "iipo_recurso_id: ".$datacol['tipo_recurso_id']."\n";
                $msg .= "nivel_id: ".$datacol['nivel_id']."\n";
                //$msg .= "area_id: ".$datacol['area_id']."\n";
                $msg .= "form_params: ".var_export($params,1)."\n";
                $messages[] = $msg;
                $hasContent = false;
                break;
            }
           

            $status_code = $res->getStatusCode();
            if ($status_code >= 200 && $status_code < 300) {
                $body = $res->getBody()->getContents();  
            } else {
                $messages["Error en la peticion POST a la url: $url_cercador"] = true;
                break;
            }

            
            $doc = new HtmlDocument($body);
            
            $searchHasContent = false;
            foreach ($doc->find('#resultats_left') as $rl) {
                foreach ($rl->find('div') as $div) {
                    foreach ($div->find('a') as $a) {
                        //var_dump('#a');
                        if ($a->hasAttribute('id')) {
                            $urls[] = $a->getAttribute('id');
                            $searchHasContent = true;
                        }
                    }
                }                
            }
            
            $hasContent = $searchHasContent;
            
            
            $pagina++;
            
        }
        //var_dump($urls); die();

        //$data['total_urls'] = $data['total_urls'] + count($urls);
        $datacol['urls'] = $urls;

    }



    /*


         █████  ██████   ██████      ██  █████  ██████  ██      ██ ███████ ███    ██ ███████ ███████ ██  
        ██   ██ ██   ██ ██          ██  ██   ██ ██   ██ ██      ██ ██      ████   ██ ██      ██       ██ 
        ███████ ██████  ██          ██  ███████ ██████  ██      ██ █████   ██ ██  ██ ███████ █████    ██ 
        ██   ██ ██   ██ ██          ██  ██   ██ ██      ██      ██ ██      ██  ██ ██      ██ ██       ██ 
        ██   ██ ██   ██  ██████      ██ ██   ██ ██      ███████ ██ ███████ ██   ████ ███████ ███████ ██  
                                                                                                                                                                                                                     
    */

    private function findInApliense($url_ficha)
    {       
  
        if ($url_ficha) {
            $data = [
                'updates' => 0,
                'counter' => 0,
            ];
            $error_messages = [];
            $this->scrapInArc(null,$data,$error_messages,$url_ficha);
            $info = sprintf("S'han inserit %d noves registres.
                S'han actulitzat %d registres | URLS procesats: %d urls ", 
                $data['counter'], 
                $data['updates'],
                $data['total_urls']
            );

        } else {
                
            $cercador_url = "https://apliense.xtec.cat/arc/cercador";

            $data = [
                'updates' => 0,
                'counter' => 0,
                'images' => 0,
            ];

            $error_messages = [];
            $this->scrapInArc($cercador_url,$data,$error_messages);           
            $data['resumen'] = sprintf("S'han inserit %d noves registres.
                S'han actulitzat %d registres, 
                S'han importat %d imatges | URLS procesats: %d urls ", 
                $data['counter'], 
                $data['updates'],
                $data['images'],
                $data['total_urls']
            );            

            $info = $data['resumen'] . "<br />";                                         
        }

        

        $response['resumen'] = $info;
        $response['messages'] = $error_messages;   
        
        //var_dump($response); die();

        return $response;              
    }
    


    private function scrapInArc($cercador_url=null,&$data,&$messages,$url_ficha = null)
    {
        
        $base_url = "https://apliense.xtec.cat";
       
        # img temp dir
        $dir = plugin_dir_url( __FILE__ );
        $dir = trim($dir, 'admin/');
        $img_temp_dir = plugin_dir_path(__FILE__); //str_replace('admin','tmp_img',$img_temp_dir);
        $img_temp_dir = str_replace('admin/','',$img_temp_dir);

        $client = new \GuzzleHttp\Client();

        if (empty($url_ficha)) {

            #obtencion de la pagina de las colecciones
            $res = $client->request('GET', $cercador_url ,['verify' => false] );
            $body = $res->getBody()->getContents();  
    
            $doc = new HtmlDocument($body);

            $ambits = [];

            foreach ($doc->find('#searchresultsmap') as $div) {
                foreach ($div->find('div.mapelement') as $div_el) {
                    foreach ($div_el->find('div.mapbox') as $mapbox) {                           
                        $element = [];
                        $element['title']       = $div_el->children(1)->children(0)->innertext;
                        $element['href']         = $base_url . $div_el->children(1)->children(0)->getAttribute('href');    
                        $element['elements']    = $mapbox->children(0)->children(2)->innertext;
                        $element['urls']    = [];
                        $ambits[] = $element;
                    }
                }
            }
                
            //var_dump($ambits); die();

            $i=0;
            $index = 0;

            foreach($ambits as $index => $coleccion) {

                $urls = [];
                # hacemos consultas paginadas, ya tenemos cuantos elementos hay y podemos calcular
                # cuantas paginas pueden haber
                $ipp = 25;        
                
                
                $paginas = floor( intval($coleccion['elements']) / $ipp);
                
                for($i=0; $i<=$paginas; $i++) {
                    
                    $url = $coleccion['href'] . '?pag1='.$i.'&pag2=1';
                    $res = $client->request('GET', $url ,['verify' => false] );
                
                    $status_code = $res->getStatusCode();
                    if ($status_code >= 200 && $status_code < 300) {
                        $body = $res->getBody()->getContents();  
                    } else {
                        $messages[] = "Error en la peticion a la url: $url";
                        continue;
                    }

                    
                    $doc = new HtmlDocument($body);
                    

                    foreach ($doc->find('div.searchresultsbox') as $div) {
                        foreach ($div->find('div.item') as $item) {                           
                            $urls[] = $base_url . $item->children(0)->children(0)->getAttribute('href');                                                
                        }                        
                    }
                }   
               
                $ambits[$index]['urls'] = $urls;
                // break;
            }
       
            $recurses = [];
    
            $total_urls = 0;

            foreach($ambits as $index => $coleccion) {
                foreach($coleccion['urls'] as $url) {
                    $recurs = [];
                    $recurs['href'] = $url;
                    $recurs['ambit'] = $coleccion['title'];
                    $recurses[] = $recurs;
                    $total_urls++;
                }
            }

        } else {
            $recurs = [];
            $recurs['href'] = $url_ficha;
            $recurses[] = $recurs;
            $total_urls = 1;   
        }

       
        $data['total_urls'] = $total_urls;
        
        

        $arr_recurses = [];

        # recorremos todos los recursos de todas las paginas de origen
        foreach ($recurses as $recurs) {
            $row = [];

            try {
                $res = $client->request('GET', $recurs['href'] ,['verify' => false] );
            } catch (\Exception $e) {
                $messages[] = "Error en la peticion a la url: " . $recurs['href'] . " | " . $e->getMessage() ;
                continue;
            }
            
            $body = $res->getBody()->getContents();    
            
            $doc = new HtmlDocument($body);
            
            $row['xtra-data-json'] = [];


            $row['url_import'] = $recurs['href'];
            
            foreach ($doc->find('#titletext') as $title) {
                $row['title'] = $title->innertext;
            }
            
            foreach ($doc->find('#contentleft') as $left) {
                $row['url_img'] = $base_url . $left->children(0)->children(0)->getAttribute('src');
            }

            foreach ($doc->find('#contentright') as $rigth) {

                foreach ($rigth->find('div') as $el) {

                    $labels = [];
                    foreach ($el->find('p.descriptors') as $p) {
                        foreach ($p->find('span') as $span) {
                            $labels[] = $span->children(0)->innertext;
                        }
                    }
     
                    foreach ($el->find('p.fieldtitle') as $p) {
                        $title = trim($p->innertext);
                        switch($title) {
                            case 'Resum':
                                $row['description'] = $p->nextSibling()->innertext;
                                break;
                            case 'Eixos de capacitats / Competències':
                                $node = $p->nextSibling()->children(0);
                                foreach ($node->find('a') as $a) {
                                    if (!isset($row['xtra-data-json'][$title])) {
                                        $row['xtra-data-json'][$title] = [];
                                    }
                                    //if (!in_array(trim($a->innertext),$row['xtra-data-json'][$title])) {
                                        $row['xtra-data-json'][$title][] = trim($a->innertext);
                                    //}
                                }
                                break;
                            case 'Àrees / Matèries':
                                $node = $p->nextSibling()->children(0);
                                foreach ($node->find('a') as $a) {
                                    if (!isset($row['xtra-data-json'][$title])) {
                                        $row['xtra-data-json'][$title] = [];
                                    }
                                    //if (!in_array(trim($a->innertext),$row['xtra-data-json'][$title])) {
                                        $row['xtra-data-json'][$title][] = trim($a->innertext);
                                    //}                                    
                                    
                                }
                                break;
                        }                        
                    }                    
                    break;
                }
                $row['xtra-data-json']['ambit'] = $recurs['ambit'];
                $row['xtra-data-json']['labels'] = $labels;
            }
                

            $arr_recurses[] = $row;
           
        }

        //var_dump($arr_recurses); die();
        
        $messages = [];

        foreach($arr_recurses as $row) {
            $this->saveCommonResource('arc',$data,$messages,$row);            
        }
        
        
        
    }





    /*


         ██████  ██████  ███    ███ ███    ███  ██████  ███    ██                 
        ██      ██    ██ ████  ████ ████  ████ ██    ██ ████   ██                 
        ██      ██    ██ ██ ████ ██ ██ ████ ██ ██    ██ ██ ██  ██                 
        ██      ██    ██ ██  ██  ██ ██  ██  ██ ██    ██ ██  ██ ██                 
         ██████  ██████  ██      ██ ██      ██  ██████  ██   ████                 
                                                                                  
                                                                                  
        ███████ ██    ██ ███    ██  ██████ ████████ ██  ██████  ███    ██ ███████ 
        ██      ██    ██ ████   ██ ██         ██    ██ ██    ██ ████   ██ ██      
        █████   ██    ██ ██ ██  ██ ██         ██    ██ ██    ██ ██ ██  ██ ███████ 
        ██      ██    ██ ██  ██ ██ ██         ██    ██ ██    ██ ██  ██ ██      ██ 
        ██       ██████  ██   ████  ██████    ██    ██  ██████  ██   ████ ███████ 
                                                                                  
                                                                                  


    */


    private function makeUpdate($table,$post_data)
    {       
        # no tocamos el estado cuando actualizamos
        unset($post_data['status']);
        unset($post_data['created_at']);

        $post_data['updated_at'] = date('Y-m-d H:i:s');

        $res = $this->wpdb->update(
            $table, 
            $post_data, 
            array('url_import' => $post_data['url_import'] ) 
        ) ;
        if ($res !== false) {
            return true;
        }
        return false;
    }

    private function saveCommonResource($source,&$data,&$messages,$row,$post_type=null)
    {
        $scrap_id = null;
        # data table
        $post_data = [
            'post_type' => $post_type,
            'url_import' => $row['url_import'],
            'url_img' => $row['url_img'],
            'url_descarga' => $row['url_descarga'],
            'status' => 'ignore',
            'source' => $source,
            'title' => $row['title'],
            'description' => $row['description'],
            'extra_data_json' => json_encode($row['xtra-data-json']),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        //var_dump($post_data); die();    

        try {
            $result = $this->wpdb->insert($this->wpdb->prefix.'kpr_scrappeddata', $post_data);
        } catch (\Exception $e) {
            //$messages[] = "Error en la insercion de datos: " . $e->getMessage();
            $result = false;
        }
        
        //var_dump($result); die();    
        
        if ($result === false) {     
            $inserted = false;
            unset($post_data['created_at']);                       
            $updated = $this->makeUpdate($this->wpdb->prefix.'kpr_scrappeddata',$post_data);
            if ($updated) {
                
                $query = $this->wpdb->prepare(
                    "SELECT id FROM " .$this->wpdb->prefix."kpr_scrappeddata WHERE url_import = %s AND source = %s",
                    $row['url_import'],
                    $source
                );
                $qres = $this->wpdb->get_results($query);
                
                $scrap_id = isset($qres[0]->id) ? $qres[0]->id : null;
                if ($scrap_id) {
                    $data['updates']++;
                    //$messages[] = "BAD QUERY: $query | " . $row['url_import'] . " | " . var_export($qres,1); 
                }
            }
        }
               

        if (empty($scrap_id) && $this->wpdb->insert_id > 0 ) {
            $scrap_id = $this->wpdb->insert_id;
            $inserted = true;
            $data['counter']++;
        }
        // Ruta al archivo que deseas subir
        

        if ($scrap_id && $inserted && isset($row['url_img'])) {

            $this->tractaImageDestacada($scrap_id,$row,$messages,$source);
            
        }
        
        /*
        if (isset($row['xtra-data-json']) && count($row['xtra-data-json']) ) {
            //$this->crearTipologias($row['xtra-data-json'],$source,$messages);
        }
        */
        
        
    }


    private function tractaImageDestacada($scrap_id,$row,&$messages,$source)
    {
        $dir = plugin_dir_url( __FILE__ );
        $dir = trim($dir, 'admin/');
        $img_temp_dir = plugin_dir_path(__FILE__); //str_replace('admin','tmp_img',$img_temp_dir);
        $img_temp_dir = str_replace('admin/','',$img_temp_dir);

        if ($source == 'toolbox') {
            $filename = explode('/Icons/',$row['url_img'])[1];
            $filename = explode('?',$filename)[0];
            $filename = str_replace('/','_',$filename);
            $file = file_get_contents($row['url_img']);
        } else {
            $arr      = explode('/',$row['url_img']);
            $filename = $arr[count($arr)-1];
            if (strpos($filename,'?') !== false) {
                $filename = explode('?',$filename)[0];
            }
            $filename = str_replace('/','_',$filename);
            $file = file_get_contents($row['url_img']);

        }
        
        
        $rutaArchivo = $img_temp_dir . 'tmp_img' .DIRECTORY_SEPARATOR.  $filename;
        $res = file_put_contents($rutaArchivo,$file);
        
        if ($res) {
            $rutaArchivo = $this->renameAndSetExtensionToImg($rutaArchivo,$filename,$img_temp_dir);

            $infoArchivo = wp_upload_bits($filename, '', $file);

            // Verificar si la subida fue exitosa
            if ($infoArchivo['error']) {
                $messages['Error al subir el archivo: ' . $infoArchivo['error']] = true;
                return false;
            } else {
                // Configurar los metadatos del archivo
                $attachment = array(
                    'post_mime_type' => mime_content_type($rutaArchivo),
                    'post_title'     => preg_replace('/\.[^.]+$/', '', $filename),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                );
            
                // Insertar el archivo en la biblioteca de medios
                $attachment_id = wp_insert_attachment($attachment, $infoArchivo['file']);
                
                // Verificar si la subida fue exitosa
                if (is_wp_error($attachment_id)) {
                    $messages[] = 'Error al subir la imagen: ' . $attachment_id->get_error_message();
                } else {
                    $this->wpdb->update(
                        $this->wpdb->prefix.'kpr_scrappeddata', 
                        ['attachment_id' => $attachment_id], 
                        array('id' => $scrap_id ) 
                    ) ;

                    # borramos archivo temporal
                    unlink($rutaArchivo);

                }

                // Generar metadatos para el archivo
                //require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attach_data = wp_generate_attachment_metadata($attachment_id, $infoArchivo['file']);
                wp_update_attachment_metadata($attachment_id, $attach_data);

                return true;
            
            }                        
        } else {
            $messages[] = 'Error al descargar la imagen: ' . $row['url_img'];
            return false;
        }       
        return false;
    }       

    /*
    private function crearTipologias($data,$source,&$messages)
    {
        
        foreach($data as $tipologia => $values ) {
                    
            $data_tipologia = [
                'source' => $source,
                'name' => $tipologia,
                'title' => $tipologia,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            
            $result = $this->wpdb->insert($this->wpdb->prefix.'kpr_typologies', $data_tipologia);
            if ($result === false) {
                $messages[$this->wpdb->last_error] = true;
                
                $table = $this->wpdb->prefix.'kpr_typologies'; 
                $sql = "SELECT * FROM $table where `name` = '" .  $tipologia . "' and `source` = '".$source."'";
                
                $qres = $this->wpdb->get_results($sql);         
                $tipologia_id = $qres[0]->id;
            } else {
                $tipologia_id = $this->wpdb->insert_id;
            }
            
            if (is_numeric($tipologia_id)) {
                foreach($values as $value) {
                    $tipologia_data = [
                        'typology_id' => $tipologia_id,
                        'name' => $value,
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                    $result = $this->wpdb->insert($this->wpdb->prefix.'kpr_typologyvalues', $tipologia_data);
                    if ($result === false) {
                        $messages[$this->wpdb->last_error] = true;
                    } 
                }
            }
        }
    }
    */

    private function renameAndSetExtensionToImg($rutaArchivo,&$filename,$img_temp_dir) {

        $newname = wp_generate_uuid4();


        // Obtener el mime_type del archivo
        $mime_type = mime_content_type($rutaArchivo);

        // Obtener la lista de extensiones asociadas al mime_type
        $extensionesPosibles = wp_get_mime_types();

        // Seleccionar la primera extensión de la lista (puedes ajustar esto según tus necesidades)
        $extensiones_permitidas = [];
        
        if (is_array($extensionesPosibles)) {
            foreach($extensionesPosibles as $ext => $mime) {
                if ($mime_type==$mime) {                    
                    $exfound = explode('|', $ext)[0];
                    if (!isset($extension_mime)) {
                        $extension_mime = $exfound;
                    }
                } 
                $extensiones_permitidas = array_merge($extensiones_permitidas, explode('|', $ext));
            }
        } else {
            return $rutaArchivo;
        }

        # comprobamos si ya tiene extension, en tal caso devolvemos con la encontrada por mime_type
        $arr = explode(".",$filename);
        $ext = $arr[count($arr)-1];
        if (in_array($ext, $extensiones_permitidas)) {    
            $final_ext = $ext;           
        } else {
            $final_ext = isset($extension_mime) ? $extension_mime : null;           
        }
        
        if (!$final_ext) {
            return $rutaArchivo;
        }

        $filename = $newname. '.' . $final_ext;           

        $destino = $img_temp_dir. 'tmp_img' .DIRECTORY_SEPARATOR. $filename;
        rename($rutaArchivo, $destino);

        $rutaArchivo = $destino;    
        //var_dump($destino); die();
        return $rutaArchivo;    

    }








    /*


         ██████  █████  ████████ ███████  ██████   ██████  ██████  ██  █████  ███████     
        ██      ██   ██    ██    ██      ██       ██    ██ ██   ██ ██ ██   ██ ██          
        ██      ███████    ██    █████   ██   ███ ██    ██ ██████  ██ ███████ ███████     
        ██      ██   ██    ██    ██      ██    ██ ██    ██ ██   ██ ██ ██   ██      ██     
         ██████ ██   ██    ██    ███████  ██████   ██████  ██   ██ ██ ██   ██ ███████     
                                                                                          
                                                                                          
        ██████  ███████                                                                   
        ██   ██ ██                                                                        
        ██   ██ █████                                                                     
        ██   ██ ██                                                                        
        ██████  ███████                                                                   
                                                                                          
                                                                                          
        ████████  ██████   ██████  ██      ██████   ██████  ██   ██                       
           ██    ██    ██ ██    ██ ██      ██   ██ ██    ██  ██ ██                        
           ██    ██    ██ ██    ██ ██      ██████  ██    ██   ███                         
           ██    ██    ██ ██    ██ ██      ██   ██ ██    ██  ██ ██                        
           ██     ██████   ██████  ███████ ██████   ██████  ██   ██                       
                                                                                                                                                                                      
    
    */

    public function createCategoriesFromToolBox() { 

        check_ajax_referer( 'createCategoriesFromToolBox_nonce' );

        $total_cat = 0;

        $table_parent = $this->wpdb->prefix.'kpr_typologies'; 
        $table_values = $this->wpdb->prefix.'kpr_typologyvalues';
        $typologies = $this->wpdb->get_results("SELECT * FROM " . $table_parent);

        foreach ($typologies as $typology) {
            $parent_term_id = wp_insert_term(
                $typology->title, 
                'category'
            );

            if (!is_wp_error($parent_term_id)) {                
                $values = $this->wpdb->get_results(    
                    $this->wpdb->prepare(
                        "SELECT * FROM 
                        $table_values 
                        WHERE typology_id = %d", $typology->id
                    )
                );
            
                foreach ($values as $row) {
                    $term_id = wp_insert_term(
                        $row->name, 
                        'category', 
                        array(
                            'parent' => $parent_term_id,
                        )
                    );
                    if (!is_wp_error($term_id)) { 
                        $total_cat++;                    
                    }
                }
            }
        }

        $response['error'] = false;
        $response['message'] = "Categories creades: $total_cat";
       
        $response = json_encode( $response );
        echo $response;
        die(); 

    }








    /*


        ██   ██ ████████ ███    ███ ██      
        ██   ██    ██    ████  ████ ██      
        ███████    ██    ██ ████ ██ ██      
        ██   ██    ██    ██  ██  ██ ██      
        ██   ██    ██    ██      ██ ███████ 
                                            
                                            


    */

    
    public function getHTMLScrappings($return_json = true) { 

        if ($return_json) {
            check_ajax_referer( 'getHTMLScrappings_nonce' );
        }
        
        try {
            $result = self::getDataScrappings();    
        } catch ( Exception $e) {
            $result = $e->getMessage();
        }
    
        //var_dump($result); die();

        if (isset($result['data'])) {
            
            $listado_html = $this->get_listado($result['data']);
    
            $result['error'] = false;
            $result['html'] = $listado_html;
            $result['paginator'] = self::getPagination($result);
            unset($result['data']);
            
            if ($return_json===false) {
                return $result;
            } else {
                status_header(200);
                echo wp_send_json($result);
            }
    
        } else {
            status_header(200);
            echo wp_send_json([
                "error" => true,
                "message" => $result,
            ]);
        }
    }

    private static function getPagination($result) { 
        $page = $result['page'];
        $per_page = $result['per_page'];
        $total = $result['total'];        

        $total_pages = round($total / $per_page);

        if (!$total) return 'The scrapping table is empty.';

        $prior_class = 'a_navigator';
        $next_class = 'a_navigator';

        if ($page == 1) {
            $prior_class = ' a_navigator_disabled ';
        }

        if ($page+1 > ($total_pages) ) {
            $next_class = ' a_navigator_disabled ';
        } 

        if ($total>1) {
            $label = " Registres ";
        } else {
            $label = " Registre ";
        }

        if ($total_pages>0) {
            $html = '<select id="pagina_actual" >';
            for($i=1; $i <= $total_pages; $i++) { 
                if ($i==$page) {
                    $selected = 'selected';
                } else {
                    $selected = '';
                }
                $html .= "<option $selected value='$i'>$i</option>";
            }
            $html .= '</select><span id="loader_select_page"></span>';
        } else {
            $html = '1';
            $total_pages = 1;
        }

        ob_start();

        ?>      
                 
                    Total: <strong><?php echo $total ?></strong> <?php echo $label ?>  &nbsp;&nbsp;&nbsp;&nbsp;
                    
                    <i class="fa fa-chevron-left fa-lg prior_row <?php echo $prior_class  ?> "                        
                            data-page="<?php echo $page ?>" 
                            data-per_page="<?php echo $per_page ?>" 
                            data-total="<?php echo $total ?>" >
                        
                    </i>   &nbsp;
                    <span class="pages_label">Pàgina 
                        <?php echo $html . " de <strong>" . ($total_pages) ?></strong> &nbsp;
                    </span>                
                    
                    <i class="fa fa-chevron-right fa-lg next_row <?php echo $next_class  ?> "                        
                            data-page="<?php echo $page ?>" 
                            data-per_page="<?php echo $per_page ?>" 
                            data-total="<?php echo $total ?>" >                       
                    </i>
                
        <?php 

        $contenido = ob_get_contents();
        ob_end_clean();
        return $contenido;
    }
  
    
    public function get_listado($results)
    {   
        $columns = $this->columns_def_scr;
        ob_start();

        ?>
        <thead>
            <tr>
                <th class="text-left"> 
                    Selecció                     
                </th> 
                <?php echo self::get_listado_header(); ?>
            </tr>
        </thead>                    
        <tbody> 
                <?php                    
                foreach( $results as $row ) { 
                    $data_json = json_decode($row->extra_data_json);
                    $data_json->title = $row->title;
                    $data_json->description = $row->description;
                    $extra_data_json = base64_encode(json_encode($data_json));
                    ?>
                    <tr id="row<?php echo $row->id ?>"  data-extra-json="<?php echo $extra_data_json ?>" >
                        <?php echo $this->render_row($row);  ?>
                    </tr> 
                <?php } //endforeach ?> 
        </tbody><?php

        $contenido = ob_get_contents();
        ob_end_clean();
        return $contenido;
    }    
    
    public function get_listado_header()
    {   
        $columns = $this->columns_def_scr;

        ob_start();
        foreach( $columns as $field ) { 
            if (!isset($field['view']) || isset($field['view']) && $field['view'] == 'web' ) {
                ?>
                    <th class="text-left"><?php echo $field['title'] ?></th>        
                <?php 
            }
        } 

        $contenido = ob_get_contents();
        ob_end_clean();
        return $contenido;
    }    

    private function render_row($row) {
        $columns = $this->columns_def_scr;
        ob_start();
        
        ?>
        <td><input class="importacionsSelecterCheckbox" type="checkbox" value="<?php echo $row->id?>" name="selection[]" />  </td>
        <?php
        foreach( $columns as $key => $field ) 
        { 

            
            if (!isset($field['view']) || isset($field['view']) && $field['view'] == 'web' ) {
            ?>
            <td <?php if ($key=='info') { echo "data-id='".$row->id."' class='clickOnShowDataJSON' "; } ?> >
                <?php 
                    switch(@$field['type']) {
                        case 'source' :                             
                            $color = 'btn-warning';
                            if ($row->source=='jclic') {
                                $row->url_import = "https://clic.xtec.cat/projects/".$row->url_import."/project.json";
                            }
                            ?>                            
                                <a href="<?php echo $row->url_import?>" target="_blank" title="<?php echo $row->url_import?>">
                                    <button type="button" class="btn btn-sm <?php echo $color?>" >
                                        <?php echo $row->{$key}?>
                                    </button>
                                </a>
                            <?php 
                            break;
                        case 'status' :                             
                            switch($row->{$key}) {
                                default:
                                case 'ignore' : 
                                    $label = "Sense tractar";
                                    $color = 'btn-danger'; break;
                                case 'draft' : 
                                    $label = "Post (draft)";
                                    $color = 'btn-dark'; break;
                                case 'publish' : 
                                    $label = "Post (publish)";
                                    $color = 'btn-success'; break;
                            }
                                
                            ?>
                                <button type="button" class="btn btn-sm <?php echo $color?>" >
                                    <?php echo $label?>
                                </button>
                            <?php 
                            break;

                        case 'date' :
                            if (!is_null(@$row->{$key})) {                                        
                                echo "<small>" . str_replace(' ','<br />',$row->{$key}) . "</small>"; 
                            }
                            break;


                        case 'post_link' : 
                            if ($key=='post_id' && !empty($row->post_id)) {
                                $url = get_edit_post_link($row->post_id);
                                echo "<a href='$url' target='_blank' >".$row->post_id."</a>";
                            }
                            break;

                        case 'image' : 
                            if (is_numeric($row->attachment_id)) {
                                $thumbnail_size = array(40, 40);
                                $image_thumb = wp_get_attachment_image($row->attachment_id, $thumbnail_size);
                                echo $image_thumb; 
                            }
                            break;

                        case 'numeric' : 
                            if (!empty($row->{$key})) { 
                                ?><strong style="color:#080"><?php echo $row->{$key} ?></strong><?php 
                            } else { 
                                echo "#" ;
                            } 
                            break;

                        case 'link' : 
                            if (!empty($row->{$key})) { 
                                $edit_link = get_edit_post_link($row->{$key});
                                ?><a href="<?php echo $edit_link ?>" target='_blank'><?php echo $row->{$key} ?></a><?php 
                            } else { 
                                echo "#" ;
                            } 
                            break;
                        case 'string' : 
                            if (isset($field['short']) && is_numeric($field['short'])) {
                                $value = strip_tags($row->{$key});
                                $value = substr($value,0, $field['short']);
                                $value = htmlspecialchars($value);

                                $end = '';
                                if (strlen($value) > $field['short']) {
                                    $end = '...';
                                }
                                if  ( $end != '' ) {
                                    $value .= $end;
                                    $val = str_replace('\"',"`",$value);
                                    $val = str_replace("'","`",$val);
                                    echo "<span title='".$val."'>" . $value . "</span>";
                                } else {
                                    //$val = str_replace("'","`",$val);
                                    //$val = str_replace('\"',"`",$val);
                                    echo $value;
                                }
                            } else {
                                //echo htmlspecialchars($row->{$key},ENT_SUBSTITUTE);
                                //echo $row->{$key}; 
                            }
                            break;
                        case 'html' : 
                            echo $field['content'];
                            break;
                        default : 
                            echo strip_tags($row->{$key}); 
                            break;
                    }
                ?>
            </td><?php        
            } //end inf
        } //end foreach

        $contenido = ob_get_contents();
        ob_end_clean();
        return $contenido;
    }

    public static function getColumnsDefScrapping() 
    {        
        return [            
            "post_id" => [
                'title' => 'PostID',
                'type'  => 'link',
            ],
            "post_type" => [
                'title' => 'Post Type',
            ],
            "img" => [
                'title' => 'Imatge',
                'type'  => 'image',
                'size'  => '80',
            ],
            "status" => [
                'title' => 'Estat',
                'type'  => 'status',
            ],
            "source" => [
                'title' => 'Origen',
                'type' => 'source',
            ],
            "info" => [
                'title' => 'Info',
                'type' => 'html',
                'content' => '<a href="#"><i class="fa-solid fa-circle-info"></i></a>',
            ],
            "title" => [
                'title' => 'Títol',
                'type' => 'string',
                'short' => 30,
            ],
            "description" => [
                'title' => 'Descripció',
                'type' => 'string',
                'short' => 30,
            ],
            "created_at" => [
                'title' => 'Data creació',
                'type'  => 'date',
            ],
            "updated_at" => [
                'title' => 'Data Act.',
                'type'  => 'date',
            ],
            "extra_data_json" => [
                'title' => 'Extra Data JSON',
                'type'  => 'row-data',
                'view'  => 'none',
            ],
        ];  

    }


    /*


         ██████ ██████  ███████  █████  ████████ ███████     
        ██      ██   ██ ██      ██   ██    ██    ██          
        ██      ██████  █████   ███████    ██    █████       
        ██      ██   ██ ██      ██   ██    ██    ██          
         ██████ ██   ██ ███████ ██   ██    ██    ███████     
                                                             
                                                             
        ██████   ██████  ███████ ████████                    
        ██   ██ ██    ██ ██         ██                       
        ██████  ██    ██ ███████    ██                       
        ██      ██    ██      ██    ██                       
        ██       ██████  ███████    ██                       
                                                             
                                                             


    */

    public function doSelectedAction() 
    {
        check_ajax_referer( 'doSelectedAction_nonce' );
        
        $response = [];
        $messages = [];
                
        $action = $_POST['insideAction'];

        try {
            switch($action) {
                case 'upsertPost':
                    $messages = $this->upsertPostFromScrapping();
                    break;
            }
            
        } catch (\Exception $e) {
            $response['error'] = true;
            $response['message'] = $e->getMessage();    
            echo $response;
            die();                
        }
        
        $str_mensajes = '';
        foreach ($messages as $key => $message) {
            if (!is_numeric($key)) {
                $str_mensajes .= $key .  '<br />';                
            } else {
                $str_mensajes .= $message .  '<br />';
            }
        }

        $response['error'] = false;
        $response['message'] = $str_mensajes;
       
        $response = json_encode( $response );
        echo $response;
        die();    
    }


    private function upsertPostFromScrapping()
    {
        global $wpdb;

        $table_name = $wpdb->prefix .'kpr_scrappeddata'; 

        $messages = [];
        
        $post_status = $_POST['post_status'] ? $_POST['post_status'] : 'draft';
        $post_type = $_POST['post_type'] ? $_POST['post_type'] : null;

        
        parse_str($_POST['selection'], $ids);  
        
        $ids = $ids['selection'];
        
        parse_str($_POST['data'], $params);  

        $applyToAllFilter = @$params['applyToAllFilter'] ? @$params['applyToAllFilter'] : false;
        $order_by = @$params['order_by'] ? $params['order_by'] : 'created_at';

        $sentido = 'DESC';
        if ($order_by == 'title') {
            $sentido = 'ASC';
        }
        
        $filtres = self::getSQLFiltersScrappings();

        //var_dump($params); die();
        
        if ($applyToAllFilter===false && (!is_array($ids) || count($ids)==0)) {
            $messages[] = "No hi ha cap selecció de registres per processar";
            return $messages;
        }
        
        //var_dump($post_type); die();
        
        if ( $applyToAllFilter )
        {
            set_time_limit(0); 

            $blockData = 1000;
    
            $total_rows = $wpdb->get_var(" select count(*) as total from $table_name scr where 1=1 " . $filtres );
            $chunks = ceil($total_rows / $blockData);
            
            // procesamos por bloques para no agotar memoria
            $count = 0;
            for ($i=0; $i<$chunks; $i++) {
                $offset = $i * $blockData;
                $sql = " 
                    SELECT scr.* 
                    FROM $table_name scr 
                    WHERE 1 = 1  
                ";
                $sql .= $filtres . " ORDER BY scr.".$order_by." " . $sentido;                
                $sql .= " LIMIT $offset, $blockData ";
                
                $data = $wpdb->get_results( $sql );
                
                foreach($data as $row) {
                    $res = $this->upsertPostDataFromScrapping($row, $post_status, $post_type, $messages);
                    if ($res) {
                        $count++;
                    }
                }
            }
            $messages[] = "S'han creat/actualitzat ".$count." posts <br />";
        } else {
            $sql = "
            SELECT scr.* FROM $table_name scr
            WHERE 
                    scr.id in (".implode(',',$ids).")                    
            ";
            $sql .= " ORDER BY scr.".$order_by." " . $sentido;                

            $rows = $wpdb->get_results($sql);
            

            $count = 0;
            foreach($rows as $row) {
                $res = $this->upsertPostDataFromScrapping($row, $post_status, $post_type, $messages);
                
                if ($res) {
                    $count++;
                }
            }
            $messages[] = "S'han creat/actualitzat ".$count." posts <br />";

        }

        return $messages;
    }

    function upsertPostDataFromScrapping($row, $post_status, $post_type, &$messages)
    {
        $post_id = null;

        $post_data = [
            'post_type' => $post_type ? $post_type : ($row->post_type ? $row->post_type : 'undefined'),
            'post_title' => $row->title,
            'post_content' => $row->description,
            'post_status' =>  $post_status,
            'post_author' => 1,
            'post_date' => $row->created_at,
        ];

        if (is_numeric($row->post_id)) {
            # actualizamos
            $post_data['post_id'] = $row->post_id;           
            $res = wp_update_post($post_data);
            if ($res !== false) {
                $post_id = $row->post_id;
            } else {
                # sino actualiza el post no actualizamos la tabla kpr_scrappeddata mas abajo
                return false;
            }
        } else {
            
            # insertamos
            $post_id = wp_insert_post($post_data);
    
            if (is_numeric($post_id)) {
                
                # Asociar la imagen al post
                set_post_thumbnail($post_id, $row->attachment_id);
                
                
            } else {
                $messages['Insert post has failed!'] = true;
                return false;
            }
        }

        # actualizamos estado tabla scrappings
        if (is_numeric($post_id)) {
            $result = $this->wpdb->update(
                $this->wpdb->prefix.'kpr_scrappeddata', 
                [
                    'post_id' => $post_id, 
                    'status' => $post_status,
                    'post_type' => $post_type,
                    'updated_at' => date('Y-m-d H:i:s'),
                ],  ['id' => $row->id] 
            );
            if ($result === false) {
                $messages[$this->wpdb->last_error] = true;
            }            
        }

        $this->updateCustomFields($row,$post_id);

        return true;

    }

    function updateCustomFields($row,$post_id)
    {
        # en $row tenemos todos los campos de la tabla wp_kpr_scrappeddata

        $json = json_decode($row->extra_data_json);  

        switch($row->post_type) {
            case 'app' :          
                update_field('app_download_' . $row->sistema , $row->url_descarga, $post_id);       
                update_field('cost', $row->price, $post_id);       
                break;
            case 'jclic' :        
                update_field('adreca_activitat_jclic', $row->url_descarga, $post_id);                       
                if (!empty($json->author)) {
                    // update_field('jclic_author', $json->author, $post_id);                       
                }                
                break;
        }
    }


    /*


        ███████  ██████  ██      
        ██      ██    ██ ██      
        ███████ ██    ██ ██      
             ██ ██ ▄▄ ██ ██      
        ███████  ██████  ███████ 
                    ▀▀           
                                 

    
    */
    public static function getDataScrappings($createPosts = false, $blockData = 1000)
    {

        global $wpdb;

        $table_name = $wpdb->prefix .'kpr_scrappeddata'; 

        parse_str(@$_POST['data'], $params);  

        $page = @$_POST['paged'] ? $_POST['paged'] : 1;
        $per_page = @$_POST['per_page'] ? $_POST['per_page'] : 10;
        $order_by = @$params['order_by'] ? $params['order_by'] : 'created_at';

        $sentido = 'DESC';
        if ($order_by == 'title') {
            $sentido = 'ASC';
        }

        if (!is_numeric($page)) $page = 1;
        if (!is_numeric($per_page)) $per_page = 10;

        $offset = $per_page * ($page-1);
        
        $filtres = self::getSQLFiltersScrappings();
        
        $sql = " 
            SELECT scr.* 
            FROM $table_name scr 
            WHERE 1 = 1  
        ";
        $sql .= $filtres . " ORDER BY scr.".$order_by."  " . $sentido;
        
        
        $sql .= " LIMIT $offset, $per_page ";

        //var_dump($sql); die();
        
        $total_rows = $wpdb->get_var(" select count(*) as total from $table_name scr where 1=1 " . $filtres );
        $data = $wpdb->get_results( $sql );
        
        //var_dump($sql); die(); 
        
        $result = [
            "data" => $data,
            "page" => $page,
            "per_page" => $per_page,
            "total" => $total_rows,
        ];         
        
        return $result;
    }
    
    private static function getSQLFiltersScrappings()
    {
        global $wpdb;

        parse_str(@$_POST['data'], $data);  

        //self::setCustomCookies($data,'filtresImportacions');

        $created_at_desde = !empty($data['created_at_desde']) ? $data['created_at_desde'] : null;
        $created_at_hasta = !empty($data['created_at_hasta']) ? $data['created_at_hasta'] : null;
        $post_type = !empty($data['post_type']) ? $data['post_type'] : null;
        $status = !empty($data['status']) ? $data['status'] : null;
        $source = !empty($data['source']) ? $data['source'] : null;
        $title = !empty($data['title']) ? $data['title'] : null;
        $descripcion = !empty($data['descripcion']) ? $data['descripcion'] : null;
        $json_key = !empty($data['json_key']) ? $data['json_key'] : null;
        $json_value = !empty($data['json_value']) ? $data['json_value'] : null;        
    

        $filtres = '';

        if ( $post_type=='null' ) {
            $filtres .= " AND scr.post_type IS NULL  ";            
        } else if ($post_type) {
            $filtres .= " AND scr.post_type = '".$post_type."' ";            
        }

        if ( $status ) {
            if ($status=='draft_publish') {
                $filtres .= " AND scr.status in ('draft','publish') ";            
            } else {
                $filtres .= " AND scr.status = '".$status."' ";
            }
        }

        if ( $json_key && $json_value ) {
            //$filtres .= " AND scr.".$json_column."->'$.".$json_key."' like '%".$json_value."%' ";            
            $filtres .= " AND JSON_UNQUOTE(JSON_EXTRACT(scr.extra_data_json, '$.\"".$json_key."\"')) LIKE '%".$json_value."%' " ;
        }

        if ( $source ) {
            $filtres .= " AND scr.source = '".$source."' ";            
        }

        if ( $title ) {
            $filtres .= " AND scr.title like '%".$title."%' ";            
        }

        if ( $descripcion ) {
            $filtres .= " AND scr.description like '%".$descripcion."%' ";            
        }

        if ( $created_at_desde ) {
            $filtres .= "                
                AND scr.created_at >= '".$created_at_desde." 00:00:00' 
            ";            
        }
        if ( $created_at_hasta ) {
            $filtres .= "                
                AND scr.created_at <= '".$created_at_hasta." 23:59:59' 
            ";            
        } 

        
        
        return $filtres;
    }

}
