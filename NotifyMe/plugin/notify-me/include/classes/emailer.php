<?php 
class notify_me_emailer{

    private $subject = "";
    private $message = "";
    private $reciver = "";
    private $sender = "";
    public $error = array();
    
    public function __construct(){
        //Temp
        $this -> set_sender("spy015@gmail.com");
        $this -> set_receiver("spy015@gmail.com");
    }

    public function set_subject($subject)
    {
        $this->subject = $subject;
    }

    public function set_message($message)
    {
        $this->message = $message;
    }

    public function set_receiver($receiver)
    {
        $this->receiver = $receiver;
    }

    public function set_sender($sender)
    {
        $this->sender = $sender;
    }

    public function send_email()
    {
        //reset errors
        $this -> error = array();
        //Try to send...
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= "From: " . $this->sender . "\r\n";
        $headers .= "Reply-to:" . $this->sender . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

        if (@wp_mail($this->receiver,  $this->betreff,  $this->nachricht, $headers)) {
            return true;
        } else {
            if (isset(error_get_last()['message'])){
                $this -> error[] = error_get_last()['message'];
            }
            return false;
        }
    }
}