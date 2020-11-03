<?php
class notify_me_admin extends notify_me_helper
{

    public function __construct()
    {

        if($this -> version_checker() !== true){
            $this -> update_version();
        }
        $this -> check_options();
        add_action( 'admin_notices', [$this, 'get_admin_errors']);
        add_action( 'admin_notices', [$this, 'get_admin_infos']);

    }

    public function add_settings_section_init()
    {
        add_settings_section(
            'notify-me-settings-section',
            __('Main settings', 'notify-me'),
            [$this, 'main_settings_section'],
            'notify-me'
        );
        //Checkbox - Activate Auto placement of Plugin
        add_settings_field(
            'activate-notify-me',
            __('Activate Notify-Me!', 'notify-me'),
            [$this, 'std_input'],
            'notify-me',
            'notify-me-settings-section',
            array(
                'type'         => 'checkbox',
                'option_group' => 'notify_me',
                'name'         => 'notify_me_activate',
                'label_for'    => 'notify_me_activate',
                'description'  => __('Check if you like to include the field in every Page, Post and Event.', 'notify-me'),
                'value'      =>  'true',
                'checked'      => (get_option('notify_me_activate', 'false')  === 'true') ? 'checked' : '',
            )
        );
        //Checkbox - Activate Shortcode functionality
        add_settings_field(
            'activate-shortcode-notify-me',
            __('Activate Notify-Me! Shortcodes', 'notify-me'),
            [$this, 'std_input'],
            'notify-me',
            'notify-me-settings-section',
            array(
                'type'         => 'checkbox',
                'option_group' => 'notify_me',
                'name'         => 'notify_me_activate_sc',
                'label_for'    => 'notify_me_activate_sc',
                'description'  => __('Check if you like to enable the Shortcodes.<br/>Avaliable Shortcodes: [notify_me_button]', 'notify-me'),
                'value'      =>  'true',
                'checked'      => (get_option('notify_me_activate_sc','true')  === 'true') ? 'checked' : '',
            )
        );
        //Input Field - Send From Email
        add_settings_field(
            'email-notify-me',
            __('Send From Email', 'notify-me'),
            [$this, 'std_input'],
            'notify-me',
            'notify-me-settings-section',
            array(
                'type'         => 'email',
                'option_group' => 'notify_me',
                'name'         => 'notify_me_email_from',
                'label_for'    => 'notify_me_email_from',
                'description'  => __('Set the Email-Adress you like to send the emails from', 'notify-me'),
                'value'      => get_option('notify_me_email_from', get_option('admin_email')),
                'checked'      => "",
            )
        );
        register_setting('notify-me', 'notify_me_activate');
        register_setting('notify-me', 'notify_me_activate_sc');
        register_setting('notify-me', 'notify_me_email_from');
        register_setting('notify-me', 'notify_me_manage_subscription_page');
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function main_settings_section()
    {
        echo __('Those are the main settings...', 'notify-me');
    }

    public function std_input($args)
    {
?>
        <label for="<?php echo $args['label_for']; ?>"></label>
        <input type="<?php echo $args['type']; ?>" id="<?php echo $args['label_for']; ?>" name="<?php echo $args['name']; ?>" value="<?php echo $args['value']; ?>" <?php echo $args['checked']; ?>>
        <p><?php echo $args['description']; ?></p>
<?php
    }

    public function check_options(){
        $sub_manage_page = get_option( 'notify_me_manage_subscription_page' );
        if(!get_post($sub_manage_page)){
            $this -> admin_errors[] = __('No Page for Managing Subscriptions set! Please deactivate and activate the Plugin again.','notify-me');
        }
    }
}

?>