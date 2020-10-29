<?php

class notify_me extends notify_me_helper{

    private $plugin_path = '';
    private $plugin_path_relative = 'wp-content/plugins/notify-me/';
    private $plugin_url = '';
    private $helper = null;
    private $scriptsLoaded = false;

    public function __construct()
    {
        $this -> plugin_path = get_home_path() . $this -> plugin_path_relative;
        $this -> plugin_url = get_site_url() . '/' .  $this -> plugin_path_relative;
        //$this -> helper = new notify_me_helper;

        //register Ajax
        add_action('wp_ajax_nm-ajax', [$this, 'nm_ajax']);
        add_action( 'wp_ajax_nopriv_nm-ajax', [$this, 'nm_ajax'] );

    }
/**
 * Adds the button to the Page or Event.
 *
 * @param boolean $returnhtml - If true, the function will not include the button to the page. Instead it will output the HTML from the template file. 
 * @return void - bool or string
 * @todo Check if Option for automatic include is set -> create option
 */
    public function add_button($returnhtml = false){
        $tmp = $this -> get_template('button');
        if($returnhtml){
            return $this -> helper -> requireToVar($tmp);
        }else{
            add_action( 'tribe_events_single_event_after_the_content', function () use ($tmp) {
                include($tmp);
                $this -> enqueue_scripts();
            }, 99 , 1 );
        }

       return;
    }
    /**
     * Gets the Plugin Path. From the current Theme (/notify-me/templates/) or from the Plugin
     * Structure is the same for plugin an theme
     *
     * @param [string] $name - Name of the template file to load
     * @param [string] $path - Path to the templates files. Default: templates/theme/
     * @return false on error, path on success
     */
    public function get_template($name,$path = 'templates/theme/'){
        if(empty($this -> plugin_path)){ $this -> plugin_path = get_home_path() . $this -> plugin_path_relative;}
        $tmpTheme = get_stylesheet_directory() . '/notify-me/' . $path .$name.'.php';
        $tmp = $this -> plugin_path . $path .$name.'.php';
        if(file_exists($tmpTheme)){return $tmpTheme;} //check if exists in Theme folder
        if(!file_exists($tmp)){ return false;} //Not found in Theme as well as in plugin folder
        return $tmp;
    }

    /**
     * Loads the Javascripts into the head of the page
     * Modifies the script type to "module"
     *
     * @return void
     */
    public function enqueue_scripts(){
        if($this -> scriptsLoaded){return true;}
        wp_enqueue_script( 'notify-me-app',$this -> plugin_url . 'scripts/notify-me-app.js',['jquery']);
        //Set script tag "Module"
        add_filter( 'script_loader_tag', function ( $tag, $handle, $src ) {
            if ($handle === 'notify-me-app'){
               return '<script type="module" src="' . esc_url( $src ) . '"></script>' . '<script>var notify_me_url = "' . esc_url($this -> plugin_url) . '"; var wp_site_url = "' . esc_url(get_site_url()) . '";</script>';
            }else{return $tag;} 
            }, 10, 3 );
        //set the Site URL for JS
        $this -> scriptsLoaded = true;
        return;
    }
    /**
     * Main Method for the Ajax Calls
     *
     * @return void
     */
    public function nm_ajax() {
        $ajax = new notify_me_ajax();
        $action =  $_REQUEST['do'];
        switch($action){
            case 'save':
                echo $ajax -> save_subscriber($_REQUEST['postid'],$_REQUEST['email']);    
            break;
        }
        exit();
   } 
}