<?php

class notify_me extends notify_me_helper
{

    public function __construct()
    {
        $adminclass = new notify_me_admin;
        $this->plugin_path = get_home_path() . $this->plugin_path_relative;
        $this->plugin_url = get_site_url() . '/' .  $this->plugin_url_relative;

        //Add Actions
        add_action('init', [$this, 'load_textdomain']); //load language 
        add_action('post_updated', [$this, 'on_save'], 10, 3);
        add_action('admin_menu', [$this, 'init_admin_menu']);
        add_action('admin_init', [$adminclass, 'add_settings_section_init']);
        add_action('nm_cron_event', array($this, 'nm_cron_run')); 

        register_activation_hook( $this->plugin_path . 'notify-me.php', [$this, 'activate_plugin'] );
        register_deactivation_hook( $this->plugin_path . 'notify-me.php', [$this, 'deactivate_plugin'] );

        //register Ajax
        add_action('wp_ajax_nm-ajax', [$this, 'nm_ajax']);
        add_action('wp_ajax_nopriv_nm-ajax', [$this, 'nm_ajax']);

        //Check if the plugin is active or not
        if (get_option('notify_me_activate') === 'true') {
            //Load Button to supported plugins / pages
            $this->add_button();
        }

        //Add Shortcodes
        if (get_option('notify_me_activate_sc') === 'true') {
            add_shortcode('notify_me_button', [$this, 'add_button_sc']);
        }

        $this->set_blacklist(array(
            'ID', 'comment_status', 'ping_status',
            'post_password', 'to_ping', 'post_name', 'pinged', 'post_parent', 'guid',
            'menu_order', 'post_type', 'post_mime_type', 'filter'
        ));
    }
    /**
     * Adds the Button when it is called from a shortcode.
     *
     * @return void
     */
    public function add_button_sc()
    {
        echo $this->add_button(true);
    }
    /**
     * Adds the button to the Page or Event.
     *
     * @param boolean $returnhtml - If true, the function will not include the button to the page. Instead it will output the HTML from the template file. 
     * @return void - bool or string
     */
    public function add_button($returnhtml = false)
    {
        $this->enqueue_scripts();
        $tmp = $this->get_template('button');
        if ($returnhtml) {
            return $this->load_template($tmp);
        } else {
            add_action('tribe_events_single_event_after_the_content', function () use ($tmp) {
                include($tmp);
            }, 99, 1);
        }

        return;
    }

    /**
     * Main Method for the Ajax Calls
     *
     * @return void
     */
    public function nm_ajax()
    {
        $ajax = new notify_me_ajax();
        $action =  $_REQUEST['do'];
        switch ($action) {
            case 'save':
                echo $ajax->save_subscriber($_REQUEST['postid'], $_REQUEST['email']);
                break;
        }
        exit();
    }

    /**
     * Runs if a post gets updates. Saves the entry to the notify-me database
     *
     * @param [integer] $postId
     * @param [object] $postAfter
     * @param [object] $postBefore
     * @return void
     * @todo Add to the queue function, send them later to avoid issues
     */
    public function on_save($postId, $postAfter, $postBefore)
    {
        //check for subscribers
        $subs = get_post_meta($postId, 'nm_subscribers', true);
        if (empty($subs)) {
            return null;
        }
        $subsArr = json_decode($subs);
        
        //compare Changes
        $changes = $this->compare_posts($postAfter, $postBefore);
        if (empty($changes)) {
            return null;
        }

        //Add Users to quere
        $db = new notify_me_db;
        foreach ($subsArr as $email) {
            $db -> add_entry($postId,$email,json_encode($changes),1);
        }
       
        return;
    }

    /**
     * Send the mails to the subscribers
     * 
     *
     * @return void
     * @todo Add a Limit of posts to send
     */
    public function send_mails(){
        $db = new notify_me_db;
        //get post ids from DB
        $items = $db -> get_mails_to_send();
        $ret = array();
        if(!empty($items)){
            foreach($items as $id => $it){
                $ret[$id] = $this -> send_mail_to_subscriber($it -> email, $it -> post_id, json_decode($it -> changes,true));
                if($ret[$id] === true){
                    $db -> update_to_send_entry($it -> id,0);
                }
            }
        }
        return $ret;

    }
    /**
     * Sends a mail with the changes to the subscriber
     *
     * @param [string] $email - email of the subscriber
     * @param [interger] $postId - Post ID of the post, which got updated
     * @param [array] $changes - Array of changes arra(array('oldVal','newVal'), array....)
     * @return [bool] true on success, false on error
     */
    public function send_mail_to_subscriber($email, $postId,$changes){
        $tmp = $this->get_template('compare');
        if (empty($tmp)) {
            return null;
        }
        $changesHtml = $this->load_template($tmp, array('postid' => $postId, 'changes' => $changes));

        //Send changes mail
        $send = new notify_me_emailer;
        $send->set_message_from_template(array('pageId' => $postId, 'message' => $changesHtml), 'default');
        //$send -> set_message($changesHtml);
        $oldTitle = (isset($changes['post_title'][0]))?$changes['post_title'][0]:get_the_title($postId);
        
        $send->set_subject(get_option('blogname') . '  ' .  sprintf(__('Changes made to "%s"!'), $oldTitle));
        $send -> set_receiver($email);
        return $send -> send_email();

    }
    /**
     * Main function to run the cron jobs.
     *
     * @return void
     */
    public function nm_cron_run(){
        $this -> send_mails();
    }

    /**
     * Adds the Notify-Me Settings page
     *
     * @return void
     */
    public function init_admin_menu()
    {
        add_options_page('Notify Me! - ' . __('Settings'), 'Notify Me!', 'manage_options', 'notify-me', [$this, 'show_admin_menu'], 'none');

    }
    /**
     * Loads the Template of the Admin page
     *
     * @return void
     */
    public function show_admin_menu()
    {
        $tmp = $this->get_template('admin_menu', 'templates/admin/');
        echo $this->load_template($tmp);

        return;
    }

    /**
     * Loads the notify-me textdomain.
     * Tries first to load from template dir, on failure it loads from plugin dir.
     *
     * @return void
     */
    public function load_textdomain()
    {
        //Try to load from theme dir first
        if (load_textdomain('notify-me', get_stylesheet_directory() . '/notify-me/' . 'languages/notify-me-' . determine_locale() . '.mo') === false) {
            //Load it from the plugins dir
            load_textdomain('notify-me', $this->plugin_path . 'languages/notify-me-' . determine_locale() . '.mo');
        }
    }

/**
 * Check on activation of the plugin if the right version is installed
 * Sets the shedule for the cron job
 *
 * @return void
 * @todo Allow user to set the frequency of the wp_shedule_event
 */
    public function activate_plugin() {

        if(empty(get_option( 'nm_version'))){
            $db = new notify_me_db;
            $db -> create_db();
        }
        wp_schedule_event( time(), 'hourly', 'nm_cron_event');
             
    }
    /**
     * Runs if the Plugin gets deactivated
     * Deletes the shedule of the cron job
     *
     * @return void
     */
    public function deactivate_plugin(){
        wp_clear_scheduled_hook( 'nm_cron_event' );
    }
}
