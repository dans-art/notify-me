<?php
//extract($data);
$changes = (isset($data['changes'])) ? $data['changes'] : array();
$post_id = (isset($data['post_id'])) ? $data['post_id'] : null;
$reciver_email = (isset($data['reciver_email'])) ? $data['reciver_email'] : null;
$unsubscribe_page = get_option('notify_me_manage_subscription_page');
$the_title = get_the_title($post_id);
?>
Es gibt Änderungen am Beitrag <a href="<?php echo get_permalink($post_id); ?>"><?php echo $the_title; ?></a>, welchen du abonniert hast.<br />
Hier eine Übersicht der Änderungen:<br />
<table id="nm_compare_table">
    <?php
    if (!is_array($changes)) {
        $changes = json_decode($changes);
    }
    foreach ($data['changes'] as $k => $vs) {
        $key_name = $this->get_field_name($k);
        echo '<tr class="nm_table_header"><td>' . __($key_name, 'notify-me') . '</td></tr>';
        echo '<tr><td class="nm_compare_old">' . $vs[0] . '</td><td class="nm_compare_new">' . $vs[1] . '</td></tr>';
    }
    ?>
</table>
<div class="nm_button">
    <a href="<?php echo get_permalink($post_id); ?>">Hier gehts um Beitrag auf <?php echo get_bloginfo('name'); ?></a>
</div>