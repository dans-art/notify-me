<?php 
 //extract($data);
 $changes = (isset($data['changes']))?$data['changes']:array();
 $postId = (isset($data['postid']))?$data['postid']:null;
 $reciver_email = (isset($data['reciver_email']))?$data['reciver_email']:null;
 $unsubscribe_page = get_option('notify_me_manage_subscription_page');
 $the_title = get_the_title( $postId );
?>
<style>
    <?php include_once($this -> nm_get_stylesheet('mail')); ?>
</style>
<div id="nm_mail_main">
<div id="nm_header">
<h1>Hey!</h1>
Es gibt Änderungen am  Beitrag "<a href="<?php echo get_permalink( $postId );?>"><?php echo $the_title;?></a>", welchen du abonniert hast.
</div>
    <div id="nm_content">
        Hier eine Übersicht der Änderungen:<br/> 
        <table>
        <?php
        if(!is_array($changes)){$changes = json_decode($changes);} 
        foreach($data['changes'] as $k => $vs){
            $key_name = $this -> get_field_name($k);
            echo '<tr><td>'.__($key_name,'notify-me').'</td></tr>';
            echo '<tr><td>'.$vs[0].'</td><td>'.$vs[1].'</td></tr>';

        }
        ?>
        </table>
       
    </div>
    <div id="nm_footer">
        Du willst keine Mails mehr erhalten?<br/>
        <?php echo $this -> get_unsubscribe_link($reciver_email,$postId);?><br/>
        <?php echo $this -> get_unsubscribe_link($reciver_email);?><br/>
        Powered by <a href="">Notify Me!</a>
    </div>
</div>
