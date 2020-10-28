<?php
class notify_me_ajax {
     /**
    * Saves new subscriber to the post meta
    *
    * @param [int] $eventId -ID of Event / Post 
    * @param [str] $email - Email Adress of subscriber
    * @return str - Message
    */
    public function save_subscriber($eventId,$email){
        //validate
        $eventIdVal = (int) $eventId;
        $emailVal = $email; //Validate this!! To Do!
        $oldSub = get_post_meta($eventIdVal,'nm_subscribers',true);
        $subs = ( is_string( $oldSub ) AND empty( $oldSub ) ) ? array((string)$emailVal) : json_decode( $oldSub );
        $indb = is_integer(array_search($emailVal,$subs, true ));
        if($indb == false){$subs[] = $emailVal; }
        if(json_decode( $oldSub ) === $subs){return __('You are already subscribed!','notify-me');}
        if(false === update_post_meta($eventIdVal,'nm_subscribers',json_encode($subs))){
            return __('Error while saving subscriber','notify-me');
        }else{
        return __('Successfully subscribed!','notify-me');
        }
    }
}


?>