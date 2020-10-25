<?php
/**
 * Plugin Name: The Events Calendar - Notify Me
 * Description: Notify Me! keeps you updated when a event changes.
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

//Include JS Script


$nm = new notify_me();
$nm -> add_button();
