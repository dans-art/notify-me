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
        //validate
        if(empty($postId) OR empty($email)){return __('Please fill all fields!','notify-me');}
        $postIdVal = (int) $postId;
        $emailVal = $email; //Validate this!! To Do!
        $oldSub = get_post_meta($postIdVal,'nm_subscribers',true);
        $subs = ( is_string( $oldSub ) AND empty( $oldSub ) ) ? array((string)$emailVal) : json_decode( $oldSub );
        $indb = is_integer(array_search($emailVal,$subs, true ));
        if($indb == false){$subs[] = $emailVal; }
        if(json_decode( $oldSub ) === $subs){return __('You are already subscribed!','notify-me');}
        
        //Send confirmation mail
        $send = new notify_me_emailer;
        $msg = sprintf(__('Thanks for subscribing to "%s"!'),get_the_title( $postId ));
        $send -> set_message_from_template(array('postId' => $postIdVal, 'message' => $msg),'default');
        $send -> set_subject(get_option('blogname') . __(' - Subscribed to Post'));
        $send -> set_receiver($emailVal);
        $send -> send_email();

        if(!empty($send -> error)){return implode(',',$send -> error);}

        if(false === update_post_meta($postIdVal,'nm_subscribers',json_encode($subs))){
            return __('Error while saving subscriber','notify-me');
        }else{
        return __('Successfully subscribed!','notify-me');
        }




    }
}


?>