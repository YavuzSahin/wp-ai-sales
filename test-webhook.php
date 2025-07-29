<?php

$data = [
    'Body' => 'Bu bir test webhook mesajıdır ✅',
    'From' => 'whatsapp:+905555555555',
    'To' => 'whatsapp:+447822027528',
    'MessageSid' => 'SM' . rand(100000,999999),
];

$context = stream_context_create(['http' => [
    'method' => 'POST',
    'header' => "Content-Type: application/x-www-form-urlencoded",
    'content' => http_build_query($data)
]]);

$response = file_get_contents('https://www.bariatricistanbul.com/crm/whatsapp/webhook.php', false, $context);
echo $response;
