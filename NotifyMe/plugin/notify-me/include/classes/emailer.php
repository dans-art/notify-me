<?php 
/**
 * Class for sending and preparing emails
 */
class notify_me_emailer extends notify_me{

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
    /**
     * Set the Subject of the Message
     *
     * @param [string] $subject - Text to display in the subject of the email
     * @return void
     */
    public function set_subject($subject)
    {
        $this->subject = (string) $subject;
    }
    /**
     * Set the Message of the email
     *
     * @param [string] $message - Main Message for the Email
     * @return void
     */
    public function set_message($message)
    {
        $this->message = $message;
    }
    /**
     * Set the message by using a template.
     * Plugin Folder: /templates/mail/[language].php - Default: default.php
     *
     * @param [mixed] $data - Can be String or Array, anything you wanna deliver to the Template
     * @param [string] $template - the name of the Templatefile (without .php) - Default: the current launguage code.
     * @return void
     */
    public function set_message_from_template($data,$template = 'default')
    {
        $file = ($template === 'default')?get_locale():$template;
        $tmp = $this -> get_template($file,'templates/mail/');
        if($tmp === false){$tmp = $this -> get_template($template,'templates/mail/');}
        
        if(!$tmp){$this -> message = __('Error while getting the Template file','notify-me');}
        $data = (is_string($data))?array('data' => $data):$data;
        $this->message = $this -> load_template($tmp,$data);
    }
    /**
     * Set the reciver of the email
     *
     * @param [string] $reciver - a valid email adress
     * @return void
     */
    public function set_receiver($reciver)
    {
        $this->reciver = $reciver;
    }
    /**
     * Set the sender of the email
     *
     * @param [string] $sender - a valid email adress
     * @return void
     */
    public function set_sender($sender)
    {
        $this->sender = $sender;
    }
    /**
     * Returns the errors
     *
     * @return [mixed] - Default: Array
     * @todo Format error. Output as String or Array
     */
    public function get_errors(){
        return $this -> error;
    }

    /**
     * Sends the email. Make shure that sender, reciver, subject and message are set before.
     *
     * @return [bool] true or false
     */
    public function send_email()
    {
        if(empty($this -> sender) OR empty($this -> reciver) OR empty($this -> subject) OR empty($this -> message)){
            $this -> error[] = 'Some of the required options are not set.';
            return false;
        }
        //reset errors
        $this -> error = array();
        //Try to send...
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= "From: " . $this->sender . "\r\n";
        $headers .= "Reply-to:" . $this->sender . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

        if (@wp_mail($this->reciver,  $this->subject,  $this->message, $headers)) {
            return true;
        } else {
            if (isset(error_get_last()['message'])){
                $this -> error[] = error_get_last()['message'];
            }
            return false;
        }
    }
}