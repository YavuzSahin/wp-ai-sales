<?php
header('Content-Type: application/json');

require_once '../vendor/autoload.php';
require_once '../controller/database.php';
require_once '../controller/leads.php';
$from_phone = 'whatsapp:+447822027528';
$db = database::connect();

use Twilio\Rest\Client;


$lead_id        = intval($_POST['lead_id']);
$bmessage       = trim($_POST['message']);
$template_id    = trim($_POST['template_id']);
$variables      = isset($_POST['content_variables']) ? json_decode($_POST['content_variables'], true) : [];

$template       = $db->table('leads_template')->where('template_id', $template_id)->get();
if($template->template_media){$mediaUrl = $template->template_media;}else{$mediaUrl = null;}



$leads = new leads();
$lead  = $leads->getLeads($lead_id);

if (!$lead || !$lead->full_phone || !$template_id) {
    echo json_encode(["status" => "error", "message" => "Veri eksik."]);
    exit;
}

// Twilio ayarları
$sid     = "AC38daa1891191e0849c529ee9c45bc6f5";
$token   = "11b14fb5eb69cb87c0fd6044fd8e5b69";
$twilio  = new Client($sid, $token);

try {
    $params = [
        "from" => $from_phone,
        "contentSid" => $template_id,
        "contentVariables" => json_encode($variables),
        "statusCallback" => "https://www.bariatricistanbul.com/crm/whatsapp/webhook.php"
    ];

    if ($mediaUrl) {
        $params['mediaUrl'] = $mediaUrl;
    }

    $message = $twilio->messages->create("whatsapp:" . $lead->full_phone, $params);
    $conversationID = $message->sid;

    // Veritabanına kaydet
    $data = [
        'patient_id'    => $lead->id,
        'from_number'   => str_replace("whatsapp:", "", $from_phone),
        'to_number'     => $lead->full_phone,
        'message'       => $bmessage,
        'template_id'   => $template_id,
        'status'        => 'sent',
        'media_url'     => $mediaUrl,
        'message_sid'   => $conversationID,
        'way'           => 0,
        'created_at'    => date('Y-m-d H:i:s'),
        'last_interaction_at'    => date('Y-m-d H:i:s'),
    ];

    $r = $db->table('wa_messages')->insert($data);
    $lastId = $db->insertId();

    if ($r) {
        $msg = array(
            'id' => $lastId,
            'sender_name' => 'Bariatric Istanbul',
            'content' => $bmessage,
            'created_at' => date('Y-m-d H:i')
        );
        echo json_encode([
            "status" => "ok",
            "message" => $msg
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Twilio gönderim hatası: " . $e->getMessage()
    ]);
}
