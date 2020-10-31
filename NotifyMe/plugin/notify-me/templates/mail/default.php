<?php 
 extract($data);
?>
Hallo!<br/> 
<hr/>
<?php echo $message;?>
<br/>
<a href='<?php echo get_permalink( $pageId );?>'><?php echo get_the_title( $pageId );?></a>
<hr/>
Gesendet über Notify Me!