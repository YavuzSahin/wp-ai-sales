<?php

header('Content-Type: application/json');

require_once '../vendor/autoload.php';
require_once '../controller/database.php';
require_once '../controller/leads.php';

global $db;
$db = database::connect();

$lead_id = intval($_POST['lead_id']);

$leads  = new leads();
$lead   = $leads->getLeads($lead_id);

$messages = $db->table('wa_messages')
    ->where('patient_id', $lead_id)
    ->orderBy('created_at', 'ASC')
    ->getAll();

if (is_object($messages)) {
    $messages = json_decode(json_encode($messages), true); // stdClass -> array
}

$messages = array_map(function($msg) use ($lead) {
    return [
        'id'            => $msg->id,
        'patient_id'    => $msg->patient_id,
        'sender_name'   => $msg->from_number === '+447822027528' ? 'Bariatric Istanbul' : $lead->name,
        'content'       => $msg->message,
        'created_at'    => $msg->created_at,
        'way'           => $msg->way,
        'media_url'     => $msg->media_url,
        'reaction'      => $msg->reaction,
        'status'        => $msg->status
    ];
}, $messages);


echo json_encode([
    'status' => 'ok',
    'messages' => $messages
]);
