/*
  Project: The Events Calendar - Notify Me!
  Author: Dansart
 */

import * as tools from './modules/tools.js';
//import overlay from './modules/overlay.js';

/*var overlayName = "44562";
var ov = new overlay(overlayName);

var imgBtn = document.getElementsByClassName("wp-block-image");
//Add Event listener
for (var i = 0; i < imgBtn.length; i++) {
  imgBtn[i].addEventListener('click',function(){
    var currentPos = window.scrollY;
    ov.renderOverlay(this);
    document.getElementById("overlay_"+overlayName).getElementsByClassName("button_close")[0].addEventListener("click",function(){
      ov.closeOverlay(currentPos)});
  });

  //Hover effect
  imgBtn[i].addEventListener('mouseover',function(){this.getElementsByTagName("img")[0].classList.add("transition-img");});
  imgBtn[i].addEventListener('mouseout',function(){this.getElementsByTagName("img")[0].classList.remove("transition-img");});
}*/
tools.s("hi0");
set_subscriber();
function set_subscriber(){
  var data = {
    action: 'nm-ajax',
    do: 'save',
    eventid: '1',
    email: 'spy15@bluewin.ch',
  };

  jQuery(document).ready(function($) {
      $.post(wp_site_url + '/wp-admin/admin-ajax.php', data, function(response) {
      // alert('Got this from the server: ' + response);
      tools.s(response);      
    });
  });
  }




/*
jQuery(document).ready(function($) {
  $('.myajax').click(function(){
    //alert(1);
    var mydata = $(this).data();
    //var termID= $('#locinfo').val();
    $('#wpajaxdisplay').html('<div style="text-align:center;"><img src="<?php echo get_template_directory_uri(); ?>/images/bx_loader.gif" /></div>');
    //console.log(mydata);
      var data = {
          action: 'custom_action',
          //whatever: 1234,
          id: mydata.id
      };

      $.post(wp_site_url + '/wp-admin/admin-ajax.php', data, function(response) {
         // alert('Got this from the server: ' + response);
         $('#wpajaxdisplay').html(response);      
      });
  });
});
*/

