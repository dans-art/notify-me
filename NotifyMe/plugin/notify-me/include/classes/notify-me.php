<?php

class notify_me extends notify_me_helper
{

    /**
     * Loads all the necessary stuff for the plugin
     * Add Actions, register hooks, set default properties, 
     * checks if the plugin auto includes the field in frontend or not.
     */
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

        register_activation_hook($this->plugin_path . 'notify-me.php', [$this, 'activate_plugin']);
        register_deactivation_hook($this->plugin_path . 'notify-me.php', [$this, 'deactivate_plugin']);

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
            add_shortcode('notify_me_manage_subscription', [$this, 'manage_subscription']);
        }

        $this->set_blacklist(array(
            'ID', 'comment_status', 'ping_status',
            'post_password', 'to_ping', 'post_name', 'pinged', 'post_parent', 'guid',
            'menu_order', 'post_type', 'post_mime_type', 'filter'
        ));
    }
    /**
     * Outputs the Button when it is called from a shortcode.
     * Use [notify_me_button]
     *
     * @return [string] - Html Code of the Button. From template templates/theme/button.php
     */
    public function add_button_sc()
    {
        echo $this->add_button(true);
    }
    /**
     * Adds the button to the Page or Event.
     *
     * @param boolean $return_html - If true, the function will not include the button to the page. 
     *                              Instead it will output the HTML from the template file. (templates/theme/button.php) 
     * @return [mixed] - bool or string
     */
    public function add_button($return_html = false)
    {
        $this->enqueue_scripts();
        $template = $this->get_template('button');
        if ($return_html) {
            return $this->load_template($template);
        } else {
            add_action('tribe_events_single_event_after_the_content', function () use ($template) {
                include($template);
            }, 99, 1);
        }

        return;
    }

    /**
     * Main Method for handling the Ajax Calls
     *
     * @return [string] echoes the output of the ajax function
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
     * Runs if a post gets updates. Saves the entry to the notify-me queue
     *
     * @param [integer] $post_id
     * @param [object] $post_after
     * @param [object] $post_before
     * @return [void]
     */
    public function on_save($post_id, $post_after, $post_before)
    {
        //check for subscribers
        $subs = get_post_meta($post_id, 'nm_subscribers', true);
        if (empty($subs)) {
            return null;
        }
        $subs_arr = json_decode($subs);

        //compare Changes
        $changes = $this->compare_posts($post_after, $post_before);
        if (empty($changes)) {
            return null;
        }

        //Add Users to quere
        $db = new notify_me_db;
        foreach ($subs_arr as $email) {
            $db->add_entry($post_id, $email, json_encode($changes), 1);
        }

        return;
    }

    /**
     * Send the mails to the subscribers
     * Called by cron job.
     *
     * @return void
     */
    public function send_mails()
    {
        $db = new notify_me_db;
        //get post ids from DB
        $items = $db->get_mails_to_send();
        if (!empty($items)) {
            foreach ($items as $id => $it) {
                $send = $this->send_mail_to_subscriber($it->email, $it->post_id, json_decode($it->changes, true));
                if ($send === true) {
                    $db->update_to_send_entry($it->id, 0);
                }
            }
        }
        return;
    }
    /**
     * Sends a mail with the changes to the subscriber
     *
     * @param [string] $email - email of the subscriber
     * @param [interger] $post_id - Post ID of the post, which got updated
     * @param [array] $changes - Array of changes arra(array('oldVal','newVal'), array....)
     * @return [bool] true on success, false on error
     * @todo fix pageId / postid confusion -> use only post_id!
     */
    public function send_mail_to_subscriber($email, $post_id, $changes)
    {
        $template = $this->get_template('compare');
        if (empty($template)) {
            return null;
        }
        $changesHtml = $this->load_template($template, array('postid' => $post_id, 'changes' => $changes));

        //Send changes mail
        $send = new notify_me_emailer;
        $send->set_message_from_template(array('pageId' => $post_id, 'message' => $changesHtml), 'default');
        //$send -> set_message($changesHtml);
        $oldTitle = (isset($changes['post_title'][0])) ? $changes['post_title'][0] : get_the_title($post_id);

        $send->set_subject(get_option('blogname') . '  ' .  sprintf(__('Changes made to "%s"!'), $oldTitle));
        $send->set_receiver($email);
        return $send->send_email();
    }
    /**
     * Main function to run the cron jobs.
     *
     * @return void
     */
    public function nm_cron_run()
    {
        $this->send_mails();
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
        $template = $this->get_template('admin_menu', 'templates/admin/');
        echo $this->load_template($template);

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
    public function activate_plugin()
    {

        if (empty(get_option('nm_version'))) {
            $db = new notify_me_db;
            $db->create_db();
        }
        wp_schedule_event(time(), 'hourly', 'nm_cron_event');
        $this->setup_frontend_page();
    }
    /**
     * Runs if the Plugin gets deactivated
     * Deletes the shedule of the cron job
     *
     * @return void
     */
    public function deactivate_plugin()
    {
        wp_clear_scheduled_hook('nm_cron_event');
    }
    /**
     * Creates a new Page with the shortcode [notify_me_manage_subscription] as its content
     * Checks if the page is already set in options. Runs on Plugin activation.
     *
     * @return void
     */
    public function setup_frontend_page()
    {
        $current_page = get_option('notify_me_manage_subscription_page');
        if (empty($current_page) or get_post($current_page) === null) {
            $new_page = array('post_title' => __('Notify Me! - Manage Subscription', 'notify-me'), 'post_status' => 'publish', 'post_type' => 'page', 'post_content' => '[notify_me_manage_subscription]');
            $page = wp_insert_post($new_page);
            update_option('notify_me_manage_subscription_page', $page);
        }
    }
    /**
     * Main Method for managing Subscriptions. Called by shortcode [notify_me_manage_subscription]
     *
     * @return void
     */
    public function manage_subscription()
    {
        $to_do = (isset($_REQUEST['do']) and !empty($_REQUEST['do'])) ? $_REQUEST['do'] : 'none';
        switch ($to_do) {
            case 'unsubscribe':
                return $this->delete_subscription();
                break;
        }
        return;
    }
    /**
     * Removes a subscriber for the subscribers postmeta and notify-me queue
     *
     * @return void
     * @todo Allow to delete only single posts (instead of all)
     * @todo Plural success message
     */
    public function delete_subscription()
    {
        $post_id = (isset($_REQUEST['post']) and !empty($_REQUEST['post'])) ? $_REQUEST['post'] : null;
        $email = (isset($_REQUEST['email']) and !empty($_REQUEST['email'])) ? $_REQUEST['email'] : null;

        if (empty($post_id) or empty($email)) {
            return __('Could not unsubscribe because there are some informations missing.', 'notify-me');
        }
        $db = new notify_me_db;
        $remove_sub = $db->remove_subscriber($post_id, $email);
        if($remove_sub > 0){
            echo sprintf(__('You are successfully unsubscribed to %s posts', 'notify-me'),$remove_sub);
        }
        $db->remove_entry($post_id, $email);
        return;
    }
}
