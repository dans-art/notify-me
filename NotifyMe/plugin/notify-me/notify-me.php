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

//require_once('wp-load.php');
$nm = new notify_me();

//$nmdb = new notify_me_db;

//s($nmdb -> add_entry('2254','spy15@bluewin.ch','{null : null}',0));
//wp_mail('spy015@gmail.com',  'test',  'hi');
//s($nm -> send_mails());

//add_action( 'admin_notices', [$nmdb, 'get_admin_errors']);
//add_action( 'admin_notices', [$nmdb, 'get_admin_infos']);