<?php
class notify_me_admin extends notify_me_helper{

    public function __construct(){

        
        
    }

    public function add_settings_section_init(){
        add_settings_section(
            'notify-me-settings-section',
            __( 'Main settings', 'notify-me' ),
            [$this,'main_settings_page'],
            'notify-me'
        );
        add_settings_field(
            'activate-notify-me',
            __( 'Activate Notify-Me!', 'notify-me' ),
            [$this, 'checkbox'],
            'notify-me',
            'notify-me-settings-section'
         );
         register_setting( 'notify-me', 'notify_me_activate' );
    }
    public function main_settings_page(){
       echo __('Those ar the main settings...','notify-me');
    }
    public function checkbox(){
        ?>
        <label for="notify_me_activate"></label>
        <input type="checkbox" id="notify_me_activate" name="notify_me_activate" value="true" <?php echo ( get_option( 'notify_me_activate' ) === 'true')?'checked':'';?>>
        <?php
    }

}

?>