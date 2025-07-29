<?php

header('Content-Type: application/json');

require_once '../vendor/autoload.php';
require_once '../controller/database.php';
require_once '../controller/leads.php';
$leadC = new leads();
global $db;

$patientId = $_POST['id'];
try {
    $leads = $leadC->getLeads($patientId);
    echo json_encode([
        "status" => "ok",
        "leads" => $leads
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Veritabanı hatası: " . $e->getMessage()
    ]);
}