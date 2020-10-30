<?php 
 //extract($data);
 $changes = (isset($data['changes']))?$data['changes']:array();
 $postId = (isset($data['postid']))?$data['postid']:null;
?>
Die änderungen:!<br/> 
Post ID: <?php echo $postId;?>
<hr/>
<?php 
foreach($data['changes'] as $k => $vs){
    echo $k;
    echo '<br/>';
    echo $vs[0] . ' - '.$vs[1];
    echo '<br/>';
}
?>
<br/>
<a href='<?php echo get_permalink( $postId );?>'><?php echo get_the_title( $postId );?></a>
<hr/>