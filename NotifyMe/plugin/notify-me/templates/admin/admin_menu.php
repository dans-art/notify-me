<?php 
    $admin = new notify_me_admin;
?>
<h1>Notify Me! - <?php echo __('Settings');?></h1>
<form method='POST' action = 'options.php'>
<?php 
    settings_fields( 'notify-me' );
    do_settings_sections( 'notify-me' );
    submit_button( );
 ?>
</form>