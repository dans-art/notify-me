<?php 
 //extract($data);
 $changes = (isset($data['changes']))?$data['changes']:array();
 $post_id = (isset($data['post_id']))?$data['post_id']:null;
?>
Changes are made to the Post!<br/> 
Post ID: <?php echo $post_id;?>
<hr/>
<?php 
foreach($data['changes'] as $k => $vs){
    echo $k;
    echo '<br/>';
    echo $vs[0] . ' - '.$vs[1];
    echo '<br/>';
}
?>