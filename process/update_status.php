<?php
$messageSid = $_POST['MessageSid'] ?? null;
$status     = $_POST['MessageStatus'] ?? null;

if (!$messageSid || !$status) {
    echo json_encode([
        "status" => "error",
        "message" => "MessageSid veya MessageStatus eksik."
    ]);
    exit;
}

// ✅ Uygun statüler dışında güncelleme yapılmasın
$validStatuses = ['sent', 'delivered', 'read', 'failed'];

if (!in_array($status, $validStatuses)) {
    echo json_encode([
        "status" => "error",
        "message" => "Geçersiz status: $status"
    ]);
    exit;
}

// ✅ Veritabanında eşleşen mesaj güncelle
global $db;
$updated = $db->table('wa_messages')
    ->where('message_sid', $messageSid)
    ->update(['status' => $status]);

if ($updated) {
    echo json_encode([
        "status" => "ok",
        "message" => "Mesaj durumu güncellendi: $status"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Eşleşen mesaj bulunamadı."
    ]);
}
