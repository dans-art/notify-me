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
        if(false === update_post_meta($eventIdVal,'nm_user',$emailVal)){
            return __('Error while saving subscriber','notify-me');
        }else{
        return __('Successfully subscribed!','notify-me');
        }
    }
}


?>