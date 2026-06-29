<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
$sql = "SELECT fsg.id as fsg_id, fg.name, ft.type, fgft.amount 
        FROM fee_session_groups fsg 
        JOIN fee_groups fg ON fg.id = fsg.fee_groups_id 
        JOIN fee_groups_feetype fgft ON fgft.fee_session_group_id = fsg.id
        JOIN feetype ft ON ft.id = fgft.feetype_id 
        WHERE fsg.session_id = 22";
$r = $c->query($sql);
while($row = $r->fetch_assoc()) {
    echo $row['fsg_id'] . " | " . $row['name'] . " | " . $row['type'] . " | " . $row['amount'] . "\n";
}
$c->close();
?>
