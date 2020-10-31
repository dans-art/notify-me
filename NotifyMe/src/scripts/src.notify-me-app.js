/*
  Project: The Events Calendar - Notify Me!
  Author: Dansart
 */

import * as tools from './modules/tools.js';
//import overlay from './modules/overlay.js';



//set_subscriber();
jQuery(document).ready(function($) {
  $('.notify-me.button').click(function(){
    var mail = jQuery(this).parent().find('input').val();
    var id = jQuery(this).data('postid');
    set_subscriber(id,mail);
  });
 
});

function set_subscriber(pid,em){
  var data = {
    action: 'nm-ajax',
    do: 'save',
    postid: pid,
    email: em,
  };
  jQuery.post(wp_site_url + '/wp-admin/admin-ajax.php', data, function(response) {
    // alert('Got this from the server: ' + response);
    jQuery('span[data-postid="'+pid+'"]').parent().find('.return').html(response);    
  });

  }


