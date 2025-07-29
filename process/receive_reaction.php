<?php
$messageSid = $_POST['MessageSid'] ?? null;
$reaction   = $_POST['Reaction'] ?? null;

if (!$messageSid || !$reaction) {
    echo json_encode([
        "status" => "error",
        "message" => "Gerekli alanlar eksik: MessageSid veya Reaction"
    ]);
    exit;
}

global $db;

// ✅ Veritabanında eşleşen mesaj var mı?
$updated = $db->table('wa_messages')
    ->where('message_sid', $messageSid)
    ->update(['reaction' => $reaction]);

if ($updated) {
    echo json_encode([
        "status" => "ok",
        "message" => "Tepki kaydedildi: $reaction"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Tepki eşleşen mesaj bulunamadı."
    ]);
}
