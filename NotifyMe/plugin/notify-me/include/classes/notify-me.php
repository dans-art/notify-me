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
     * Runs if a post gets updates. Sends a email to every subscriber
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
        $tmp = $this->get_template('compare');
        if (empty($tmp)) {
            return null;
        }
        $changesHtml = $this->load_template($tmp, array('postid' => $postId, 'changes' => $changes));

        //Send changes mail
        $send = new notify_me_emailer;
        $send->set_message_from_template(array('pageId' => $postId, 'message' => $changesHtml), 'default');
        //$send -> set_message($changesHtml);
        $send->set_subject(get_option('blogname') .  sprintf(__('Changes made to "%s"!'), $postBefore->post_title));

        foreach ($subsArr as $s) {
            $send->set_receiver($s);
            $send->send_email();
        }
        return;
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
}
