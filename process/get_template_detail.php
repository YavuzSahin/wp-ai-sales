<?php
header('Content-Type: application/json');

require_once '../vendor/autoload.php';
require_once '../controller/database.php';
require_once '../controller/leads.php';

$db = database::connect();

$id = intval($_POST['id']);
$template = $db->table('leads_template')->where('id', $id)->get();

if ($template) {
    echo json_encode([
        "status" => "ok",
        "template" => $template
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Şablon bulunamadı"
    ]);
}
exit;