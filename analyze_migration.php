<?php
// analyze_migration.php

$host = 'localhost';
$user = 'u707543112_systeme';
$pass = '/|098hH7';
$db   = 'u707543112_systeme';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");

// Get active session ID
$session_id = 21; // 2025-26

// Define class mapping (Primary only)
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
$res = $conn->query("SELECT id, type, code FROM feetype WHERE type LIKE '%scolarité%' OR type = 'SCOLARITE' OR code LIKE 'sc-%' OR code = 'FS'");
while ($row = $res->fetch_assoc()) {
    $fee_types[] = $row['id'];
}

if (empty($fee_types)) {
    die("Error: No scolarité fee types found by name.");
}
$fee_types_list = implode(',', $fee_types);

// Prepare CSV handles
$h_900 = fopen('MIGRATION_STANDARD_900.csv', 'w');
$h_750 = fopen('MIGRATION_LEGACY_750.csv', 'w');
$h_mat = fopen('MIGRATION_MATERNELLE.csv', 'w');
$h_ver = fopen('MIGRATION_A_VERIFIER.csv', 'w');

$header = ['admission_no', 'firstname', 'lastname', 'ancienne_classe', 'nouvelle_classe', 'montant_moyen_detecte', 'nombre_mois_analysés', 'statut'];
$header_ver = array_merge($header, ['raison']);

fputcsv($h_900, $header);
fputcsv($h_750, $header);
fputcsv($h_mat, $header);
fputcsv($h_ver, $header_ver);

// Fetch students
$sql = "SELECT s.id as student_id, s.admission_no, s.firstname, s.lastname, c.class as old_class
        FROM students s
        JOIN student_session ss ON s.id = ss.student_id
        JOIN classes c ON ss.class_id = c.id
        WHERE ss.session_id = $session_id";

$res_students = $conn->query($sql);

$counters = ['900' => 0, '750' => 0, 'MAT' => 0, 'VER' => 0];
$group_counters = []; // For 50/50 split when no group info is present

while ($s = $res_students->fetch_assoc()) {
    $old_class = trim($s['old_class']);
    $admission_no = $s['admission_no'];
    
    // Check if Maternelle
    $is_maternelle = false;
    foreach ($maternelle_classes as $m) {
        if (stripos($old_class, $m) !== false) {
            $is_maternelle = true;
            break;
        }
    }
    
    if ($is_maternelle) {
        fputcsv($h_mat, [$admission_no, $s['firstname'], $s['lastname'], $old_class, $old_class, 'N/A', 0, 'MATERNELLE']);
        $counters['MAT']++;
        continue;
    }
    
    // Determine Level and Group
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
        fputcsv($h_ver, [$admission_no, $s['firstname'], $s['lastname'], $old_class, 'A_DETERMINER', 0, 0, 'A_VERIFIER', "Niveau inconnu ou hors périmètre: $level"]);
        $counters['VER']++;
        continue;
    }
    
    if ($group === '') {
        // 50/50 split
        if (!isset($group_counters[$new_level])) $group_counters[$new_level] = 0;
        $group = ($group_counters[$new_level] % 2 == 0) ? 'A' : 'B';
        $group_counters[$new_level]++;
    }
    
    $nouvelle_classe = "$new_level-$group";
    
    // Analyze payments
    $paid_amounts = [];
    $sql_fees = "SELECT amount_detail 
                 FROM student_fees_deposite 
                 WHERE student_fees_master_id IN (
                     SELECT id FROM student_fees_master WHERE student_session_id IN (
                         SELECT id FROM student_session WHERE student_id = {$s['student_id']}
                     )
                 )
                 AND fee_groups_feetype_id IN (
                     SELECT id FROM fee_groups_feetype WHERE feetype_id IN ($fee_types_list)
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
    
    if (empty($paid_amounts)) {
        fputcsv($h_ver, [$admission_no, $s['firstname'], $s['lastname'], $old_class, $nouvelle_classe, 0, 0, 'A_VERIFIER', "Aucun paiement de scolarité trouvé"]);
        $counters['VER']++;
        continue;
    }
    
    // Calculate mode
    $frequencies = array_count_values(array_map('strval', $paid_amounts));
    arsort($frequencies);
    $dominant_amount = floatval(key($frequencies));
    $count_months = count($paid_amounts);
    
    if ($dominant_amount >= 650 && $dominant_amount <= 800) {
        fputcsv($h_750, [$admission_no, $s['firstname'], $s['lastname'], $old_class, $nouvelle_classe, $dominant_amount, $count_months, 'LEGACY_750']);
        $counters['750']++;
    } elseif ($dominant_amount >= 850 && $dominant_amount <= 1000) {
        fputcsv($h_900, [$admission_no, $s['firstname'], $s['lastname'], $old_class, $nouvelle_classe, $dominant_amount, $count_months, 'STANDARD_900']);
        $counters['900']++;
    } else {
        $amounts_str = implode(',', array_slice($paid_amounts, 0, 5));
        fputcsv($h_ver, [$admission_no, $s['firstname'], $s['lastname'], $old_class, $nouvelle_classe, $dominant_amount, $count_months, 'A_VERIFIER', "Montant atypique détecté ($dominant_amount). Historique: [$amounts_str]"]);
        $counters['VER']++;
    }
}

fclose($h_900);
fclose($h_750);
fclose($h_mat);
fclose($h_ver);

echo json_encode($counters);
$conn->close();
