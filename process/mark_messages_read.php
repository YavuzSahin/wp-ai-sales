<?php

require_once '../vendor/autoload.php';
require_once '../controller/database.php';

$lead_id = intval($_POST['lead_id'] ?? 0);

if (!$lead_id) {
    echo json_encode(['status' => 'error', 'message' => 'Lead ID eksik.']);
    exit;
}

global $db;
$db = database::connect();

$db->table('leads')->where('id', $lead_id)->update(['last_unread_count' => 0]);

echo json_encode(['status' => 'ok', 'message' => 'Okunmamış mesajlar sıfırlandı.']);
