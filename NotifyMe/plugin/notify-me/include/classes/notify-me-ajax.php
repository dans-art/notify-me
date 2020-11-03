<?php

class notify_me_ajax {
     /**
    * Saves new subscriber to the post meta and send a confirmation mail to him
    *
    * @param [int] $postId -ID of Event / Post 
    * @param [str] $email - Email Adress of subscriber
    * @return str - Success or error message
    * @todo Validate input of user
    */
    public function save_subscriber($postId,$email){
        $helper = new notify_me_helper;
        //validate inputs
        if(empty($email)){return $helper -> format_error(__('Please fill all fields!','notify-me'));}
        if(empty($postId)){return $helper -> format_error(__('Error: No Post ID found!','notify-me'));}
        if($helper -> is_email($email) === false){return $helper -> format_error(__('Please enter a valid e-Mail address!','notify-me'));}
        $postIdVal = (int) htmlspecialchars($postId);
        $emailVal = htmlspecialchars($email);

        //Check if already subscribed
        $oldSub = get_post_meta($postIdVal,'nm_subscribers',true);
        $subs = ( is_string( $oldSub ) AND empty( $oldSub ) ) ? array((string)$emailVal) : json_decode( $oldSub );
        $indb = is_integer(array_search($emailVal,$subs, true ));
        if($indb === false){$subs[] = $emailVal; }
        else{return $helper -> format_info(__('You are already subscribed!','notify-me'));}
        
        //Send confirmation mail
        $send = new notify_me_emailer;
        $msg = sprintf(__('Thanks for subscribing to "%s"!'),get_the_title( $postId ));
        $send -> set_message_from_template(array('postId' => $postIdVal, 'message' => $msg),'default');
        $send -> set_subject(get_option('blogname') . __(' - Subscribed to Post'));
        $send -> set_receiver($emailVal);
        $send -> send_email();

        //error handling
        if(!empty($send -> get_errors())){return $helper -> format_error(implode(',',$send -> get_errors()));}

        $subs_no_index = array_values($subs);
        $new_data = json_encode($subs_no_index);
        if(false === update_post_meta($postIdVal,'nm_subscribers',$new_data)){
            return $helper -> format_error(__('Error while saving subscriber','notify-me'));
        }else{
        return $helper -> format_success(__('Successfully subscribed!','notify-me'));
        }




    }
}


?>