<?php
require_once 'api/vendor/autoload.php';
require_once 'api/controller/database.php';
$db = database::connect();

// ✅ 1. META WEBHOOK DOĞRULAMA
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $verify_token = 'bi2020';
    $mode = $_GET['hub_mode'] ?? $_GET['hub.mode'] ?? null;
    $token = $_GET['hub_verify_token'] ?? $_GET['hub.verify_token'] ?? null;
    $challenge = $_GET['hub_challenge'] ?? $_GET['hub.challenge'] ?? null;
    if ($mode === 'subscribe' && $token === $verify_token) {
        header('Content-Type: text/plain');
        echo $challenge;
        exit;
    } else {
        http_response_code(403);
        echo 'Invalid verification';
        exit;
    }
}

// ✅ 2. JSON VERİ ALMA
$input = file_get_contents('php://input');
file_put_contents('webhook-log.txt', $input . PHP_EOL, FILE_APPEND);
$data = json_decode($input, true);

if (!isset($data['entry'][0]['changes'][0]['value']['messages'][0])) {
    file_put_contents('webhook-debug.log', "⛔ No message found\n", FILE_APPEND);
    http_response_code(200);
    exit('No message');
}

$value = $data['entry'][0]['changes'][0]['value'];
$message = $value['messages'][0] ?? [];

$from         = $message['from'] ?? null;
$type         = $message['type'] ?? '';
$messageSid   = $message['id'] ?? null;
$timestamp    = $message['timestamp'] ?? time();
$profileName  = $value['contacts'][0]['profile']['name'] ?? 'Unknown';
$displayPhone = $value['metadata']['display_phone_number'] ?? '';
$media_url    = '';
$media_type   = '';
$text         = '';

// ✅ 3. MEDYA VE MESAJ TİPLERİNİ YAKALA
switch ($type) {
    case 'text':
        $text = $message['text']['body'] ?? '';
        break;

    case 'button':
        $text = $message['button']['text'] ?? '[Button Clicked]';
        break;

    case 'interactive':
        $text = $message['interactive']['button_reply']['title'] ?? '[Interactive Button Clicked]';
        break;

    case 'reaction':
        $reactionEmoji = $message['reaction']['emoji'] ?? '';
        $reactedMessageId = $message['reaction']['message_id'] ?? null;

        if ($reactionEmoji && $reactedMessageId) {
            // İlgili mesajı bul ve reaction alanını güncelle
            $db->table('messages')
                ->where('message_sid', $reactedMessageId)
                ->update(['reaction' => $reactionEmoji]);

            // WebSocket yayını (type: reaction)
            $wsPayload = [
                'type' => 'reaction',
                'message_sid' => $reactedMessageId,
                'reaction' => $reactionEmoji
            ];

            $ch = curl_init('https://wss.bariatricistanbul.com.tr:9443/broadcast');
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($wsPayload),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            curl_exec($ch);
            curl_close($ch);
        }

        // reaction mesajı olarak ayrı mesaj kaydı yapmıyoruz
        http_response_code(200);
        echo 'OK';
        exit;

    case 'image':
    case 'document':
    case 'video':
    case 'audio':
        // ... (mevcut medya kodları)
        break;

    default:
        $text = '[Unsupported message type]';
}

// ✅ 4. GEÇERSİZ MESAJLARI YAKALA
if (!$from || !$messageSid) {
    file_put_contents('webhook-debug.log', "⛔ Missing from or messageSid\n", FILE_APPEND);
    http_response_code(200);
    exit('Missing data');
}

// ✅ 5. DUPLICATE KONTROLÜ
$existing = $db->table('messages')->where('message_sid', $messageSid)->get();
if ($existing && isset($existing->id)) {
    file_put_contents('webhook-debug.log', "🔁 Duplicate message skipped: $messageSid\n", FILE_APPEND);
    http_response_code(200);
    exit('Duplicate');
}

// ✅ 6. LEAD OLUŞTUR
$lead = $db->table('leads')->where('phone', $from)->get();
if (!$lead || !isset($lead->id)) {
    $db->table('leads')->insert([
        'name' => $profileName,
        'phone' => $from,
        'full_phone' => $from,
        'status' => 1,
        'agent' => 1,
        'transfered' => 0,
        'notified' => 0,
        'language' => 0,
        'createdate' => date('Y-m-d H:i:s'),
        'updatedate' => date('Y-m-d H:i:s')
    ]);
    $leadId = $db->insertId();
    $lead = $db->table('leads')->where('id', $leadId)->get();
}

// ✅ 7. MESAJ KAYDET
$db->table('messages')->insert([
    'patient_id' => $lead->id,
    'from_number' => $from,
    'to_number' => $displayPhone,
    'message' => $text,
    'message_sid' => $messageSid,
    'media_url' => $media_url,
    'media_type' => $media_type,
    'status' => 'received',
    'way' => 0,
    'is_new' => 1,
    'created_at' => date('Y-m-d H:i:s', $timestamp)
]);
$messageId = $db->insertId(); // ✅ INSERT'TEN HEMEN SONRA ALINDI

$db->table('leads')->where('id', $lead->id)->update([
    'last_interaction_at' => date('Y-m-d H:i:s')
]);

$unreadCount = $db->table('messages')
        ->where('patient_id', $lead->id)
        ->where('is_read', 0)
        ->count('id', 'total')
        ->get()->total ?? 0;

$db->table('leads')->where('id', $lead->id)->update([
    'last_unread_count' => $unreadCount
]);

// ✅ 8. WebSocket Broadcast
$payload = [
    'type' => 'new_message',
    'patient_id' => $lead->id,
    'id' => $messageId,
    'message_sid' => $messageSid,
    'content' => $text,
    'media_url' => $media_url,
    'media_type' => $media_type,
    'created_at' => date('Y-m-d H:i:s'),
    'way' => 1
];

$ch = curl_init('https://wss.bariatricistanbul.com.tr:9443/broadcast');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_SSL_VERIFYPEER => false
]);
curl_exec($ch);
curl_close($ch);

http_response_code(200);
echo 'OK';