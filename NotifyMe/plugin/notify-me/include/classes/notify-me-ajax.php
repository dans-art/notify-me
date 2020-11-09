<?php

/**
 * Plugin Name: Notify Me!
 * Class description: The Ajax functions. Saves Posts to the database.
 * Author: DansArt.
 * Author URI: http://dans-art.ch
 *
 */

class notify_me_ajax {
     /**
    * Saves new subscriber to the post meta and send a confirmation mail to him
    *
    * @param [int] $post_id -ID of Event / Post 
    * @param [str] $email - Email Adress of subscriber
    * @return str - Success or error message
    * @todo Validate input of user
    */
    public function save_subscriber($post_id,$email){
        $helper = new notify_me_helper;
        $send = new notify_me_emailer;

        //validate inputs
        if(empty($email)){return $helper -> format_error(__('Please fill all fields!','notify-me'));}
        if(empty($post_id)){return $helper -> format_error(__('Error: No Post ID found!','notify-me'));}
        if($helper -> is_email($email) === false){return $helper -> format_error(__('Please enter a valid e-Mail address!','notify-me'));}
        $post_id_validated = (int) htmlspecialchars($post_id);
        $email_validated = htmlspecialchars($email);

        //Check if already subscribed
        $oldSub = get_post_meta($post_id_validated,'nm_subscribers',true);
        $subs = ( is_string( $oldSub ) AND empty( $oldSub ) ) ? array() : json_decode( $oldSub );
        $indb = is_integer(array_search($email_validated,$subs, true ));
        if($indb === false){$subs[] = $email_validated; }
        else{return $helper -> format_info(__('You are already subscribed!','notify-me'));}
        
        //Send confirmation mail
        $send -> send_confirmation_mail($post_id_validated,$email_validated);

        //error handling
        if(!empty($send -> get_errors())){return $helper -> format_error(implode(',',$send -> get_errors()));}

        $subs_no_index = array_values($subs);
        $new_data = json_encode($subs_no_index);
        if(false === update_post_meta($post_id_validated,'nm_subscribers',$new_data)){
            return $helper -> format_error(__('Error while saving subscriber','notify-me'));
        }else{
        return $helper -> format_success(__('Successfully subscribed!','notify-me'));
        }

    }
}


?>