<?php

header('Content-Type: application/json');

require_once '../vendor/autoload.php';
require_once '../controller/database.php';


$lead_id = intval($_POST['lead_id'] ?? 0);

if ($lead_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Geçersiz lead_id"
    ]);
    exit;
}

try {
    global $db;
    $db = database::connect();
    $db->table('leads')->where('id', $lead_id)->update(['last_unread_count'=>0]);

    echo json_encode([
        "status" => "ok",
        "message" => "Unread sayacı sıfırlandı"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Hata: " . $e->getMessage()
    ]);
}
