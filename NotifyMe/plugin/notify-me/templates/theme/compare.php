<?php 
 //extract($data);
 $changes = (isset($data['changes']))?$data['changes']:array();
 $postId = (isset($data['postid']))?$data['postid']:null;
?>
Changes are made to the Post<br/> 
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