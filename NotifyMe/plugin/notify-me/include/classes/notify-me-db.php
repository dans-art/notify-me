<?php

class notify_me_db extends notify_me_helper{

    public $tableName = '';

    public function __construct()
    {
        global $wpdb;
        $this -> tableName = $wpdb-> prefix. 'notify_me'; 
    }

    public function update_db(){
        $v = (string) $this -> version;
        switch($v){
            case '0.1':
                if(update_option('nm_version', $this -> version)){
                    $this -> admin_infos[] = __('Notify-Me! was updated to version: ') . '0.1';
                    return true;
                }else{
                    $this -> admin_errors[] = __('Notify-Me! could not update to version: ') . '0.1';
                    return false; 
                }
            break;
        }


    }
    /**
     * Creates the database and set the version to the current plugin version
     * Only works if the version is not already set. Otherwise use update_db
     *
     * @return [bool] true on success, false if nm_version already exists
     */
    public function create_db(){
        if(!empty(get_option( 'nm_version'))){
           return false;
        }

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE ".$this -> tableName." (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        post_id bigint(20) NOT NULL,
        time_sent datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        email varchar(100) NOT NULL,
        changes text NOT NULL,
        to_send tinyint(1) NOT NULL,
        PRIMARY KEY  (id)
        ) ".$charset_collate;

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta( $sql );
        update_option('nm_version', $this -> version);
        return true;
    }

    /**
     * Adds a new entry to the database.
     * 
     * @param [type] $postId - Id of a post
     * @param [type] $email - email of the subscriber
     * @param integer $toSend - If the email has to be send or no.
     * @param [string] $oldContent - Json of the old content eg: {'post_content' : 'Old content of the post'}
     * @return [bool] true on success, false if email is not valid or insert was not successfully.
     * @todo Check if entry with (to_send === 1) exists, and override it instead of adding a new one.
     */
    public function add_entry($postId, $email, $oldContent, $toSend = 1 ){
        global $wpdb;
        $data = array();
        $data['post_id'] = (int) $postId;
        $data['to_send'] = (int) $toSend;
        $data['changes'] = $oldContent;

        if($this -> check_user($postId,$email) === false){
            return false;
        }

        if($this -> is_email($email)){
            $data['email'] = htmlspecialchars($email);
        }else{
            return false;
        }
        if($wpdb -> insert($this -> tableName,$data) !== false){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Updates the 'to_send' field in the database.
     *
     * @param [integer] $entryId - Id of the entry (not Post_id!)
     * @param [bool] $toSend - 1 or 0
     * @return [bool] true on success, false on error
     */
    public function update_to_send_entry($entryId,$toSend){
        global $wpdb;
        $data = array();
        $data['to_send'] = (int) $toSend;
        $data['time_sent'] = date('Y-m-d H:i:s');
        $where = array('id' => $entryId);
       
        if($wpdb -> update($this -> tableName,$data,$where) !== false){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Checks if user is already in the database for this post
     *
     * @param [integer] $postId
     * @param [string] $email - The email to check
     * @return void
     */
    public function check_user($postId, $email){
        global $wpdb;
        if($this -> is_email($email) === false){return false;}
        $query = 'SELECT `id` FROM '.$this -> tableName.' WHERE `post_id` = %s AND `email` = %s';
        $result = $wpdb -> get_results($wpdb -> prepare(
            $query, $postId, $email
        ));
        return (empty($result))?false:true;
    }
   /**
    * Get all the entries from the DB, wich are not sent yet.
    * Returns Object 
    * @return [mixed] false on error, result on success.
    */
    public function get_mails_to_send(){
        global $wpdb;
        $limit = 7;
        $query = 'SELECT `id`, `post_id`, `email`, `changes` FROM '.$this -> tableName.' WHERE `to_send` = 1 ORDER BY `id` ASC LIMIT %d';
        $result = $wpdb -> get_results($wpdb -> prepare(
            $query, $limit
        ));
        return (empty($result))?false:$result;
    }

}

?>