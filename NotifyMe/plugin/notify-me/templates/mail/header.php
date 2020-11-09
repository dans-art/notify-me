<?php
/**
 * Plugin Name: Notify Me!
 * Template description: Header for the eMails
 * Create your own template by putting this into: [your_template]/notify-me/templates/mail/[template.php] (template_en_EN.php)
 * Avaliable Variables: $data['reciver_email','post_id'], Methods from notify_me class.
 * Author: DansArt.
 * Author URI: http://dans-art.ch
 *
 */
extract($data);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo get_the_title($post_id);?></title>
  <style>
    <?php include_once($this->nm_get_stylesheet('mail')); ?>
</style>
</head>