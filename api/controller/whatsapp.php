<?php
use Twilio\Rest\Client;
class whatsapp{
    private static $instance;
    public static function getInstance(){
        if (!whatsapp::$instance instanceof self) {
            whatsapp::$instance = new self();
        }
        return whatsapp::$instance;
    }

    public static function sendMessage($patientID, $messageType){
        $db         = database::getInstance()->connect("prod");
        $sid        = "AC38daa1891191e0849c529ee9c45bc6f5";
        $token      = "11b14fb5eb69cb87c0fd6044fd8e5b69";
        $twilio     = new Client($sid, $token);
            $patient    = patient::getPatient($patientID);
        $operation  = patient::getPatientOperation($patient->id);
        $message    = messages::getMessages($messageType, $patient->language);


        $readyMessage = str_replace('{{1}}', $patient->name, $message->message);
        $locale = setting::setLanguageLocale($patient->language);
        setlocale(LC_ALL, $locale);
        $readyMessage = str_replace('{{2}}', date('d-m-Y, l', strtotime($operation->operationDate)), $readyMessage);
        $operationType      = finance::getProductName($operation->operationType);
        $readyMessage = str_replace('{{3}}', $operationType, $readyMessage);

        $message = $twilio->messages
            ->create("whatsapp:".$patient->phoneNumber, // to
                [
                    "from" => "whatsapp:+447822027528",
                    "body" => $readyMessage,
                ]
            );
        $conversationID = $message->sid;

        $data = [
            'WaId'              =>  $patient->id,
            'body'              =>  $readyMessage,
            'SmsMessageSid'     =>  $conversationID,
            'fromwho'           =>  $patient->id,
            'status'            =>  'status',
            'media'             =>  '',
            'showed'            =>  0,
            'createdate'        =>  date('Y-m-d H:i:s'),
            'updatedate'        =>  date('Y-m-d H:i:s')
        ];
        $db->table('message_logs')->insert($data);
        $lastId     = $db->insertId();

        $dataLeads = [
            'name'              =>  $patient->name,
            'createdate'        =>  date('Y-m-d H:i:s'),
            'updatedate'        =>  date('Y-m-d H:i:s')
        ];
        $db->table('leads')->insert($dataLeads);
        $leadid     = $db->insertId();

        $db->table('wa_messages')->insert([
            'patient_id'            => $leadid,
            'from_number'           => '+447822027528',
            'to_number'             => $patient->phoneNumber,
            'message'               => $readyMessage,
            'status'                => 'sent',
            'message_sid'           => $conversationID,
            'way'                   => 0,
            'media_url'             => '',
            'reserved'              => 1,
            'reserved_patient_id'   => $lastId,
            'created_at'            => date('Y-m-d H:i:s')
        ]);

    }
    public static function sendRequestInfoMessage($patientID, $messageType, $requestType){
        $db         = database::getInstance()->connect("prod");
        $sid        = "AC38daa1891191e0849c529ee9c45bc6f5";
        $token      = "11b14fb5eb69cb87c0fd6044fd8e5b69";
        $twilio     = new Client($sid, $token);
        $patient    = patient::getPatient($patientID);
        $operation  = patient::getPatientOperation($patient->id);
        $message    = messages::getMessages($messageType, 4);

        if($requestType=='Peşinat Ödemesi') {
            $managers = array(1, 19);//array(1,19,31);
        }else{
            $managers = array(1);//array(1,19,31);
        }


        foreach ($managers as $manager){
            $managerDetail  = manager::info($manager);
            $link           = 'https://www.bariatricistanbul.com/crm';
            if($managerDetail->role==6){
                $link   = $link.'/nurses/patient/edit/'.$patient->id;
            }else{
                $link   = $link.'/patient/finance/'.$patient->id;
            }

            $readyMessage   = str_replace('{{1}}', $managerDetail->name, $message->message);
            $readyMessage   = str_replace('{{2}}', $patient->name, $readyMessage);
            $readyMessage   = str_replace('{{3}}', $requestType, $readyMessage);
            $readyMessage   = str_replace('{{4}}', $link, $readyMessage);
        }

        $message = $twilio->messages
            ->create("whatsapp:".$managerDetail->phone, // to
                [
                    "from" => "whatsapp:+447822027528",
                    "body" => $readyMessage,
                ]
            );
        $conversationID = $message->sid;

        $data = [
            'WaId'              =>  $patient->id,
            'body'              =>  $readyMessage,
            'SmsMessageSid'     =>  $conversationID,
            'fromwho'           =>  $patient->id,
            'status'            =>  'status',
            'media'             =>  '',
            'showed'            =>  0,
            'createdate'        =>  date('Y-m-d H:i:s'),
            'updatedate'        =>  date('Y-m-d H:i:s')
        ];
        $r = $db->table('message_logs')->insert($data);
        if($r){
            $msg = ['status' => 1, 'message' => 'Bilgi Talebi Başarıyla İletildi.'];
        }else{
            $msg = ['status' => 2, 'message' => 'Bilgi Talebi İletilemedi.'];
        }
        echo json_encode($msg);

    }



