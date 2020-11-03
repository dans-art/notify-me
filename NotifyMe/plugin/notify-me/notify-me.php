<?php

/**
 * Plugin Name: Notify Me!
 * Description: Notify Me! keeps you updated when a post or event changes.
 * Version: 0.1
 * Author: DansArt.
 * Author URI: http://dans-art.ch
 * Text Domain: notify-me
 * Domain Path: /languages
 * License: GPLv2 or later
 *
 */


require_once('include/tools/helper.php');
require_once('include/classes/notify-me.php');
require_once('include/classes/notify-me-admin.php');
require_once('include/classes/notify-me-ajax.php');
require_once('include/classes/notify-me-db.php');
require_once('include/classes/emailer.php');

$nm = new notify_me();


/**
 * Testing AREA
 */
add_action( 'wp_loaded','nm_run' );
function nm_run() {
    global $nm;
    //$db = new notify_me_db;
    //s($db -> remove_subscriber('all','test@dans-art.ch'));
    //$nm -> setup_frontend_page();
}

