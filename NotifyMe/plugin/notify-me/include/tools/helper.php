<?php

include_once('kint.phar');

class notify_me_helper
{
    protected $version = '0.1';

    protected $scriptsLoaded = false;
    protected $plugin_url = '';
    public $plugin_path = '';
    protected $plugin_path_relative = 'wp-content/plugins/notify-me/';
    protected $plugin_url_relative = 'wp-content/plugins/notify-me/';
    protected $compareBlacklist = array();
    protected $admin_errors = array();
    protected $admin_infos = array();
    


    public function __construct()
    {

    }

    /**
     * Gets a file and transport some data to use.
     *
     * @param [type] $file
     * @param array $data
     * @return void
     */
    public function load_template($file, $data = array())
    {
        ob_start();
        require($file);
        return ob_get_clean();
    }
    /**
     * Gets the Plugin Path. From the current Theme (/notify-me/templates/) or from the Plugin
     * Structure is the same for plugin an theme
     *
     * @param [string] $name - Name of the template file to load
     * @param [string] $path - Path to the templates files. Default: templates/theme/
     * @return false on error, path on success
     */
    public function get_template($name, $path = 'templates/theme/')
    {
        if (empty($this->plugin_path)) {
            $this->plugin_path = get_home_path() . $this->plugin_path_relative;
        }
        $loc = get_locale();
        $tmpThemeLoc = get_stylesheet_directory() . '/notify-me/' . $path . $name . '_' . $loc . '.php';
        $tmpTheme = get_stylesheet_directory() . '/notify-me/' . $path . $name . '.php';
        $tmpLoc = $this->plugin_path . $path . $name . '_' . $loc . '.php';
        $tmp = $this->plugin_path . $path . $name . '.php';
        //Check if localized version exists in Theme folder
        if (file_exists($tmpThemeLoc)) {
            return $tmpThemeLoc;
        }
        //Check if default version exists in Theme Folder
        if (file_exists($tmpTheme)) {
            return $tmpTheme;
        }
        //Check if localized version exists in plugin folder
        if (file_exists($tmpLoc)) {
            return $tmpLoc;
        }
        //Check if default version exists in Plugin Folder
        if (!file_exists($tmp)) {
            return false;
        } //Not found in Theme as well as in plugin folder
        return $tmp;
    }

    /**
     * Compares to WP_Post objects. Uses the $this -> compareBlacklist.
     *
     * @param [object] $new - The field after saving
     * @param [object] $old - The field before saving
     * @return [array] $changes - All the missmatch fields array('fieldname' => array('newVal','oldVal'))
     */
    public function compare_posts($new, $old)
    {
        $changes = array();
        foreach ($new as $k => $v) {
            if (is_int(array_search($k, $this->compareBlacklist))) {
                continue;
            }
            if ($new->{$k} !== $old->{$k}) {
                $changes[$k] = array($new->{$k}, $old->{$k});
            }
        }
        return $changes;
    }

