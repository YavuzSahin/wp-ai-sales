<?php
use Twilio\Rest\Client;
$lead_id = intval($_POST['lead_id'] ?? 0);
$message = trim($_POST['message'] ?? '');
$from_phone = 'whatsapp:+447822027528';

$mediaUrl = null;
if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION);
    $filename = time() . '_' . uniqid() . '.jpg'; // Sabit .jpg uzantısı

    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $filePath = $uploadDir . $filename;
    $fileUrl = "https://www.bariatricistanbul.com/crm/whatsapp/uploads/" . $filename;

    $tmp = $_FILES['media']['tmp_name'];

    if (in_array(mime_content_type($tmp), ['image/png', 'image/webp', 'image/jpeg'])) {
        $img = imagecreatefromstring(file_get_contents($tmp));
        imagejpeg($img, $filePath, 90); // kalite %90
        imagedestroy($img);
        $mediaUrl = $fileUrl;
    } else {
        // JPG değilse orijinal olarak kaydet (örn: PDF)
        $filename = time() . '_' . basename($_FILES['media']['name']);
        $filePath = $uploadDir . $filename;
        $fileUrl  = "https://www.bariatricistanbul.com/crm/whatsapp/uploads/" . $filename;
        if (!move_uploaded_file($tmp, $filePath)) {
            error_log("🚫 Dosya taşınamadı: $tmp -> $filePath");
        } else {
            $mediaUrl = $fileUrl;
        }
    }
}


$leads = new leads();
$lead = $leads->getLeads($lead_id);

if (!$lead || empty($lead->full_phone)) {
    echo json_encode(["status" => "error", "message" => "Telefon numarası bulunamadı."]);
    exit;
}

// ✅ Twilio ayarları
$sid = "AC38daa1891191e0849c529ee9c45bc6f5";
$token = "11b14fb5eb69cb87c0fd6044fd8e5b69";
$twilio = new Client($sid, $token);

try {
    $params = [
        "from" => $from_phone,
        "body" => $message,
        "statusCallback" => "https://www.bariatricistanbul.com/crm/whatsapp/webhook.php"
    ];

    if ($mediaUrl) {
        $params['mediaUrl'] = $mediaUrl;
    }

    $twilioMessage = $twilio->messages->create("whatsapp:".$lead->full_phone, $params);


    // ✅ Mesaj DB'ye kaydedilebilir
    global $db;
    $db = database::connect();
    $db->table('wa_messages')->insert([
        'patient_id' => $lead->id,
        'from_number' => str_replace("whatsapp:", "", $from_phone),
        'to_number' => $lead->full_phone,
        'message' => $message,
        'status' => 'sent',
        'message_sid'               => $twilioMessage->sid,
        'way' => 0,
        'media_url'                 =>$mediaUrl,
        'created_at'                => date('Y-m-d H:i:s'),
        'last_interaction_at'       => date('Y-m-d H:i:s'),
    ]);
    $lastId = $db->insertId();
    $msg = [
        'id' => $lastId,
        'sender_name' => 'Bariatric Istanbul',
        'content' => $message,
        'media_url'     => $mediaUrl,
        'created_at' => date('Y-m-d H:i:s')
    ];
    echo json_encode([
        'status' => 'ok',
        'message' => $msg,

        "sid" => $twilioMessage->sid,
        "to" => $lead->full_phone
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Twilio hatası: " . $e->getMessage()
    ]);
}
