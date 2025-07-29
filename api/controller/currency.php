<?php
class currency{
    public function __constructor(){}
    private static $instance;
    public static function getInstance() {
        if (!currency::$instance instanceof self) {
            currency::$instance = new self();
        }
        return currency::$instance;
    }
    public function getCurrencySell($id){
        try {
        $currencies     = array(1=>3, 2=>1, 3=>0);
        $client         = new SoapClient('http://data.altinkaynak.com/DataService.asmx?WSDL');
        $auth           = new stdClass();
        $auth->Username = 'AltinkaynakWebServis';
        $auth->Password = 'AltinkaynakWebServis';
        $header         = new SoapHeader('http://data.altinkaynak.com/', 'AuthHeader', $auth, false);
        $client->__setSoapHeaders($header);
        $response       = $client->GetCurrency();
            libxml_use_internal_errors(TRUE);
            $xml            = simplexml_load_string($response->GetCurrencyResult);
            return $xml->Kur[$currencies[$id]]->Satis;
        } catch (Exception $e) {
            return 1;
        }
    }

    public function getCurrencyBuy($id){
        if($id!=4) {
            try {
            $currencies = array(1 => 3, 2 => 1, 3 => 0);
            $client = new SoapClient('http://data.altinkaynak.com/DataService.asmx?WSDL');
            $auth = new stdClass();
            $auth->Username = 'AltinkaynakWebServis';
            $auth->Password = 'AltinkaynakWebServis';
            $header = new SoapHeader('http://data.altinkaynak.com/', 'AuthHeader', $auth, false);
            $client->__setSoapHeaders($header);
            $response   = $client->GetCurrency();
            $xml        = simplexml_load_string($response->GetCurrencyResult);
            $currency   =  $xml->Kur[$currencies[$id]]->Alis;
            } catch (Exception $e) {
                $currency = 1;
            }
        }else{
            $currency = 1;
        }
        return $currency;
    }




    public function tcmb($id){
        $currency = 1;
        $kur = simplexml_load_file("https://www.tcmb.gov.tr/kurlar/today.xml");
        if($id!=4 && !empty($id)) {
            $currencies = array(1 => 'GBP', 2 => 'EUR', 3 => 'USD');
            foreach ($kur->Currency as $cur) {
                if ($cur["Kod"] == $currencies[$id]){
                    $currency = $cur->ForexSelling;
                }
            }
        }else{
            $currency = 1;
        }
        return $currency;
    }

}