    /**
     * Loads the Javascript and css into the head of the page
     * Modifies the script type to "module"
     *
     * @return void
     * @todo Add support for custom stylesheets in theme folder
     */
    public function enqueue_scripts()
    {
        //Load Admin scripts if on plugin-admin page
        global $wp;
$current_slug = $wp->request;
        if( isset($_REQUEST['page']) AND ($_REQUEST['page']) === 'notify-me')  {
            wp_enqueue_style('notify-me-admin', plugins_url('notify-me/style/admin.css'));
       }
        if ($this->scriptsLoaded) {
            return true;
        }
        wp_enqueue_style('notify-me-style', plugins_url('notify-me/style/main.css'));
        wp_enqueue_script('notify-me-app',  plugins_url('notify-me/scripts/notify-me-app.js'), ['jquery']);
        //Set script tag "Module"
        add_filter('script_loader_tag', function ($tag, $handle, $src) {
            if ($handle === 'notify-me-app') {
                return '<script type="module" src="' . esc_url($src) . '"></script>' . '<script>var notify_me_url = "' . esc_url($this->plugin_url) . '"; var wp_site_url = "' . esc_url(get_site_url()) . '";</script>';
            } else {
                return $tag;
            }
        }, 10, 3);
        //set the Site URL for JS
        $this->scriptsLoaded = true;
        return;
    }
    /**
     * Allows you to set wich post-fields you like NOT to compare
     * Default: 'ID', 'comment_status', 'ping_status',
        *'post_password', 'to_ping', 'post_name', 'pinged', 'post_parent', 'guid',
        *'menu_order', 'post_type', 'post_mime_type', 'filter'
     * @param [type] $array
     * @return void
     */
    public function set_blacklist($array)
    {
        $this->compareBlacklist = $array;
    }
    /**
     * Checks if Email adress is valid
     *
     * @param [type] $mail
     * @return boolean
     */
    public function is_email($mail){
        if(sanitize_email($mail) === $mail){
            //is valid
            return true;
        }else{
            return false;
        }
    }
    /**
     * Formats a message to an ERROR message. Wraps <span> around, class "nm-error"  
     *
     * @param [string] $msg - The message to format
     * @return [string] - The formated message
     */
    public function format_error($msg){
        return '<span class="nm-error">' . $msg . '</span>';
    }
    /**
     * Formats a message to an INFO message. Wraps <span> around, class "nm-info"  
     *
     * @param [string] $msg - The message to format
     * @return [string] - The formated message
     */
    public function format_info($msg){
        return '<span class="nm-info">' . $msg . '</span>';
    }
    /**
     * Formats a message to an SUCCESS message. Wraps <span> around, class "nm-success"  
     *
     * @param [string] $msg - The message to format
     * @return [string] - The formated message
     */
    public function format_success($msg){
        return '<span class="nm-success">' . $msg . '</span>';
    }

    /**
     * Reads out all the admin errors. Errors are getting deleted after readout. 
     *add_action( 'admin_notices', [$this, 'get_admin_errors'] );
     * 
     * @return void
     */
    public function get_admin_errors(){
        if(!empty($this -> admin_errors)){
            foreach($this -> admin_errors as $e){
                print('<div class="notice error"><p>Notify-Me: ');
                print($e);
                print('</p></div>');
            }
        }
        $this -> admin_errors = array();
    }
    /**
     * Reads out all the admin Infos. Infos are getting deleted after readout. 
     *  add_action( 'admin_notices', [$this, 'get_admin_infos'] );
     *
     * @return void
     */
    public function get_admin_infos(){
        if(!empty($this -> admin_infos)){
            foreach($this -> admin_infos as $i){
                print('<div class="notice info"><p>Notify-Me: ');
                print($i);
                print('</p></div>');
            }
        }
        $this -> admin_infos = array();
    }

    /**
     * Checks if the Verion of the Plugin. Runs on activation of the plugin and on the admin menu. 
     * Displays admin Error if plugin is older than installed version.
     *
     * @return [mixed] true if plugin and db version is the same, false if not, null if not defined
     */
    public function version_checker(){
        $instvers = get_option('nm_version');
        if(empty($instvers)){
            return null;
        }
        if($instvers === $this -> version){
            return true;
        }
        if($instvers < $this -> version){
            return false;
        }
        if($instvers > $this -> version){
            $this -> admin_errors[] = __('Version missmatch! Please update your Plugin','notify-me');
           return false;
        }

    }
    /**
     * Updates the Version and Database. Updates only if the plugin version is newer than the installed version.
     * Use version_checker first.
     *
     * @return [bool] true on success, false if update failed. 
     */
    public function update_version(){
        $instvers = get_option('nm_version');
        if(empty($instvers)){
            update_option('nm_version', $this -> version);
        }
        if($instvers < $this -> version){
            $db = new notify_me_db;
            if($db -> update_db()){
                $this -> admin_infos = array_merge($this -> admin_infos, $db -> admin_infos);
                return true;
            }
            else{
                $this -> admin_errors = array_merge($this -> admin_errors, $db -> admin_errors);
                return false;
            }
        }
        return false;
       
    }

}
