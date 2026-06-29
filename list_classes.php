<?php
$c = new mysqli('localhost', 'u707543112_evictoire', 'Prodesk@1922', 'u707543112_evictoire');
$sql = "SELECT classes.id as class_id, classes.class, sections.id as section_id, sections.section 
        FROM class_sections 
        JOIN classes ON classes.id = class_sections.class_id 
        JOIN sections ON sections.id = class_sections.section_id
        ORDER BY classes.class, sections.section";
$r = $c->query($sql);
while($row = $r->fetch_assoc()) {
    echo $row['class'] . ' ' . $row['section'] . ' (ClassID: ' . $row['class_id'] . ', SectionID: ' . $row['section_id'] . ")\n";
}
$c->close();
?>
