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

 //Loading the required Classes and tools

require_once('include/tools/helper.php');
require_once('include/classes/notify-me.php');
require_once('include/classes/notify-me-ajax.php');
require_once('include/classes/emailer.php');


$nm = new notify_me();
$nm -> add_button();




