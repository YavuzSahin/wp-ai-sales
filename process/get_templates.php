<?php
header('Content-Type: application/json');

require_once '../vendor/autoload.php';
require_once '../controller/database.php';
require_once '../controller/leads.php';

$db = database::connect();
$language = isset($_POST['language']) ? intval($_POST['language']) : 1;

$all = $db->table('leads_template')
    ->where('status', 1)
    ->where('language', $language)
    ->orderBy('created_at', 'desc')
    ->getAll();

// Sadece benzersiz template_code olanları filtrele
$seen = [];
$templates = [];

foreach ($all as $tpl) {
    if (!in_array($tpl->template_code, $seen)) {
        $seen[] = $tpl->template_code;
        $templates[] = $tpl;
    }
}


echo json_encode([
    "status" => "ok",
    "templates" => $templates
]);
exit;