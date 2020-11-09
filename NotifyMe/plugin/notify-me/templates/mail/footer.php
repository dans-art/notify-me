<?php
/**
 * Plugin Name: Notify Me!
 * Template description: Footer for the eMails
 * Create your own template by putting this into: [your_template]/notify-me/templates/mail/[template.php] (template_en_EN.php)
 * Avaliable Variables: $data['reciver_email','post_id'], Methods from notify_me class.
 * Author: DansArt.
 * Author URI: http://dans-art.ch
 *
 */
 extract($data); 
 ?>
<div id="nm_footer">
        Du willst keine Mails mehr erhalten?<br />
        <?php echo $this->get_unsubscribe_link($reciver_email, $post_id); ?> - <?php echo $this->get_unsubscribe_link($reciver_email); ?><br />
        Powered by <a href="">Notify Me!</a>
    </div>