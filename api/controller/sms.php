<?php
class sms{
    private static $instance;
    public static function getInstance() {
        if (!sms::$instance instanceof self) {
            sms::$instance = new self();
        }
        return sms::$instance;
    }


    public static function sendSMSPT($id){
        $db         = database::getInstance()->connect();
        $sid        = "AC687e5892f31ab42bcac3fbbf8b8e8c8e";
        $token      = "829ed72189ee1db081e82c7ca9e19883";

        $client     = new Twilio\Rest\Client($sid, $token);
        $db         = database::getInstance()->connect();
        $patient    = $db->table('sms_data')->where('id', $id)->get();
        $sms        = "Olá ".$patient->name."! Última oportunidade antes do aumento de preço para a cirurgia bariátrica! Livre-se de seu excesso de peso com a Bariátrica Istanbul, a escolha número 1 dos Portugueses na Turquia! Para mais detalhes; https://bit.ly/bipt";
        $number     = $patient->phone;
        $smsMessage = $sms;
        $message    = $client->messages->create($number, ["body" => $smsMessage, "from" => "+447897014247"]);
        if($message->sid) {
            $db->table('sms_log')->insert(['dataID'=>$patient->id, 'sid'=>$message->sid, 'status'=>0, 'created'=>date('Y-m-d H:i:s'), 'updated'=>date('Y-m-d H:i:s')]);
            echo '<div class="alert alert-success mb-1">SMS successfully sent!</div>';
        }else {
            echo '<div class="alert alert-danger mb-1">Error in sending sms please try again!</div>';
        }
    }
    public static function sendSMS($id, $textType){
        $sid        = "AC687e5892f31ab42bcac3fbbf8b8e8c8e";
        $token      = "829ed72189ee1db081e82c7ca9e19883";

        $client     = new Twilio\Rest\Client($sid, $token);

        $patient    = patient::getPatient($id);
        $sms        = "";
        switch ($textType){
            case 'welcome':
                $sms.= "Hello, ".$patient->name."! Welcome to Bariatric Istanbul. Congratulations for first step of new life. Parabéns! Pelo primeiro passo de uma nova vida.";
                break;
            case'result':
                $sms.= "Hi, ".$patient->name."! Your surgery files are ready and always online accessible! Please follow the link; https://bariatric.ist/r/".$patient->id;
                break;
        }


        $number     = $patient->phoneNumber;
        $smsMessage = $sms;
        $message    = $client->messages->create($number, ["body" => $smsMessage, "from" => "+14788005433"]);
        if($message->sid) {
            echo 'SMS successfully sent!';
        }else {
           echo 'Error in sending sms please try again!';
        }
    }

    public static function sendSMSTR($phones, $message){
        $gsm = "";
        foreach ($phones as $phone){
            $gsm.= "<gsm>".$phone.'</gsm>';
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://soap.netgsm.com.tr:8080/Sms_webservis/SMS?wsdl/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '<?xml version="1.0"?>
            <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
                         xmlns:xsd="http://www.w3.org/2001/XMLSchema"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                <SOAP-ENV:Body>
                    <ns3:smsGonder1NV2 xmlns:ns3="http://sms/">
                        <username>2166065478</username>
                        <password>yVz3191.</password>
                        <header>BariatrcIst</header>
                        <msg>'.$message.'</msg>
                        '.$gsm.'
                        <filter>0</filter>
                        <encoding>TR</encoding>
                    </ns3:smsGonder1NV2>
                </SOAP-ENV:Body>
            </SOAP-ENV:Envelope>',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        //echo $response;
    }
}