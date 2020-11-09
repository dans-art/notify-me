<?php
/**
 * Plugin Name: Notify Me!
 * Template description: Main container and default template
 * Create your own template by putting this into: [your_template]/notify-me/templates/mail/[template.php] (template_en_EN.php)
 * Avaliable Variables: $data['reciver_email','post_id','message'], Methods from notify_me class.
 * Author: DansArt.
 * Author URI: http://dans-art.ch
 *
 */
extract($data);
?>
<?php echo $this->load_mail_template('header', $data); ?>

<body>
    <div id="nm_mail_main">
        <div id="nm_header">
            <h1>Hey!</h1>
        </div>
        <div id="nm_content">
            <?php echo $message; ?>
        </div>
        <?php echo $this->load_mail_template('footer', $data); ?>
    </div>
</body>
</html>