<?php
// Configuration des bases de données
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

// 1. Identification dynamique des tarifs
$tarifs_mapping = [];
$res = $conn->query("SELECT DISTINCT fgft.amount, fg.name 
                     FROM fee_groups_feetype fgft 
                     JOIN fee_groups fg ON fg.id = fgft.fee_groups_id 
                     WHERE fg.name LIKE '%Scolarité%' AND fg.name LIKE '%Primaire%'");
while ($row = $res->fetch_assoc()) {
    $amount = (int)$row['amount'];
    if ($amount == 900) $tarifs_mapping[$amount] = 'STANDARD_900';
    if ($amount == 750) $tarifs_mapping[$amount] = 'LEGACY_750';
}

// 2. Préparation des fichiers CSV
$h_900 = fopen('CP_STANDARD_900.csv', 'w');
$h_750 = fopen('CP_LEGACY_750.csv', 'w');
$h_ver = fopen('CP_A_VERIFIER.csv', 'w');

$header = ['admission_no', 'firstname', 'lastname', 'groupe', 'nouvelle_classe', 'tarif_base_detecte', 'montant_moyen_paye', 'type_remise', 'statut'];
fputcsv($h_900, $header);
fputcsv($h_750, $header);
fputcsv($h_ver, $header);

// 3. Extraction des élèves CP (Classes 13=CP-A, 14=CP-B)
$session_id = 21;
$sql = "SELECT s.id as student_id, ss.id as student_session_id, s.admission_no, s.firstname, s.lastname, c.class 
        FROM students s 
        JOIN student_session ss ON s.id = ss.student_id 
        JOIN classes c ON ss.class_id = c.id 
        WHERE ss.session_id = $session_id AND ss.class_id IN (13, 14) AND s.is_active = 'yes'";

$students = $conn->query($sql);

$counters = ['900' => 0, '750' => 0, 'VER' => 0];

while ($student = $students->fetch_assoc()) {
    $ss_id = $student['student_session_id'];
    $admission_no = $student['admission_no'];
    
    // Groupe et Nouvelle Classe
    $groupe = (strpos($student['class'], '-A') !== false) ? 'A' : 'B';
    $nouvelle_classe = ($groupe == 'A') ? 'CE1-A' : 'CE1-B';

    // A. Tarif assigné (via fee master)
    $tarif_base = 0;
    $fee_master_sql = "SELECT fgft.amount 
                       FROM student_fees_master sfm 
                       JOIN fee_session_groups fsg ON sfm.fee_session_group_id = fsg.id 
                       JOIN fee_groups_feetype fgft ON fsg.fee_groups_id = fgft.fee_groups_id 
                       JOIN fee_groups fg ON fgft.fee_groups_id = fg.id 
                       WHERE sfm.student_session_id = $ss_id 
                       AND fg.name LIKE '%Scolarité%' AND fg.name LIKE '%Primaire%'
                       LIMIT 1";
    $fm_res = $conn->query($fee_master_sql);
    if ($fm_row = $fm_res->fetch_assoc()) {
        $tarif_base = (int)$fm_row['amount'];
    }

    // B. Paiements réels
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

    // C. Remises
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
    $detected_type = isset($tarifs_mapping[$tarif_base]) ? $tarifs_mapping[$tarif_base] : 'INCONNU';
    $data = [
        $admission_no, 
        $student['firstname'], 
        $student['lastname'], 
        $groupe, 
        $nouvelle_classe, 
        $tarif_base, 
        $moyenne, 
        $type_remise, 
        'OK'
    ];

    if ($detected_type == 'STANDARD_900') {
        fputcsv($h_900, $data);
        $counters['900']++;
    } elseif ($detected_type == 'LEGACY_750') {
        fputcsv($h_750, $data);
        $counters['750']++;
    } else {
        $data[8] = "Tarif inconnu ou non détecté ($tarif_base)";
        fputcsv($h_ver, $data);
        $counters['VER']++;
    }
}

fclose($h_900);
fclose($h_750);
fclose($h_ver);

echo json_encode($counters);
$conn->close();
?>
