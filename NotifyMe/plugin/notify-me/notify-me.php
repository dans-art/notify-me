<?php
/**
 * Plugin Name: Notify Me!
 * Description: Notify Me! keeps you updated when a post or event changes.
 * Version: 0.1
 * Author: DansArt.
 * Author URI: http://dans-art.ch
 * Text Domain: notify-me
 * License: GPLv2 or later
 *
 */

require_once('include/tools/helper.php');
require_once('include/classes/notify-me.php');
require_once('include/classes/notify-me-ajax.php');
require_once('include/classes/emailer.php');

//Include JS Script


$nm = new notify_me();
$nm -> add_button();

//Temp
add_action( 'wp_loaded', 'cron_time' );

function cron_time(){
    $send = new notify_me_emailer;
    $pageId = get_the_ID();
    $send -> set_message("Send!");
    $send -> set_subject("Test");
    s($send -> send_email());
}

