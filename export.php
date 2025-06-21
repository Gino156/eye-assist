<?php
require 'includes/session.php';
require 'config.php';
require 'includes/functions.php';

$format = $_GET['format'] ?? 'csv';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$whereClause = "";
if (!empty($search)) {
    $escapedSearch = $conn->real_escape_string($search);
    $whereClause = "WHERE log_data LIKE '%$escapedSearch%'";
}

$query = $conn->query("SELECT * FROM navigation_logs $whereClause ORDER BY created_at DESC");

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=\"navigation_logs.csv\"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Date', 'Encrypted', 'Decrypted']);

    while ($row = $query->fetch_assoc()) {
        fputcsv($output, [
            $row['created_at'],
            $row['log_data'],
            decryptData($row['log_data'])
        ]);
    }

    fclose($output);
    exit;
}

if ($format === 'pdf') {
    require_once('tcpdf/tcpdf.php'); // ✅ Make sure TCPDF is installed under /tcpdf

    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Eye Assist');
    $pdf->SetTitle('Navigation Logs');
    $pdf->SetMargins(15, 20, 15);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 10);

    $html = '<h2>Navigation Logs (Decrypted)</h2>';
    while ($row = $query->fetch_assoc()) {
        $html .= '<hr>';
        $html .= '<p><strong>Date:</strong> ' . $row['created_at'] . '<br>';
        $html .= '<strong>Encrypted:</strong> ' . $row['log_data'] . '<br>';
        $html .= '<strong>Decrypted:</strong> ' . decryptData($row['log_data']) . '</p>';
    }

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('navigation_logs.pdf', 'D'); // force download
    exit;
}

// fallback if format is invalid
echo "Invalid export format.";
