<?php
class notify_me_ajax {
     /**
    * Saves new subscriber to the post meta and send a confirmation mail to him
    *
    * @param [int] $pageId -ID of Event / Post 
    * @param [str] $email - Email Adress of subscriber
    * @return str - Success or error message
    * @todo Validate input of user
    */
    public function save_subscriber($pageId,$email){
        //validate
        $pageIdVal = (int) $pageId;
        $emailVal = $email; //Validate this!! To Do!
        $oldSub = get_post_meta($pageIdVal,'nm_subscribers',true);
        $subs = ( is_string( $oldSub ) AND empty( $oldSub ) ) ? array((string)$emailVal) : json_decode( $oldSub );
        $indb = is_integer(array_search($emailVal,$subs, true ));
        if($indb == false){$subs[] = $emailVal; }
        if(json_decode( $oldSub ) === $subs){return __('You are already subscribed!','notify-me');}
        
        //Send confirmation mail
        $send = new notify_me_emailer;
        $msg = sprintf(__('Thanks for subscribing to "%s"!'),get_the_title( $pageId ));
        $send -> set_message_from_template(array('pageId' => $pageIdVal, 'message' => $msg),'default');
        $send -> set_subject(get_option('blogname') . __(' - Subscribed to Post'));
        $send -> set_receiver($emailVal);
        $send -> send_email();

        if(!empty($send -> error)){return implode(',',$send -> error);}

        if(false === update_post_meta($pageIdVal,'nm_subscribers',json_encode($subs))){
            return __('Error while saving subscriber','notify-me');
        }else{
        return __('Successfully subscribed!','notify-me');
        }




    }
}


?>