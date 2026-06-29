<?php
// analyze_migration_v2.php

$host = 'localhost';
$user = 'u707543112_systeme';
$pass = '/|098hH7';
$db   = 'u707543112_systeme';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");

$session_id = 21; // 2025-26

$level_progression = [
    '6ième Année' => '7ième Année',
    'CM2' => '6ième Année',
    'CM1' => 'CM2',
    'CE2' => 'CM1',
    'CP' => 'CE1',
    'CE1' => 'CE2',
];

$maternelle_classes = ['GS', 'MS', 'PS', 'Crèche', 'Maternelle'];

// Find feetype IDs for "Scolarité" by name
$fee_types = [];
$res = $conn->query("SELECT id, type FROM feetype WHERE type LIKE '%scolarité%' OR type = 'SCOLARITE' OR code LIKE 'sc-%' OR code = 'FS'");
while ($row = $res->fetch_assoc()) {
    $fee_types[] = $row['id'];
}
$fee_types_list = implode(',', $fee_types);

// Prepare CSV handles
$h_900 = fopen('MIGRATION_STANDARD_900.csv', 'w');
$h_750 = fopen('MIGRATION_LEGACY_750.csv', 'w');
$h_mat = fopen('MIGRATION_MATERNELLE.csv', 'w');
$h_ver = fopen('MIGRATION_A_VERIFIER.csv', 'w');

$header = ['admission_no', 'firstname', 'lastname', 'ancienne_classe', 'nouvelle_classe', 'tarif_base_detecte', 'montant_paye_moyen', 'type_remise', 'nombre_mois_analysés', 'statut'];
$header_ver = array_merge($header, ['raison']);

fputcsv($h_900, $header);
fputcsv($h_750, $header);
fputcsv($h_mat, $header);
fputcsv($h_ver, $header_ver);

// Fetch students
$sql = "SELECT s.id as student_id, s.admission_no, s.firstname, s.lastname, c.class as old_class, ss.id as student_session_id
        FROM students s
        JOIN student_session ss ON s.id = ss.student_id
        JOIN classes c ON ss.class_id = c.id
        WHERE ss.session_id = $session_id";

$res_students = $conn->query($sql);

$counters = ['900' => 0, '750' => 0, 'MAT' => 0, 'VER' => 0];
$group_counters = [];

