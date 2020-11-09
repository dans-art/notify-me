<?php
/**
 * Plugin Name: Notify Me!
 * Template description: Main Button. Don't forget to include the JS Scripts.
 * This is automated by calling the notify_me -> add_button() method.
 * Create your own template by putting this into: [your_template]/notify-me/templates/[template.php] (template_en_EN.php)
 * Avaliable Variables: $button_text, Methods from notify_me class.
 * Author: DansArt.
 * Author URI: http://dans-art.ch
 *
 */
$button_text = (isset($button_text)) ? $button_text : __('Subscribe to this Post','notify-me');
$button_text = (isset($data['button_text'])) ? $data['button_text'] : $button_text;
?>
<div class="notify-me container">
<span class="notify-me email" ><input type="email" /></span>
<span class="notify-me button" data-post_id="<?php echo get_the_ID();?>">+  <?php echo $button_text;?> </span>
<span class="notify-me return"></span>
</div>