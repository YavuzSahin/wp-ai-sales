<?php
error_reporting(E_ALL & ~E_NOTICE);
header("Access-Control-Allow-Origin: https://www.bariatricistanbul.com.tr");
header("Access-Control-Allow-Headers: Content-Type");
ob_start();
session_start();
require_once 'vendor/autoload.php';
require_once 'controller/database.php';
require_once 'controller/setting.php';
require_once 'controller/leads.php';
require_once 'controller/agents.php';
require_once 'controller/WhatsAppService.php';
header('Content-Type: application/json');

$cmd = $_GET['cmd'] ?? null;
global $db;
$db         = database::connect();
$settings   = new setting();
$leadC      = new leads();
$whatsapp   = new WhatsAppService();


function logMessage($content) {
    file_put_contents(__DIR__ . '/logs/send_message.log', "[" . date('Y-m-d H:i:s') . "] " . $content . "\n", FILE_APPEND);
}

switch ($cmd){
    default:
        echo json_encode(['status' => 'error', 'message' => 'Unknown command']);
        break;
    case 'list_leads':
        if(isset($_SESSION['biAgent_admin'])){$userid=$_SESSION['biAgent_admin'];}else{$userid=1;}
        try {
            //$leads = $leadC->allLeads($userid);
            $leads = $db->query("
    SELECT 
        l.*, 
        (
            SELECT message 
            FROM messages 
            WHERE patient_id = l.id
            ORDER BY created_at DESC 
            LIMIT 1
        ) AS last_message,
        (
            SELECT created_at 
            FROM messages 
            WHERE patient_id = l.id
            ORDER BY created_at DESC 
            LIMIT 1
        ) AS last_message_time
    FROM leads l
    ORDER BY l.updatedate DESC
")->fetchAll();
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
        break;
    case'check_updates':
        $user_id = $_POST['user_id'] ?? null;

        if (!$user_id) {
            echo json_encode(['error' => 'user_id missing']);
            exit;
        }

        $response = [
            'new_lead' => null,
            'new_message' => null
        ];

        try {
            // ✅ 1. Yeni lead kontrolü (hasta mesajı ama henüz bildirilmeyenler)
            $lead = $db->table('messages')
                ->where('way', 0)
                ->where('is_new', 1)
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->get();

            if (!empty($lead)) {
                $patient_id = $lead->patient_id;

                // Hasta adı
                $patient = $db->table('leads')
                    ->where('id', $patient_id)
                    ->get(['name']);

                $response['new_lead'] = [
                    'id' => $patient_id,
                    'name' => $patient->name ?? 'Unknown',
                    'last_message' => $lead->message,
                    'time' => date('H:i', strtotime($lead->created_at))
                ];

                // is_new = 0 yap
                $db->table('messages')
                    ->where('patient_id', $patient_id)
                    ->where('is_new', 1)
                    ->update(['is_new' => 0]);
            }

            // ✅ 2. Gönderilmeyi bekleyen sistem mesajı
            $msg = $db->table('messages')
                ->where('way', 1)
                ->where('is_sent', 0)
                ->orderBy('id', 'ASC')
                ->limit(1)
                ->get();

            if (!empty($msg)) {
                $response['new_message'] = [
                    'lead_id' => $msg->patient_id,
                    'text' => $msg->message,
                    'time' => date('H:i', strtotime($msg->created_at))
                ];

                // is_sent = 1 yap
                $db->table('messages')
                    ->where('id', $msg->id)
                    ->update(['is_sent' => 1]);
            }

            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
    case 'list_leads_2':
        $leads = $db->table('leads')
            ->orderBy('last_interaction_at', 'DESC')
            ->limit(50)
            ->getAll();

        $response = [];

        foreach ($leads as $lead) {
            $response[] = [
                'id' => $lead->id,
                'name' => $lead->name,
                'last_message' => '-', // opsiyonel, istersen messages tablosundan da çekebiliriz
                'time' => $lead->last_interaction_at ? date('H:i', strtotime($lead->last_interaction_at)) : '',
                'unread' => $lead->last_unread_count ?? 0
            ];
        }

        echo json_encode($response);
        break;
    case 'load_messages':
        $lead_id = $_GET['lead_id'] ?? null;

        if (!$lead_id) {
            echo json_encode(['error' => 'lead_id required']);
            exit;
        }

        $messages = $db->table('messages')
            ->where('patient_id', $lead_id)
            ->orderBy('id', 'ASC')
            ->getAll();

        $response = [];

        foreach ($messages as $msg) {
            $agentName = '';
            if ($msg->way == 1 && $msg->agent_id) {
                $agentData = agents::getAgent($msg->agent_id);
                $agentName = $agentData->name ?? 'System';
            }

            $response[] = [
                'id'          => $msg->id,
                'message'     => $msg->message,  // 🔁 text yerine message olarak gönder
                'time'        => date('H:i', strtotime($msg->created_at)),
                'way'         => (int)$msg->way,
                'agent_name'  => $agentName,
                'media_url'   => $msg->media_url ?? null,
                'media_type'  => $msg->media_type ?? null
            ];

        }

        echo json_encode($response);
        break;
    case 'check_new_messages':
        $lead_id = $_POST['lead_id'] ?? null;
        $last_id = $_POST['last_id'] ?? 0;

        if (!$lead_id) {
            echo json_encode(['status' => 'error', 'message' => 'Missing lead_id']);
            exit;
        }

        try {
            $messages = $db->table('messages')
                ->where('patient_id', $lead_id)
                ->where('id', '>', $last_id)
                ->orderBy('id', 'ASC')
                ->getAll();

            $response = [];

            foreach ($messages as $msg) {
                $agent = \agents::getAgent($msg->agent_id);

                $response[] = [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'time' => date('H:i', strtotime($msg->created_at)),
                    'way' => (int)$msg->way,
                    'patient_id' => $msg->patient_id,
                    'agent_name' => $agent->name ?? null
                ];
            }

            echo json_encode(['status' => 'ok', 'messages' => $response]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        // En altta:
        if (!isset($messages)) {
            echo json_encode(['status' => 'ok', 'messages' => []]);
        }


        break;

/*
    case 'send_message':
        $lead_id = $_POST['lead_id'] ?? null;
        $text = trim($_POST['text'] ?? '');
        $agent_id = $_POST['agent_id'] ?? 1;

        $mediaUrl = $_POST['media_url'] ?? null;
        $mediaType = $_POST['media_type'] ?? null; // image | pdf

        if (!$lead_id || (!$text && !$mediaUrl)) {
            echo json_encode(['status' => 'error', 'message' => 'Eksik veri']);
            exit;
        }

        try {
            $lead = $db->table('leads')->where('id', $lead_id)->get();
            if (!$lead) {
                echo json_encode(['status' => 'error', 'message' => 'Lead bulunamadı']);
                exit;
            }

            $phone              = $lead->full_phone;
            $variable           = $settings->getAgentVariables();
            $ACCESS_TOKEN       = $variable->WPAccessT;
            $PHONE_NUMBER_ID    = $variable->WPPhone;

            // Mesajı önce DB’ye kaydet
            $insertId = $db->table('messages')->insert([
                'patient_id' => $lead_id,
                'from_number' => '', // opsiyonel
                'to_number' => $phone,
                'message' => $text,
                'way' => 1,
                'agent_id' => $agent_id,
                'media_url' => $mediaUrl,
                'status' => 'pending',
                'is_sent' => 0,
                'is_read' => 0,
                'is_new' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'last_interaction_at' => date('Y-m-d H:i:s')
            ]);

            $message_id = $db->insertId();
            $db->table('leads')->where('id', $lead_id)->update([
                'last_interaction_at' => date('Y-m-d H:i:s')
            ]);

            // 🟩 Eğer medya varsa önce yükle
            if ($mediaUrl) {
                // Medya dosyasını indir
                $localFile = tempnam(sys_get_temp_dir(), 'media');
                file_put_contents($localFile, file_get_contents($mediaUrl));
                $mime = mime_content_type($localFile);

                $cFile = curl_file_create($localFile, $mime, basename($mediaUrl));

                $uploadUrl = "https://graph.facebook.com/v19.0/$PHONE_NUMBER_ID/media";
                $uploadFields = [
                    'file' => $cFile,
                    'messaging_product' => 'whatsapp',
                    'type' => $mime
                ];

                $uploadHeaders = [
                    "Authorization: Bearer $ACCESS_TOKEN"
                ];

                $uploadCh = curl_init($uploadUrl);
                curl_setopt($uploadCh, CURLOPT_POST, true);
                curl_setopt($uploadCh, CURLOPT_POSTFIELDS, $uploadFields);
                curl_setopt($uploadCh, CURLOPT_HTTPHEADER, $uploadHeaders);
                curl_setopt($uploadCh, CURLOPT_RETURNTRANSFER, true);
                $uploadResult = curl_exec($uploadCh);
                curl_close($uploadCh);
                unlink($localFile); // Temp dosyayı sil

                $uploadResponse = json_decode($uploadResult, true);
                $mediaId = $uploadResponse['id'] ?? null;

                if (!$mediaId) {
                    $db->table('messages')->where('id', $message_id)->update(['status' => 'media_upload_failed']);
                    echo json_encode(['status' => 'error', 'message' => 'Medya yüklenemedi']);
                    exit;
                }

                // Medya mesajı gönder
                $mediaType = strtolower($mediaType);
                $payload = [
                    'messaging_product' => 'whatsapp',
                    'to' => $phone,
                    'type' => $mediaType === 'pdf' ? 'document' : 'image',
                    $mediaType === 'pdf' ? 'document' : 'image' => [
                        'id' => $mediaId,
                        'caption' => $text ?: ''
                    ]
                ];
            } else {
                // Sadece metin mesajı
                $payload = [
                    'messaging_product' => 'whatsapp',
                    'to' => $phone,
                    'type' => 'text',
                    'text' => ['body' => $text]
                ];
            }

            // WhatsApp mesaj gönderimi
            $url = "https://graph.facebook.com/v19.0/$PHONE_NUMBER_ID/messages";
            $headers = [
                "Authorization: Bearer $ACCESS_TOKEN",
                "Content-Type: application/json"
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_status === 200) {
                $db->table('messages')->where('id', $message_id)->update(['status' => 'sent', 'is_sent' => 1]);
                $agentData = agents::getAgent($agent_id);
                $agentName = $agentData->name ?? 'System';
                echo json_encode(['status' => 'ok', 'message_id' => $message_id, 'agentName' => $agentName]);
            } else {
                $db->table('messages')->where('id', $message_id)->update(['status' => 'error']);
                echo json_encode(['status' => 'error', 'message' => 'WhatsApp gönderimi başarısız']);
            }

        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

        break;

    case 'send_message':
        $lead_id = $_POST['lead_id'] ?? null;
        $text = trim($_POST['text'] ?? '');
        $agent_id = $_POST['agent_id'] ?? 1;

        $mediaUrl = $_POST['media_url'] ?? null;
        $mediaType = $_POST['media_type'] ?? null;

        logMessage("Gönderim başlatıldı. LeadID: $lead_id | Text: $text | MediaURL: $mediaUrl | MediaType: $mediaType");

        if (!$lead_id || (!$text && !$mediaUrl)) {
            logMessage("❌ Eksik veri.");
            echo json_encode(['status' => 'error', 'message' => 'Eksik veri']);
            exit;
        }

        try {
            $lead = $db->table('leads')->where('id', $lead_id)->get();
            if (!$lead) {
                logMessage("❌ Lead bulunamadı: $lead_id");
                echo json_encode(['status' => 'error', 'message' => 'Lead bulunamadı']);
                exit;
            }

            $phone              = $lead->full_phone;
            $variable           = $settings->getAgentVariables();
            $ACCESS_TOKEN       = $variable->WPAccessT;
            $PHONE_NUMBER_ID    = $variable->WPPhone;

            // 🧾 Veritabanı Kaydı
            $insertId = $db->table('messages')->insert([
                'patient_id' => $lead_id,
                'from_number' => '',
                'to_number' => $phone,
                'message' => $text,
                'way' => 1,
                'agent_id' => $agent_id,
                'media_url' => $mediaUrl,
                'status' => 'pending',
                'media_type' => $mediaType,
                'is_sent' => 0,
                'is_read' => 0,
                'is_new' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'last_interaction_at' => date('Y-m-d H:i:s')
            ]);

            $message_id = $db->insertId();
            $db->table('leads')->where('id', $lead_id)->update([
                'last_interaction_at' => date('Y-m-d H:i:s')
            ]);

            // 💬 Medya mı metin mi?
            if ($mediaUrl) {
                logMessage("📎 Medya yükleme başlatılıyor: $mediaUrl");

                $localFile = tempnam(sys_get_temp_dir(), 'media');
                file_put_contents($localFile, file_get_contents($mediaUrl));
                $mime = mime_content_type($localFile);

                logMessage("📎 MIME: $mime");

                $cFile = curl_file_create($localFile, $mime, basename($mediaUrl));
                $uploadUrl = "https://graph.facebook.com/v19.0/$PHONE_NUMBER_ID/media";
                $uploadFields = [
                    'file' => $cFile,
                    'messaging_product' => 'whatsapp',
                    'type' => $mime
                ];
                $uploadHeaders = ["Authorization: Bearer $ACCESS_TOKEN"];

                $uploadCh = curl_init($uploadUrl);
                curl_setopt($uploadCh, CURLOPT_POST, true);
                curl_setopt($uploadCh, CURLOPT_POSTFIELDS, $uploadFields);
                curl_setopt($uploadCh, CURLOPT_HTTPHEADER, $uploadHeaders);
                curl_setopt($uploadCh, CURLOPT_RETURNTRANSFER, true);
                $uploadResult = curl_exec($uploadCh);
                curl_close($uploadCh);
                unlink($localFile);

                logMessage("📎 Medya yükleme sonucu: $uploadResult");

                $uploadResponse = json_decode($uploadResult, true);
                $mediaId = $uploadResponse['id'] ?? null;

                if (!$mediaId) {
                    logMessage("❌ Medya yüklenemedi.");
                    $db->table('messages')->where('id', $message_id)->update(['status' => 'media_upload_failed']);
                    echo json_encode(['status' => 'error', 'message' => 'Medya yüklenemedi']);
                    exit;
                }

                $mediaType = strtolower($mediaType);
                $payload = [
                    'messaging_product' => 'whatsapp',
                    'to' => $phone,
                    'type' => $mediaType === 'pdf' ? 'document' : 'image',
                    $mediaType === 'pdf' ? 'document' : 'image' => [
                        'id' => $mediaId,
                        'caption' => $text ?: ''
                    ]
                ];
            } else {
                $payload = [
                    'messaging_product' => 'whatsapp',
                    'to' => $phone,
                    'type' => 'text',
                    'text' => ['body' => $text]
                ];
            }

            // ✅ WhatsApp Mesaj Gönderimi
            $url = "https://graph.facebook.com/v23.0/$PHONE_NUMBER_ID/messages";
            $headers = [
                "Authorization: Bearer $ACCESS_TOKEN",
                "Content-Type: application/json"
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            logMessage("📤 WhatsApp response ($http_status): $result");

            if ($http_status === 200) {


                $db->table('messages')->where('id', $message_id)->update(['status' => 'sent', 'is_sent' => 1]);
                $agentData = agents::getAgent($agent_id);
                $agentName = $agentData->name ?? 'System';
                echo json_encode(['status' => 'ok', 'message_id' => $message_id, 'agentName' => $agentName]);
            } else {
                $db->table('messages')->where('id', $message_id)->update(['status' => 'error']);
                echo json_encode(['status' => 'error', 'message' => 'WhatsApp gönderimi başarısız']);
            }

        } catch (Exception $e) {
            logMessage("❌ Exception: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

        break;
*/

    case 'send_message':

        $lead_id     = $_POST['lead_id'] ?? null;
        $text        = trim($_POST['text'] ?? '');
        $media_url   = trim($_POST['media_url'] ?? '');
        $media_type  = $_POST['media_type'] ?? null;
        $agent_id    = $_POST['agent_id'] ?? 1;

        logMessage("🟡 Mesaj gönderim başlatıldı. Lead ID: $lead_id | Text: $text | Media: $media_url ($media_type)");

        if (!$lead_id) {
            echo json_encode(['status' => 'error', 'message' => 'Lead ID eksik']);
            exit;
        }

        $lead = $db->table('leads')->where('id', $lead_id)->get();
        if (!$lead) {
            echo json_encode(['status' => 'error', 'message' => 'Lead bulunamadı']);
            exit;
        }

        $to_number   = $lead->full_phone;
        $from_number = (new setting())->getAgentVariables()->WPPhoneP;
        $message_sid = null;

        try {
            // Gönderim tipi belirleniyor
            if ($media_type === 'image' && $media_url) {
                $message_sid = $whatsapp->sendImage($to_number, $media_url, $text);
                $text = $text ?: '[Image]';
            } elseif ($media_type === 'video' && $media_url) {
                $message_sid = $whatsapp->sendVideo($to_number, $media_url, $text);
                $text = $text ?: '[Video]';
            } elseif ($media_type === 'document' && $media_url) {
                $filename = basename($media_url);
                $message_sid = $whatsapp->sendDocument($to_number, $media_url, $filename);
                $text = $text ?: '[Document]';
            } elseif (!empty($text)) {
                $message_sid = $whatsapp->sendText($to_number, $text);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Mesaj içeriği eksik']);
                exit;
            }

            // Veritabanına mesajı kaydet
            $data = [
                'patient_id'     => $lead_id,
                'from_number'    => $from_number,
                'to_number'      => $to_number,
                'message'        => $text,
                'media_url'      => $media_url ?: null,
                'media_type'     => $media_type ?: null,
                'message_sid'    => $message_sid,
                'way'            => 1, // bizden giden
                'status'         => $message_sid ? 'sent' : 'error',
                'agent_id'       => $agent_id,
                'is_new'         => 1,
                'is_sent'        => $message_sid ? 1 : 0,
                'is_read'        => 0,
                'created_at'     => date('Y-m-d H:i:s')
            ];

            $db->table('messages')->insert($data);
            $message_id = $db->insertId();

            $db->table('leads')->where('id', $lead_id)->update([
                'last_interaction_at' => date('Y-m-d H:i:s')
            ]);

            logMessage("✅ Mesaj gönderildi. SID: $message_sid");

            // WebSocket yayını
            $wsData = [
                'type'    => 'new_message',
                'lead_id' => $lead_id,
                'message' => [
                    'id'         => $message_id,
                    'message'    => $text,
                    'media_url'  => $media_url ?: null,
                    'media_type' => $media_type ?: null,
                    'time'       => date('H:i'),
                    'way'        => 1
                ]
            ];
            @file_get_contents('https://wss.bariatricistanbul.com.tr:9443/broadcast', false, stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/json',
                    'content' => json_encode($wsData)
                ]
            ]));

            $db->table('messages')->where('id', $message_id)->update(['status' => 'sent', 'is_sent' => 1]);
            $agentData = agents::getAgent($agent_id);
            $agentName = $agentData->name ?? 'System';

            echo json_encode([
                'status'      => 'ok',
                'message_id'  => $message_id,
                'sid'         => $message_sid,
                'agentName'   => $agentName
            ]);

        } catch (Exception $e) {
            logMessage("❌ Mesaj gönderim hatası: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Gönderim başarısız', 'error' => $e->getMessage()]);
        }

        break;




    case 'check_unreads':
        $leads = $db->table('leads')->getAll();
        $updated = [];

        foreach ($leads as $lead) {
            $countResult = $db->table('messages')
                ->where('patient_id', $lead->id)
                ->where('is_read', 0)
                ->count('id', 'unread_count')
                ->get();

            $unreadCount = (int) ($countResult->unread_count ?? 0);

            if ($unreadCount != $lead->last_unread_count) {
                $db->table('leads')->where('id', $lead->id)->update([
                    'last_unread_count' => $unreadCount,
                    'updatedate' => date('Y-m-d H:i:s'),
                ]);
                $updated[] = [
                    'id' => $lead->id,
                    'unread' => $unreadCount
                ];
            }
        }

        echo json_encode([
            'status' => 'ok',
            'updated_leads' => $updated
        ]);
        break;
    case 'read_messages':
        $leadId = $_REQUEST['lead_id'] ?? 0;

        if ($leadId > 0) {
            // 1. Hasta tarafından gelen ve okunmamış mesajları çek
            $messages = $db->table('messages')
                ->where('patient_id', $leadId)
                ->where('is_read', 0)
                ->where('way', 0)
                ->getAll();

            foreach ($messages as $msg) {
                // 2. Mesajı veritabanında okundu olarak güncelle
                $db->table('messages')
                    ->where('id', $msg->id)
                    ->update(['is_read' => 1]);

                // 3. Meta API üzerinden "okundu" bildirimi gönder
                if (!empty($msg->message_sid)) {
                    try {
                        $whatsapp->markAsRead($msg->message_sid);
                    } catch (Exception $e) {
                        // İsteğe bağlı olarak loglayabilirsin
                        file_put_contents('log-markasread.txt', "❌ Hata: " . $e->getMessage() . "\n", FILE_APPEND);
                    }
                }
            }

            // 4. Lead tablosundaki unread count sıfırla
            $db->table('leads')
                ->where('id', $leadId)
                ->update(['last_unread_count' => 0]);

            echo json_encode(['status' => 'ok']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lead ID geçersiz']);
        }
        break;

    case'ai_response':
        $lead_id = $_POST['lead_id'] ?? null;
        if (!$lead_id) {
            echo json_encode(['status' => 'error', 'message' => 'lead_id is required']);
            exit;
        }

        $db = database::connect();
        $setting = new setting();

// 1. Lead bilgisi
        $lead = $db->table('leads')->where('id', $lead_id)->get();
        if (!$lead) {
            echo json_encode(['status' => 'error', 'message' => 'Lead not found']);
            exit;
        }

// 2. Agent aktif mi?
        $agent_id = $lead->agent_id;
        $agent = $db->table('users')->where('id', $agent_id)->get();
        if ($agent && (int)$agent->login === 1) {
            echo json_encode(['status' => 'skip', 'message' => 'Agent is online, AI not triggered']);
            exit;
        }

// 3. Konuşma geçmişini al
        $messages = $db->table('messages')
            ->where('patient_id', $lead_id)
            ->orderBy('id', 'ASC')
            ->getAll();

        $conversation = [];
        foreach ($messages as $msg) {
            $conversation[] = [
                'role' => $msg->way == 1 ? 'user' : 'assistant',
                'content' => $msg->message
            ];
        }

        if (count($conversation) === 0) {
            echo json_encode(['status' => 'error', 'message' => 'No message history']);
            exit;
        }

        // 4. OpenAI yanıtı al
        $variables      = $setting->getAgentVariables();
        $OPENAI_API_KEY = $variables->AIKey;
        $client = OpenAI::client($OPENAI_API_KEY);
        try {
            $response = $client->chat()->create([
                'model' => 'gpt-4o',
                'messages' => $conversation,
                'temperature' => 0.7,
            ]);

            $reply = $response->choices[0]->message['content'] ?? '';

            if (!$reply) {
                echo json_encode(['status' => 'error', 'message' => 'Empty AI response']);
                exit;
            }

            echo json_encode([
                'status' => 'ok',
                'reply' => $reply
            ]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
        break;

    case 'check_agent_status':
        $lead_id = $_GET['lead_id'] ?? $_POST['lead_id'] ?? null;

        if (!$lead_id) {
            echo json_encode(['status' => 'error', 'message' => 'Missing lead_id']);
            exit;
        }

        try {
            $lead = $db->table('leads')->where('id', $lead_id)->get();
            if (!$lead || !$lead->agent) {
                echo json_encode(['status' => 'offline']);
                exit;
            }

            $agent = $db->table('agents')->where('id', $lead->agent)->get();
            if (!$agent || $agent->login != 1) {
                echo json_encode(['status' => 'offline']);
            } else {
                echo json_encode(['status' => 'online']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;



}