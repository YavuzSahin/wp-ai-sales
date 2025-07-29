<?php

use Twilio\Rest\Client;

// ✅ Formdan gelen veriler
$lead_id    = intval($_POST['lead_id'] ?? 0);
$caption    = trim($_POST['caption'] ?? '');
$from       = "whatsapp:+447822027528";

// ✅ Medya yüklenmiş mi kontrol
if (!isset($_FILES['media']) || $_FILES['media']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["status" => "error", "message" => "Geçerli bir medya dosyası yüklenmedi."]);
    exit;
}

// ✅ Sunucuya yükle
$uploadDir = __DIR__ . '/../uploads/whatsapp/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$filename  = time() . '_' . basename($_FILES['media']['name']);
$filePath  = $uploadDir . $filename;
$fileUrl   = "https://www.bariatricistanbul.com/crm/uploads/whatsapp/" . $filename;

if (!move_uploaded_file($_FILES['media']['tmp_name'], $filePath)) {
    echo json_encode(["status" => "error", "message" => "Dosya sunucuya yüklenemedi."]);
    exit;
}


$leads = new leads();
$lead  = $leads->getLeads($lead_id);

if (!$lead || empty($lead->full_phone)) {
    echo json_encode(["status" => "error", "message" => "Telefon numarası bulunamadı."]);
    exit;
}

// ✅ Twilio ayarları
$sid    = "AC38daa1891191e0849c529ee9c45bc6f5";
$token  = "11b14fb5eb69cb87c0fd6044fd8e5b69";
$twilio = new Client($sid, $token);

try {
    $msg = $twilio->messages->create(
        "whatsapp:" . $lead->full_phone,
        [
            "from"     => $from,
            "mediaUrl" => [$fileUrl],
            "body"     => $caption // opsiyonel açıklama
        ]
    );

    global $db;
    $db->table('wa_messages')->insert([
        'patient_id'    => $lead->id,
        'from_number'   => str_replace("whatsapp:", "", $from),
        'to_number'     => $lead->full_phone,
        'message'       => $caption,
        'status'        => 'sent',
        'media_url'     => $fileUrl,
        'message_sid'   => $msg->sid,
        'way'           => 0,
        'created_at'    => date('Y-m-d H:i:s')
    ]);

    echo json_encode([
        "status" => "ok",
        "message" => [
            "sid" => $msg->sid,
            "media" => $fileUrl
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Twilio medya hatası: " . $e->getMessage()
    ]);
}
