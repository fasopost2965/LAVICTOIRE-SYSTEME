<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_legacy = [
    'host' => 'localhost',
    'user' => 'u707543112_systeme',
    'pass' => '/|098hH7',
    'name' => 'u707543112_systeme'
];

$db_new = [
    'host' => 'localhost',
    'user' => 'u707543112_evictoire',
    'pass' => 'Prodesk@1922',
    'name' => 'u707543112_evictoire'
];

$conn_legacy = new mysqli($db_legacy['host'], $db_legacy['user'], $db_legacy['pass'], $db_legacy['name']);
$conn_new = new mysqli($db_new['host'], $db_new['user'], $db_new['pass'], $db_new['name']);

if ($conn_legacy->connect_error) die("Legacy failed");
if ($conn_new->connect_error) die("New failed");

$session_id_new = 22;
$section_id_new = 2;

$new_cols = [];
$res_cols = $conn_new->query("SHOW COLUMNS FROM students");
while ($row = $res_cols->fetch_assoc()) {
    $new_cols[$row['Field']] = $row;
}

$new_user_cols = [];
$res_user_cols = $conn_new->query("SHOW COLUMNS FROM users");
while ($row = $res_user_cols->fetch_assoc()) {
    $new_user_cols[$row['Field']] = $row;
}

function migrate_csv($filename, $fee_session_group_id, $conn_legacy, $conn_new, $session_id_new, $section_id_new, $new_cols, $new_user_cols) {
    $path = dirname(__FILE__) . '/' . $filename;
    if (!file_exists($path)) return 0;
    $handle = fopen($path, "r");
    fgetcsv($handle); 
    $count = 0;

    while (($data = fgetcsv($handle)) !== FALSE) {
        $admission_no = trim($data[0]);
        $target_class = trim($data[4]);
        
        $class_res = $conn_new->query("SELECT id FROM classes WHERE class = '$target_class'");
        $class_row = $class_res->fetch_assoc();
        if (!$class_row) continue;
        $class_id_new = $class_row['id'];

        $res = $conn_legacy->query("SELECT * FROM students WHERE admission_no = '$admission_no'");
        $student_data = $res->fetch_assoc();
        
        if ($student_data) {
            $old_student_id = $student_data['id'];
            $old_parent_id = $student_data['parent_id'];

            // 1. Insert Student
            $fields = [];
            $values = [];
            foreach ($student_data as $key => $val) {
                if ($key == 'id' || $key == 'created_at' || $key == 'updated_at') continue;
                if (!isset($new_cols[$key])) continue;
                
                $fields[] = "`$key`";
                if ($key == 'parent_id') {
                     $values[] = 0; // Will update later
                } elseif ($val === NULL) {
                    $values[] = ($new_cols[$key]['Null'] == 'NO') ? "''" : "NULL";
                } else {
                    $values[] = "'" . $conn_new->real_escape_string($val) . "'";
                }
            }
            
            $sql = "INSERT INTO students (" . implode(',', $fields) . ") VALUES (" . implode(',', $values) . ")";
            if ($conn_new->query($sql)) {
                $new_student_id = $conn_new->insert_id;
                
                // 2. Insert Student Session
                $conn_new->query("INSERT INTO student_session (student_id, session_id, class_id, section_id, is_active) 
                                 VALUES ($new_student_id, $session_id_new, $class_id_new, $section_id_new, 'yes')");
                $new_ss_id = $conn_new->insert_id;
                
                // 3. Assign Fees
                $conn_new->query("INSERT INTO student_fees_master (student_session_id, fee_session_group_id, is_active) 
                                     VALUES ($new_ss_id, $fee_session_group_id, 'yes')");

                // 4. Migrate Users (Student)
                $user_res = $conn_legacy->query("SELECT * FROM users WHERE user_id = $old_student_id AND role = 'student'");
                if ($user_row = $user_res->fetch_assoc()) {
                    $u_fields = []; $u_values = [];
                    foreach ($user_row as $ukey => $uval) {
                        if ($ukey == 'id' || $ukey == 'created_at' || $ukey == 'updated_at') continue;
                        if (!isset($new_user_cols[$ukey])) continue;
                        $u_fields[] = "`$ukey`";
                        if ($ukey == 'user_id') {
                            $u_values[] = $new_student_id;
                        } elseif ($uval === NULL) {
                            $u_values[] = ($new_user_cols[$ukey]['Null'] == 'NO') ? "''" : "NULL";
                        } else {
                            $u_values[] = "'" . $conn_new->real_escape_string($uval) . "'";
                        }
                    }
                    $conn_new->query("INSERT INTO users (" . implode(',', $u_fields) . ") VALUES (" . implode(',', $u_values) . ")");
                }

                // 5. Migrate Users (Parent) and Link back to Student
                $parent_res = $conn_legacy->query("SELECT * FROM users WHERE id = $old_parent_id AND role = 'parent'");
                if ($parent_row = $parent_res->fetch_assoc()) {
                    $p_username = $parent_row['username'];
                    $p_check = $conn_new->query("SELECT id, childs FROM users WHERE username = '$p_username' AND role = 'parent'");
                    if ($p_match = $p_check->fetch_assoc()) {
                        $new_parent_user_id = $p_match['id'];
                        $current_childs = $p_match['childs'];
                        $new_childs = $current_childs ? $current_childs . ',' . $new_student_id : $new_student_id;
                        $conn_new->query("UPDATE users SET childs = '$new_childs' WHERE id = $new_parent_user_id");
                    } else {
                        $p_fields = []; $p_values = [];
                        foreach ($parent_row as $pkey => $pval) {
                            if ($pkey == 'id' || $pkey == 'created_at' || $pkey == 'updated_at') continue;
                            if (!isset($new_user_cols[$pkey])) continue;
                            $p_fields[] = "`$pkey`";
                            if ($pkey == 'user_id') {
                                $p_values[] = $new_student_id;
                            } elseif ($pkey == 'childs') {
                                $p_values[] = "'$new_student_id'";
                            } elseif ($pval === NULL) {
                                $p_values[] = ($new_user_cols[$pkey]['Null'] == 'NO') ? "''" : "NULL";
                            } else {
                                $p_values[] = "'" . $conn_new->real_escape_string($pval) . "'";
                            }
                        }
                        $conn_new->query("INSERT INTO users (" . implode(',', $p_fields) . ") VALUES (" . implode(',', $p_values) . ")");
                        $new_parent_user_id = $conn_new->insert_id;
                    }
                    
                    // 6. Update student.parent_id with the new parent user ID
                    $conn_new->query("UPDATE students SET parent_id = $new_parent_user_id WHERE id = $new_student_id");
                }
                
                $count++;
            }
        }
    }
    fclose($handle);
    return $count;
}

$c1 = migrate_csv('CP_STANDARD_900.csv', 2, $conn_legacy, $conn_new, $session_id_new, $section_id_new, $new_cols, $new_user_cols);
$c2 = migrate_csv('CP_LEGACY_750.csv', 3, $conn_legacy, $conn_new, $session_id_new, $section_id_new, $new_cols, $new_user_cols);

echo "Full Migration (Linked): $c1 Standard and $c2 Legacy students.\n";
?>