    public static function sendOTP($patientID, $authCode){
        $db         = database::getInstance()->connect("prod");
        $sid        = "AC38daa1891191e0849c529ee9c45bc6f5";
        $token      = "11b14fb5eb69cb87c0fd6044fd8e5b69";
        $twilio     = new Client($sid, $token);
        $patient    = patient::getPatient($patientID);
        $message    = messages::getMessages('otp_biid', $patient->language);

        $readyMessage = str_replace('{{1}}', $authCode, $message->message);
        $message = $twilio->messages
            ->create("whatsapp:+905357726543",//.$patient->phoneNumber, // to
                [
                    "from" => "whatsapp:+447822027528",
                    "body" => $readyMessage,
                ]
            );
        $conversationID = $message->sid;
        $data = [
            'WaId'              =>  $patient->id,
            'body'              =>  $readyMessage,
            'SmsMessageSid'     =>  $conversationID,
            'fromwho'           =>  $patient->id,
            'status'            =>  'status',
            'media'             =>  '',
            'showed'            =>  0,
            'createdate'        =>  date('Y-m-d H:i:s'),
            'updatedate'        =>  date('Y-m-d H:i:s')
        ];
        $db->table('message_logs')->insert($data);
    }


    public static function sendProfileMessage($patientID, $messageType){
        $db         = database::getInstance()->connect("prod");
        $sid        = "AC38daa1891191e0849c529ee9c45bc6f5";
        $token      = "11b14fb5eb69cb87c0fd6044fd8e5b69";
        $twilio     = new Client($sid, $token);
        $patient    = patient::getPatient($patientID);
        $message    = messages::getMessages($messageType, 1);//$patient->language);




        $readyMessage   = str_replace('{{1}}', $patient->name, $message->message);
        $readyMessage   = str_replace('{{2}}', 'https://www.bariatricistanbul.com/patient/profile/12345', $readyMessage);
        $locale         = setting::setLanguageLocale($patient->language);
        setlocale(LC_ALL, $locale);
        $image          = $message->media;

        /*
          $message = $twilio->messages
            ->create("whatsapp:+447428994940",//.$patient->phoneNumber, // to
                [
                    "mediaUrl" => [$image],
                    "from" => "whatsapp:+447822027528",
                    "body" => $readyMessage,
                ]
            );
        */

          $message = $twilio->messages
            ->create("whatsapp:+447428994940",//.$patient->phoneNumber, // to
                [
                    "contentSid"    => "HX6a3a694f79bda18e0872ff22adcd1493",
                    "from"          => "whatsapp:+447822027528",
                    "mediaUrl"      => [$image],
                    "contentVariables" => json_encode([
                        "1"         => trim($patient->name),
                        "2"         => 'https://www.bariatricistanbul.com/patient/profile/12345',
                    ]),
                ]
            );



        $conversationID = $message->sid;

        $data = [
            'WaId'              =>  $patient->id,
            'body'              =>  $readyMessage,
            'SmsMessageSid'     =>  $conversationID,
            'fromwho'           =>  $patient->id,
            'status'            =>  'status',
            'media'             =>  '',
            'showed'            =>  0,
            'createdate'        =>  date('Y-m-d H:i:s'),
            'updatedate'        =>  date('Y-m-d H:i:s')
        ];
        $db->table('message_logs')->insert($data);

        echo $conversationID;

    }
    public static function sendCampaign($patientID, $messageType){
        $db         = database::getInstance()->connect("prod");
        $sid        = "AC38daa1891191e0849c529ee9c45bc6f5";
        $token      = "11b14fb5eb69cb87c0fd6044fd8e5b69";
        $twilio     = new Client($sid, $token);
        $patient    = patient::getPatient($patientID);
        $message    = messages::getMessages($messageType, 1);//$patient->language);




        $readyMessage   = str_replace('{{1}}', $patient->name, $message->message);
        $readyMessage   = str_replace('{{2}}', 'https://www.bariatricistanbul.com/patient/profile/12345', $readyMessage);
        $locale         = setting::setLanguageLocale($patient->language);
        setlocale(LC_ALL, $locale);
        $image          = $message->media;

          $message = $twilio->messages
            ->create("whatsapp:+447428994940",//.$patient->phoneNumber, // to
                [
                    "contentSid"    => $message->template_id,
                    "from"          => "whatsapp:+447822027528",
                    "mediaUrl"      => [$image],
                    "contentVariables" => json_encode([
                        "1"         => trim($patient->name)
                    ]),
                ]
            );



        $conversationID = $message->sid;

        $data = [
            'WaId'              =>  $patient->id,
            'body'              =>  $readyMessage,
            'SmsMessageSid'     =>  $conversationID,
            'fromwho'           =>  $patient->id,
            'status'            =>  'status',
            'media'             =>  '',
            'showed'            =>  0,
            'createdate'        =>  date('Y-m-d H:i:s'),
            'updatedate'        =>  date('Y-m-d H:i:s')
        ];
        $db->table('message_logs')->insert($data);

        echo $conversationID;

    }
}