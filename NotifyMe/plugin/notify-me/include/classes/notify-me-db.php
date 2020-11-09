<?php
/**
 * Plugin Name: Notify Me!
 * Class description: Class for adding, removing and modifing Notify-Me Database entries. 
 * Author: DansArt.
 * Author URI: http://dans-art.ch
 *
 */
class notify_me_db extends notify_me_helper{

    public $table_name = '';

    /**
     * Sets the table_name
     */
    public function __construct()
    {
        global $wpdb;
        $this -> table_name = $wpdb-> prefix. 'notify_me'; 
    }

    /**
     * Updates the database. Checks for the version stored in the DB.
     *
     * @return void
     */
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

        $sql = "CREATE TABLE ".$this -> table_name." (
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
     * Adds a new entry to the database / queue.
     * Overrides a existing entry with the same post_id and email if there are to_send === 1
     * 
     * @param [integer] $post_id - Id of a post
     * @param [string] $email - email of the subscriber
     * @param [integer] $to_send - If the email has to be send or no.
     * @param [string] $old_content - Json of the old content eg: {'post_content' : 'Old content of the post'}
     * @return [bool] true on success, false if email is not valid or insert was not successfully.
     */
    public function add_entry($post_id, $email, $old_content, $to_send = 1 ){
        global $wpdb;
        $data = array();
        $data['post_id'] = (int) $post_id;
        $data['to_send'] = (int) $to_send;
        $data['changes'] = $old_content;
        
        if($this -> is_email($email)){
            $data['email'] = htmlspecialchars($email);
        }else{
            return false;
        }
        $where = array('email' => $data['email'], 'post_id' => $data['post_id'], 'to_send' => 1);
        
        //Try to update, if fails, try to insert
        if($wpdb -> update($this -> table_name, $data, $where) > 0){
            return true;
        }else if($wpdb -> insert($this -> table_name, $data) !== false){
            return true;
        }else{
            return false;
        }
    }
    /**
     * Removes a entry form the database / queue. Best use together with remove_subscriber
     *
     * @param [mixed] $post_id - Post Id to remove a individual entry or 'all' to remove all entries with the email set.
     * @param [string] $email - email of the subscriber
     * @return [bool] true on success, false if email is not valid or insert was not successfully.
     */
    public function remove_entry($post_id,$email){
        global $wpdb;
        if($this -> is_email($email)){
            $email = htmlspecialchars($email);
        }else{
            return __('Invalid email','notify-me');
        }
        if($post_id === 'all'){
            $where = array('email' => $email);
        }else{
            $where = array('email' => $email, 'post_id' => (int) $post_id);
        }
        $deleted = $wpdb -> delete($this -> table_name, $where);
        if( $deleted > 0){
            return $deleted;
        }else{
            return 0;
        }
    }

    /**
     * Removes the Subscriber from the Postmeta. So he will not get notified anymore on post change.
     * Best use together with remove_entry
     *
     * @param [type] $post_id
     * @param [type] $email
     * @return void
     */
    public function remove_subscriber($post_id,$email){
        global $wpdb;
        if($this -> is_email($email)){
            $email = htmlspecialchars($email);
        }else{
            return __('Invalid email','notify-me');
        }
        $like = '%'.$email.'%';
        $where_and = ($post_id === 'all')?'':' AND `post_id` = '.(int) $post_id;
        $query = 'SELECT `post_id`, `meta_value` FROM '.$wpdb -> prefix .'postmeta WHERE `meta_key` = %s AND `meta_value` LIKE %s %s';
        $result = $wpdb -> get_results($wpdb -> prepare(
            $query, 'nm_subscribers' ,$like, $where_and
        ));
        if(!empty($result)){
            $count = 0;
            foreach($result as $obj){
                if(isset($obj -> meta_value)){
                    $to_arr = json_decode($obj -> meta_value);
                    if(is_array($to_arr)){
                        $new_value = array_diff($to_arr,[$email]);
                        $new_value_no_index = array_values($new_value);
                        $new_data = json_encode($new_value_no_index);
                        if(update_post_meta($obj -> post_id,'nm_subscribers',$new_data)){
                            $count++;
                        }
                    }
                }
            }
            return $count;
        }else{
            return 0;
        }
    }

    /**
     * Updates the 'to_send' field in the database.
     *
     * @param [integer] $entry_id - Id of the entry (not Post_id!)
     * @param [bool] $to_send - 1 or 0
     * @return [bool] true on success, false on error
     */
    public function update_to_send_entry($entry_id,$to_send){
        global $wpdb;
        $data = array();
        $data['to_send'] = (int) $to_send;
        $data['time_sent'] = date('Y-m-d H:i:s');
        $where = array('id' => $entry_id);
       
        if($wpdb -> update($this -> table_name,$data,$where) !== false){
            return true;
        }else{
            return false;
        }
    }

   /**
    * Get all the entries from the DB, wich are not sent yet.
    * Returns Object 
    * @return [mixed] false on error, result on success.
    * @todo Add "limit" option to options page 
    */
    public function get_mails_to_send(){
        global $wpdb;
        $limit = 7;
        $query = 'SELECT `id`, `post_id`, `email`, `changes` FROM '.$this -> table_name.' WHERE `to_send` = 1 ORDER BY `id` ASC LIMIT %d';
        $result = $wpdb -> get_results($wpdb -> prepare(
            $query, $limit
        ));
        return (empty($result))?false:$result;
    }

}

?>