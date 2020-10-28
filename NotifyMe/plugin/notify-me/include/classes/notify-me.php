<?php

class notify_me{

    private $button_tmp = "";
    private $plugin_path = "";
    private $plugin_path_relative = 'wp-content/plugins/notify-me/';
    private $plugin_url = '';
    private $helper = null;
    private $scriptsLoaded = false;

    public function __construct()
    {
        $this -> plugin_path = get_home_path() . $this -> plugin_path_relative;
        $this -> plugin_url = get_site_url() . '/' .  $this -> plugin_path_relative;
        $this -> helper = new notify_me_helper;

        //register Ajax
        add_action('wp_ajax_nm-ajax', [$this, 'nm_ajax']);
        add_action( 'wp_ajax_nopriv_nm-ajax', [$this, 'nm_ajax'] );

    }
/**
 * Adds the button
 *
 * @param boolean $returnhtml - If true, the function will not include the button to the page. Instead it will output the HTML from the template file. 
 * @return void - bool or string
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
     *
     * @param [type] $name - Name of the template file to load
     * @return false on error, path on success
     */
    public function get_template($name){
        $tmp = $this -> plugin_path . 'templates/theme/'.$name.'.php';
        if(!file_exists($tmp)){ return false;}
        return $tmp;
    }

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
        global $wpdb; // this is how you get access to the database
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