<?php
/**
 * Final Migration Script: CM1 to CM2 (Production)
 * This script imports students from CSV, creates users, links fees, and generates assets.
 */

set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configurations
define('PROD_DB_HOST', 'localhost');
define('PROD_DB_USER', 'u707543112_evictoire');
define('PROD_DB_PASS', 'Prodesk@1922');
define('PROD_DB_NAME', 'u707543112_evictoire');

$conn = new mysqli(PROD_DB_HOST, PROD_DB_USER, PROD_DB_PASS, PROD_DB_NAME);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Session and Class Constants
$target_session_id = 22; // 2026-27

// Fee Group FSG IDs (From check_prod_fees_22.php)
$fsg_generaux = 1;
$fsg_standard = 2;
$fsg_legacy = 3;

$csv_files = [
    'CM1_LEGACY_750.csv',
    'CM1_STANDARD_900.csv',
    'CM1_A_VERIFIER.csv'
];

echo "Starting migration...\n";

foreach ($csv_files as $csv_file) {
    if (!file_exists($csv_file)) {
        echo "File $csv_file not found, skipping.\n";
        continue;
    }
    
    echo "Processing $csv_file...\n";
    $handle = fopen($csv_file, 'r');
    $headers = fgetcsv($handle); // Skip headers
    
    while (($data = fgetcsv($handle)) !== FALSE) {
        $admission_no = $data[0];
        $firstname = $data[1];
        $lastname = $data[2];
        $gender = $data[3];
        $dob = $data[4];
        $guardian_name = $data[5];
        $guardian_phone = $data[6];
        $guardian_email = $data[7];
        $guardian_relation = $data[8];
        $category = $data[9];
        $class_section_id = $data[10];

        // 1. Check if student already exists in production
        $check = $conn->query("SELECT id FROM students WHERE admission_no = '$admission_no'");
        if ($check->num_rows > 0) {
            $student_row = $check->fetch_assoc();
            $student_id = $student_row['id'];
            echo "Student $admission_no already exists (ID: $student_id). Updating...\n";
            // Update student info if needed
            $conn->query("UPDATE students SET firstname = '".addslashes($firstname)."', lastname = '".addslashes($lastname)."', gender = '$gender', dob = '$dob', guardian_name = '".addslashes($guardian_name)."', guardian_phone = '$guardian_phone', guardian_email = '$guardian_email', guardian_relation = '$guardian_relation' WHERE id = $student_id");
        } else {
            // New Student
            // Find or Create Parent ID (based on guardian phone)
            $p_check = $conn->query("SELECT parent_id FROM students WHERE guardian_phone = '$guardian_phone' LIMIT 1");
            if ($p_check->num_rows > 0) {
                $parent_id = $p_check->fetch_assoc()['parent_id'];
            } else {
                $max_p = $conn->query("SELECT MAX(parent_id) as max_p FROM students");
                $parent_id = ($max_p->fetch_assoc()['max_p'] ?? 0) + 1;
            }

            $sql = "INSERT INTO students (parent_id, admission_no, firstname, lastname, gender, dob, guardian_name, guardian_phone, guardian_email, guardian_relation, is_active) 
                    VALUES ($parent_id, '$admission_no', '".addslashes($firstname)."', '".addslashes($lastname)."', '$gender', '$dob', '".addslashes($guardian_name)."', '$guardian_phone', '$guardian_email', '$guardian_relation', 'yes')";
            $conn->query($sql);
            $student_id = $conn->insert_id;
            echo "Created Student $admission_no (ID: $student_id)\n";
            
            // Create Users
            $student_user = "std" . $student_id;
            $parent_user = "parent" . $student_id; // Simple for now, ideally links to parent_id if shared
            $password = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 6);
            
            $conn->query("INSERT INTO users (user_id, username, password, role, is_active) VALUES ($student_id, '$student_user', '$password', 'student', 'yes')");
            $conn->query("INSERT INTO users (user_id, username, password, role, is_active) VALUES ($parent_id, '$parent_user', '$password', 'parent', 'yes')");
        }

        // 2. Link to Session/Class
        $ss_check = $conn->query("SELECT id FROM student_session WHERE student_id = $student_id AND session_id = $target_session_id");
        if ($ss_check->num_rows == 0) {
            $conn->query("INSERT INTO student_session (student_id, session_id, class_id, section_id) 
                          SELECT $student_id, $target_session_id, class_id, section_id FROM class_sections WHERE id = $class_section_id");
            $student_session_id = $conn->insert_id;
        } else {
            $student_session_id = $ss_check->fetch_assoc()['id'];
            $conn->query("UPDATE student_session SET class_id = (SELECT class_id FROM class_sections WHERE id = $class_section_id), section_id = (SELECT section_id FROM class_sections WHERE id = $class_section_id) WHERE id = $student_session_id");
        }

        // 3. Assign Fee Groups
        // Always assign Frais G??n??raux
        $conn->query("INSERT IGNORE INTO student_fees_master (student_session_id, fee_session_group_id, is_active) VALUES ($student_session_id, $fsg_generaux, 'yes')");
        
        if ($category == 'LEGACY_750') {
            $conn->query("INSERT IGNORE INTO student_fees_master (student_session_id, fee_session_group_id, is_active) VALUES ($student_session_id, $fsg_legacy, 'yes')");
        } elseif ($category == 'STANDARD_900') {
            $conn->query("INSERT IGNORE INTO student_fees_master (student_session_id, fee_session_group_id, is_active) VALUES ($student_session_id, $fsg_standard, 'yes')");
        }
    }
    fclose($handle);
}

$conn->close();
echo "Migration completed. Next step: Generate QR and Barcodes.\n";
?>
