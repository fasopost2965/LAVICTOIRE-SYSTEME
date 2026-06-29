<?php
/**
 * CE2 to CM1 Migration Script
 * splitting cohort into A/B groups and classifying by fees.
 */

// Database configuration
$db_legacy = [
    'host' => 'localhost',
    'user' => 'u707543112_systeme',
    'pass' => '/|098hH7',
    'name' => 'u707543112_systeme'
];

$conn = new mysqli($db_legacy['host'], $db_legacy['user'], $db_legacy['pass'], $db_legacy['name']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Tarifs Mapping
$tarifs_mapping = [
    900 => 'STANDARD_900',
    750 => 'LEGACY_750'
];

// 2. Prepare CSV Files
$h_900 = fopen('CE2_STANDARD_900.csv', 'w');
$h_750 = fopen('CE2_LEGACY_750.csv', 'w');
$h_ver = fopen('CE2_A_VERIFIER.csv', 'w');

// Smart School Template + Internal Columns
$header = [
    'admission_no', 'firstname', 'lastname', 'gender', 'class', 'section', 'guardian_name', 'guardian_phone', 'email',
    'ancienne_classe', 'groupe_source', 'groupe_nouveau', 'tarif_base_detecte', 'montant_moyen_paye', 'type_remise', 'source_tarif', 'statut'
];

fputcsv($h_900, $header);
fputcsv($h_750, $header);
fputcsv($h_ver, $header);

// 3. Extract CE2 Students (Session 21)
$session_id = 21;
$ce2_class_id = 4; // Based on previous exploration

$sql = "SELECT s.id as student_id, ss.id as student_session_id, s.admission_no, s.firstname, s.lastname, s.gender, 
               s.guardian_name, s.guardian_phone, s.email, c.class 
        FROM students s 
        JOIN student_session ss ON s.id = ss.student_id 
        JOIN classes c ON ss.class_id = c.id 
        WHERE ss.session_id = $session_id AND ss.class_id = $ce2_class_id AND s.is_active = 'yes'
        ORDER BY s.lastname ASC, s.firstname ASC";

$result = $conn->query($sql);
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

$total_ce2 = count($students);
$counters = ['900' => 0, '750' => 0, 'VER' => 0];
$distribution = ['A' => 0, 'B' => 0];

// 4. Processing and 50/50 Split
foreach ($students as $index => $student) {
    $ss_id = $student['student_session_id'];
    
    // Alternating A and B
    $groupe_nouveau = ($index % 2 === 0) ? 'A' : 'B';
    $distribution[$groupe_nouveau]++;

    // A. Detect Fee Base
    $tarif_base = 0;
    $source_tarif = 'aucun';
    $fee_master_sql = "SELECT fgft.amount, fg.name 
                       FROM student_fees_master sfm 
                       JOIN fee_session_groups fsg ON sfm.fee_session_group_id = fsg.id 
                       JOIN fee_groups_feetype fgft ON fsg.fee_groups_id = fgft.fee_groups_id 
                       JOIN fee_groups fg ON fgft.fee_groups_id = fg.id 
                       WHERE sfm.student_session_id = $ss_id 
                       AND fg.name LIKE '%Scolarit??%' AND fg.name LIKE '%Primaire%'
                       LIMIT 1";
    $fm_res = $conn->query($fee_master_sql);
    if ($fm_row = $fm_res->fetch_assoc()) {
        $tarif_base = (int)$fm_row['amount'];
        $source_tarif = $fm_row['name'];
    }

    // B. Calculate Payments
    $total_paid = 0;
    $count_payments = 0;
    $pay_sql = "SELECT amount_detail FROM student_fees_deposite WHERE student_fees_master_id IN 
                (SELECT id FROM student_fees_master WHERE student_session_id = $ss_id)";
    $pay_res = $conn->query($pay_sql);
    while ($p_row = $pay_res->fetch_assoc()) {
        $details = json_decode($p_row['amount_detail'], true);
        if ($details) {
            foreach ($details as $inv) {
                if (isset($inv['amount'])) {
                    $total_paid += (float)$inv['amount'];
                    $count_payments++;
                }
            }
        }
    }
    $moyenne = ($count_payments > 0) ? round($total_paid / $count_payments, 2) : 0;

    // C. Detect Discounts
    $type_remise = 'aucune';
    $disc_sql = "SELECT fd.name 
                 FROM student_fees_discounts sfd 
                 JOIN fees_discounts fd ON sfd.fees_discount_id = fd.id 
                 WHERE sfd.student_session_id = $ss_id AND sfd.status = 'assigned'";
    $disc_res = $conn->query($disc_sql);
    $discounts = [];
    while ($d_row = $disc_res->fetch_assoc()) {
        $discounts[] = $d_row['name'];
    }
    if (!empty($discounts)) {
        $disc_str = implode(', ', $discounts);
        if (stripos($disc_str, 'personnel') !== false) $type_remise = 'personnel';
        elseif (stripos($disc_str, 'complet') !== false || stripos($disc_str, 'total') !== false) $type_remise = 'annuel';
        else $type_remise = 'autre (' . $disc_str . ')';
    }

    // D. Classification
    $detected_type = isset($tarifs_mapping[$tarif_base]) ? $tarifs_mapping[$tarif_base] : 'A_VERIFIER';
    
    $csv_data = [
        $student['admission_no'],
        $student['firstname'],
        $student['lastname'],
        $student['gender'],
        'CM1', // class
        $groupe_nouveau, // section
        $student['guardian_name'],
        $student['guardian_phone'],
        $student['email'],
        'CE2', // ancienne_classe
        'CE2', // groupe_source
        $groupe_nouveau, // groupe_nouveau
        $tarif_base,
        $moyenne,
        $type_remise,
        $source_tarif,
        'OK'
    ];

    if ($detected_type == 'STANDARD_900') {
        fputcsv($h_900, $csv_data);
        $counters['900']++;
    } elseif ($detected_type == 'LEGACY_750') {
        fputcsv($h_750, $csv_data);
        $counters['750']++;
    } else {
        $csv_data[16] = "Tarif non d??tect?? ou inconnu ($tarif_base)";
        fputcsv($h_ver, $csv_data);
        $counters['VER']++;
    }
}

fclose($h_900);
fclose($h_750);
fclose($h_ver);

$output = [
    'total_ce2' => $total_ce2,
    'distribution' => $distribution,
    'classification' => $counters
];

echo json_encode($output, JSON_PRETTY_PRINT);
$conn->close();
?>
