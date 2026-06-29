<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
$r = $c->query("SELECT cs.id, c.class, s.section FROM class_sections cs JOIN classes c ON c.id = cs.class_id JOIN sections s ON s.id = cs.section_id WHERE c.id IN (22, 23)");
while($row = $r->fetch_assoc()) {
    echo $row['id'] . ": " . $row['class'] . " - " . $row['section'] . "\n";
}
$c->close();
?>