while ($s = $res_students->fetch_assoc()) {
    $old_class = trim($s['old_class']);
    $admission_no = $s['admission_no'];
    $student_id = $s['student_id'];
    $student_session_id = $s['student_session_id'];
    
    // 1. Detect Base Rate from Fee Master
    $base_rate = 0;
    $sql_base = "SELECT MAX(fgf.amount) as base_rate
                 FROM student_fees_master sfm
                 JOIN fee_groups_feetype fgf ON sfm.fee_session_group_id = fgf.fee_session_group_id
                 WHERE sfm.student_session_id = $student_session_id
                 AND fgf.feetype_id IN ($fee_types_list)";
    $res_base = $conn->query($sql_base);
    if ($res_base) {
        $row_base = $res_base->fetch_assoc();
        $base_rate = floatval($row_base['base_rate']);
    }
    
    // 2. Analyze Payments (for average and discount detection)
    $paid_amounts = [];
    $sql_fees = "SELECT amount_detail 
                 FROM student_fees_deposite 
                 WHERE student_fees_master_id IN (
                     SELECT id FROM student_fees_master WHERE student_session_id = $student_session_id
                 )
                 AND fee_groups_feetype_id IN (
                     SELECT fgf.id FROM fee_groups_feetype fgf WHERE fgf.feetype_id IN ($fee_types_list)
                 )
                 ORDER BY created_at DESC LIMIT 10";
    
    $res_fees = $conn->query($sql_fees);
    if ($res_fees) {
        while ($f = $res_fees->fetch_assoc()) {
            $detail = json_decode($f['amount_detail'], true);
            if (is_array($detail)) {
                foreach ($detail as $d) {
                    if (isset($d['amount']) && floatval($d['amount']) > 100) {
                        $paid_amounts[] = floatval($d['amount']);
                    }
                }
            }
        }
    }
    
    $avg_paid = 0;
    $count_months = count($paid_amounts);
    if ($count_months > 0) {
        $avg_paid = array_sum($paid_amounts) / $count_months;
        // Use mode for better reliability on regular payments
        $freqs = array_count_values(array_map('strval', $paid_amounts));
        arsort($freqs);
        $avg_paid = floatval(key($freqs));
    }
    
    // 3. Detect Discount Type
    $type_remise = 'aucune';
    
    // Check explicit discounts in DB
    $sql_disc = "SELECT fd.name 
                 FROM student_fees_discounts sfd
                 JOIN fees_discounts fd ON sfd.fees_discount_id = fd.id
                 WHERE sfd.student_session_id = $student_session_id AND sfd.is_active = 'yes'";
    $res_disc = $conn->query($sql_disc);
    if ($res_disc && $res_disc->num_rows > 0) {
        $disc_row = $res_disc->fetch_assoc();
        $disc_name = strtolower($disc_row['name']);
        if (strpos($disc_name, 'personnel') !== false || strpos($disc_name, 'prof') !== false || strpos($disc_name, 'staff') !== false) {
            $type_remise = 'personnel';
        } else {
            $type_remise = 'autre';
        }
    }
    
    // Logic detection based on amounts
    if ($type_remise == 'aucune' && $base_rate > 0 && $avg_paid > 0) {
        $ratio = $avg_paid / $base_rate;
        if ($ratio >= 0.45 && $ratio <= 0.55) {
            $type_remise = 'personnel';
        } elseif ($base_rate == 900 && ($avg_paid >= 800 && $avg_paid <= 820)) {
            $type_remise = 'annuel';
        } elseif ($avg_paid < ($base_rate - 50)) {
            $type_remise = 'autre';
        }
    }
    
    // 4. Mapping & Export
    $is_maternelle = false;
    foreach ($maternelle_classes as $m) {
        if (stripos($old_class, $m) !== false) {
            $is_maternelle = true;
            break;
        }
    }
    
    if ($is_maternelle) {
        fputcsv($h_mat, [$admission_no, $s['firstname'], $s['lastname'], $old_class, $old_class, $base_rate, $avg_paid, $type_remise, $count_months, 'MATERNELLE']);
        $counters['MAT']++;
        continue;
    }
    
    // Determine level progression
    $level = '';
    $group = '';
    if (preg_match('/^(.*?)-(A|B)$/i', $old_class, $matches)) {
        $level = trim($matches[1]);
        $group = strtoupper($matches[2]);
    } else {
        $level = $old_class;
    }
    
    $new_level = isset($level_progression[$level]) ? $level_progression[$level] : '';
    if ($new_level === '') {
        fputcsv($h_ver, [$admission_no, $s['firstname'], $s['lastname'], $old_class, 'A_DETERMINER', $base_rate, $avg_paid, $type_remise, $count_months, 'A_VERIFIER', "Niveau inconnu: $level"]);
        $counters['VER']++;
        continue;
    }
    
    if ($group === '') {
        if (!isset($group_counters[$new_level])) $group_counters[$new_level] = 0;
        $group = ($group_counters[$new_level] % 2 == 0) ? 'A' : 'B';
        $group_counters[$new_level]++;
    }
    $nouvelle_classe = "$new_level-$group";
    
    // Classification based on BASE RATE
    if ($base_rate >= 850 && $base_rate <= 950) {
        fputcsv($h_900, [$admission_no, $s['firstname'], $s['lastname'], $old_class, $nouvelle_classe, $base_rate, $avg_paid, $type_remise, $count_months, 'STANDARD_900']);
        $counters['900']++;
    } elseif ($base_rate >= 700 && $base_rate <= 800) {
        fputcsv($h_750, [$admission_no, $s['firstname'], $s['lastname'], $old_class, $nouvelle_classe, $base_rate, $avg_paid, $type_remise, $count_months, 'LEGACY_750']);
        $counters['750']++;
    } else {
        $reason = ($base_rate == 0) ? "Tarif de base impossible à déterminer" : "Tarif de base atypique: $base_rate";
        fputcsv($h_ver, [$admission_no, $s['firstname'], $s['lastname'], $old_class, $nouvelle_classe, $base_rate, $avg_paid, $type_remise, $count_months, 'A_VERIFIER', $reason]);
        $counters['VER']++;
    }
}

fclose($h_900);
fclose($h_750);
fclose($h_mat);
fclose($h_ver);

echo json_encode($counters);
$conn->close();
