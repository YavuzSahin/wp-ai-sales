<?php
class setting{
    public $site        = 'https://www.bariatricistanbul.com/crm';
    public $cdn         = 'https://www.bariatricistanbul.com/crm/';
    public $WPAccessT   = NULL;
    public $WPPhone     = NULL;
    public $WPApiVer    = NULL;
    public $WPPhoneP    = NULL;
    public $AIKey       = NULL;
    public $variable;
    private static $instance;
    public static function getInstance() {
        if (!setting::$instance instanceof self) {
            setting::$instance = new self();
        }
        return setting::$instance;
    }
    public function __construct() {
        //$this->variables->site    = 'https://www.bariatricistanbul.com/crm';
        //$this->variables->cdn     = 'https://www.bariatricistanbul.com/crm/';
        //global $variables;
        //$this->variables = $variables;
    }

    public function getVariables(){
        $this->site  = 'https://www.bariatricistanbul.com/crm';
        $this->cdn   = 'https://www.bariatricistanbul.com/crm/';
        return $this;
    }
    public function getAgentVariables(){
        $this->site     = 'https://www.bariatricistanbul.com.tr';
        $this->cdn      = 'https://crm.bariatricistanbul.com/assets';
        $this->WPAccessT= 'EAAGOOgaQNCwBPDXfqaTc471SA6IqSC9GzAuxylpFBjbgeKulkKpGWhkzoELZCjhR2k0FGuGUxDWJUgQtLxixTRSaIYGeuC9YItTNIxwN0B92MKcAFTrutrXGZAGjU5vqz5ZAcxDnD49yU5n7gDE3FfROaif8QZBE7EO82oEsKASGpWLDiibIqZBoEg5XOtCaAlgZDZD';
        $this->WPPhone  = '749284878260919';
        $this->WPPhoneP = '905491470447';
        $this->WPApiVer = '23.0';
        $this->AIKey    = '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';
        return $this;
    }



    public static function variables(){
        $variables          = new ArrayObject();
        @$variables->site    = 'https://www.bariatricistanbul.com/crm';
        @$variables->cdn     = 'https://www.bariatricistanbul.com/crm/';

        return $variables;
    }
    public static function createSlug($s){
        $tr = array('ş','Ş','ı','I','İ','ğ','Ğ','ü','Ü','ö','Ö','Ç','ç','(',')','/',' ',',','?', '\'', '@');
        $eng = array('s','s','i','i','i','g','g','u','u','o','o','c','c','','','','','','', '');
        $s = str_replace($tr,$eng,$s);
        $s = strtolower($s);
        $s = preg_replace('/&amp;amp;amp;amp;amp;amp;amp;amp;amp;.+?;/', '', $s);
        $s = preg_replace('/\s+/', '-', $s);
        $s = preg_replace('|-+|', '-', $s);
        $s = preg_replace('/#/', '', $s);
        //$s = str_replace('.', '', $s);
        $s = trim($s, '');
        return $s;
    }

    public static function getCountry($id){
        $db = database::getInstance()->connect();
        $c  =  $db->table('countries')->where('num_code', $id)->get();
        if($c){
            $r = $c;
        }else{
            $r = new ArrayObject();
            $r->alpha_2_code    = 'No Set';
            $r->alpha_3_code    = 'No Set';
            $r->en_short_name   = 'No Set';
            $r->nationality     = 'No Set';
        }
        return $r;
    }
    public static function getAllCountries(){
        $db = database::getInstance()->connect();
        return $db->table('countries')->orderBy('num_code', 'ASC')->getAll();
    }

    public static function airport($id){
        return $id==1 ? 'SAW - Sabiha Gökçen':'IST - Istanbul';
    }
    public static function destinations($id){
        $db = database::getInstance()->connect();
        return $db->table('destinations')->where('id', $id)->get();
    }

    public static function answer($id, $value){
        if($value=='room') {
            return $id == 1 ? 'Standard' : 'Suit';
        }else if($value=='companion') {
            return $id == 2  ? 'No' : 'Yes';
        }else if($value=='operation') {
            return $id == 1 ? 'Sleeve' : 'ByPass';
        }else if($value=='status') {
            return $id == 0 ? 'Waiting' : 'Done';
        }
    }
    public static function answerTR($id, $value){
        if($value=='room') {
            return $id == 1 ? 'Standard' : 'Suit';
        }else if($value=='companion') {
            return $id == 2  ? 'Hayır' : 'Evet';
        }else if($value=='operation') {
            return $id == 1 ? 'Sleeve' : 'ByPass';
        }else if($value=='status') {
            return $id == 0 ? 'Bekliyor' : 'Opere Edildi';
        }
        if($value=='transferStatus'){
            if($id==0){return '<i class="fal fa-exclamation-circle"></i> Güncelleme Gerekli!';}elseif($id==1){return '<i class="fal fa-check-circle"></i> Transfer Tamamlandı.';}elseif($id==2){return '<i class="fal fa-alarm-exclamation"></i> Transfer Gecikmeli Tamamlandı.';}elseif($id==3){return '<i class="fal fa-times-circle"></i> Transfer İptal Edildi.';}
        }
        if($value=='transferType'){
            if($id==1){return '<i class="fal fa-plane-arrival"></i> Geliş';}elseif($id==2){return '<i class="fal fa-plane-departure"></i> Dönüş';}elseif($id==3){return '<i class="fal fa-shuttle-van"></i> Ara Transfer';}else{return '<i class="fal fa-transporter-3"></i> Bilinmiyor.';}
        }
    }
    public static function operation($id){
        $db = database::getInstance()->connect();
        return $db->table('operation')->where('id', $id)->get();
    }
    public static function calculateAge($birthDate){
        //explode the date to get month, day and year
        $birthDate = explode("-", $birthDate);
        //get age from date or birthdate
        $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[2], $birthDate[1], $birthDate[0]))) > date("md")
            ? ((date("Y") - $birthDate[0]) - 1)
            : (date("Y") - $birthDate[0]));
        return $age-1;
    }

    public static function currency($currency){

        $currencies = array(
            1=>'<i class="far fa-pound-sign"></i> GBP',
            2=>'<i class="far fa-euro-sign"></i> EUR',
            3=>'<i class="far fa-dollar-sign"></i> USD',
            4=>'<i class="far fa-lira-sign"></i> TRY'
        );
        if(array_key_exists($currency, $currencies)){
            return $currencies[$currency];
        }else{
            return 'unknown';
        }
    }
    public static function currencySign($currency){
        if($currency==null){return 0;die();}
        $currencies = array(
            1=>'£',
            2=>'€',
            3=>'$',
            4=>'₺'
        );
        return $currencies[$currency];
    }
    public static function currencies(){
        $currencies = array(
            1=>'£ British Pound',
            2=>'€ Euro',
            3=>'$ Dollar',
            4=>'₺ Turkish Lira'
        );
        return $currencies;
    }

    public static function invoiceStatus($status){
        $state = [
            0=>'<span class="badge badge-inverse text-white p-1"><i class="fal fa-dot-circle"></i> New Invoice</span>',
            1=>'<span class="badge badge-primary text-white p-1"><i class="fal fa-dot-circle"></i> Processing</span>',
            2=>'<span class="badge badge-success text-white p-1"><i class="fal fa-dot-circle"></i> Approved</span>',
            3=>'<span class="badge badge-warning text-white p-1"><i class="fal fa-dot-circle"></i> Waiting Revision</span>',
            4=>'<span class="badge badge-info text-white p-1"><i class="fal fa-check"></i> Paid</span>',
            5=>'<span class="badge badge-danger text-white p-1"><i class="fal fa-times"></i> Cancelled</span>',
            6=>'<span class="badge badge-dark text-white p-1"><i class="fal fa-dot-circle"></i> Unknown Status</span>',
            7=>'<span class="badge badge-danger text-white p-1"><i class="fas fa-history"></i> Refunded</span>'
        ];
        return $state[$status];
    }

    static function invoiceStatusText($status){
        $state = [
            0=>'New Invoice',
            1=>'Processing',
            2=>'Approved',
            3=>'Waiting Revision',
            4=>'Paid',
            5=>'Cancelled',
            6=>'Unknown Status',
            7=>'Refunded'
        ];
        return $state[$status];
    }
    public static function invoiceType($type){
        $state = [
            1=>'<span class="badge badge-danger text-white p-1"><i class="fal fa-dot-circle"></i> Expense</span>',
            2=>'<span class="badge badge-success text-white p-1"><i class="fal fa-dot-circle"></i> Income</span>',
            3=>'<span class="badge badge-dark text-white p-1"><i class="fal fa-dot-circle"></i> Unknown Type</span>',
            4=>'<span class="badge badge-warning text-white p-1"><i class="fal fa-dot-circle"></i> Refunded</span>',
            5=>'<span class="badge badge-danger text-white p-1"><i class="fal fa-dot-circle"></i> Cancelled</span>',
        ];
        return $state[$type];
    }
    public static function invoiceStatu($type){
        $state = [
            1=>'<span class="badge badge-inverse text-white p-1"><i class="fal fa-dot-circle"></i> Receipt Invoice</span>',
            2=>'<span class="badge badge-primary text-white p-1"><i class="fal fa-dot-circle"></i> Invoice</span>',
            3=>'<span class="badge badge-dark text-white p-1"><i class="fal fa-dot-circle"></i> Unknown Type</span>'
        ];
        return $state[$type];
    }
    public static function paymentStatu($type){
        $state = [
            0=>'<span class="badge badge-outline-danger text-white p-1"><i class="fal fa-user-lock"></i> unknown status</span>',
            1=>'<span class="badge badge-success text-white p-1"><i class="fal fa-check-circle"></i> done</span>',
            2=>'<span class="badge badge-inverse text-white p-1"><i class="fal fa-history"></i> waiting</span>',
            3=>'<span class="badge badge-primary text-white p-1"><i class="fal fa-times-circle"></i> waiting for transfer</span>',
            4=>'<span class="badge badge-danger text-white p-1"><i class="fal fa-times-circle"></i> refunded</span>',
        ];
        return $state[$type];
    }
    public static function paymentStatuforAgent($type){
        $state = [
            0=>'<span class="badge badge-warning text-white p-1"><i class="fal fa-history"></i> waiting</span>',
            1=>'<span class="badge badge-success text-white p-1"><i class="fal fa-check-circle"></i> payed</span>',
            2=>'<span class="badge badge-danger text-white p-1"><i class="fal fa-times-circle"></i> cancelled</span>',
            3=>'<span class="badge badge-outline-danger text-white p-1"><i class="fal fa-user-lock"></i> unknown status</span>',
            null=>'<span class="badge badge-danger text-white p-1"><i class="fal fa-user-lock"></i> unknown status</span>'
        ];
        return $state[$type];
    }
    public static function employeeStatus($type){
        $statu = [
            1=>'<span class="badge badge-success text-white p-1"><i class="fal fa-check-circle"></i> Active</span>',
            2=>'<span class="badge badge-danger text-white p-1"><i class="fal fa-times-circle"></i> Fired</span>',
            3=>'<span class="badge badge-danger text-white p-1"><i class="fal fa-times-circle"></i> reSigned</span>',
            4=>'<span class="badge badge-outline-danger text-white p-1"><i class="fal fa-user-lock"></i> Unknown Status</span>',
            5=>'<span class="badge badge-inverse text-white p-1"><i class="fal fa-history"></i> Waiting</span>',
        ];
        return $statu[$type];
    }
    public static function affiliateStatus($type){
        $statu = [
            0=>'<span class="badge badge-inverse text-white p-1"><i class="fal fa-history"></i> Waiting Approve</span>',
            1=>'<span class="badge badge-success text-white p-1"><i class="fal fa-check-circle"></i> Active</span>',
            2=>'<span class="badge badge-danger text-white p-1"><i class="fal fa-times-circle"></i> Rejected</span>',
            3=>'<span class="badge badge-warning text-dark p-1"><i class="fal fa-user-cog"></i> Needs Action</span>',
            4=>'<span class="badge badge-primary text-white p-1"><i class="fal fa-user-lock"></i> Duplicate Record</span>',
        ];
        return $statu[$type];
    }

    public static function operationStatus($status){
        $state = [
            0=>'<span class="badge badge-inverse text-white p-1"><i class="fal fa-dot-circle"></i> Waiting</span>',
            1=>'<span class="badge badge-success text-white p-1"><i class="fal fa-check-circle"></i> Done</span>',
            4=>'<span class="badge badge-success text-white p-1"><i class="fal fa-check-circle"></i> At Hospital</span>',
            5=>'<span class="badge badge-success text-white p-1"><i class="fal fa-check-circle"></i> Discharged</span>',
            3=>'<span class="badge badge-warning text-white p-1"><i class="fal fa-dot-circle"></i> Unknown Status</span>',
            8=>'<span class="badge badge-primary text-white p-1"><i class="fal fa-history"></i> Waiting New Date</span>',
            9=>'<span class="badge badge-danger text-white p-1"><i class="fal fa-times"></i> Cancelled</span>',
        ];
        return $state[$status];
    }

    public static function smsStatus($status){
        $state = [
            0=>'<span class="badge badge-inverse text-white p-1"><i class="fal fa-dot-circle"></i> Waiting</span>',
            1=>'<span class="badge badge-success text-white p-1"><i class="fal fa-check-circle"></i> SMS Sent</span>',
            2=>'<span class="badge badge-warning text-white p-1"><i class="fal fa-dot-circle"></i> Unknown Status</span>'
        ];
        return $state[$status];
    }

    public static function operationStatusHeader($status){
        $state = [
            0=>'<span class="badge badge-inverse text-white mt-2" style="position:relative;float:right;padding:5px;"><i class="fal fa-dot-circle"></i> Waiting</span>',
            1=>'<span class="badge badge-secondary text-white mt-2" style="position:relative;float:right;padding:5px;"><i class="fal fa-check-circle"></i> Surgery Performed</span>',
            4=>'<span class="badge badge-success text-white mt-2" style="position:relative;float:right;padding:5px;"><i class="fal fa-check-circle"></i> At Hospital</span>',
            5=>'<span class="badge badge-dark text-white mt-2" style="position:relative;float:right;padding:5px;"><i class="fal fa-check-circle"></i> Discharged</span>',
            3=>'<span class="badge badge-warning text-white mt-2" style="position:relative;float:right;padding:5px;"><i class="fal fa-dot-circle"></i> Unknown Status</span>',
            8=>'<span class="badge badge-primary text-white mt-2" style="position:relative;float:right;padding:5px;"><i class="fal fa-history"></i> Waiting New Date</span>',
            9=>'<span class="badge badge-danger text-white mt-2" style="position:relative;float:right;padding:5px;"><i class="fal fa-times"></i> Cancelled</span>',
        ];
        return $state[$status];
    }

    public static function operationStatusText($status){
        $state = [
            0=>'<div class="status text-inverse"><i class="fal fa-dot-circle"></i> Waiting</div>',
            1=>'<div class="status text-success"><i class="fal fa-check-circle"></i> Done</div>',
            4=>'<div class="status text-success"><i class="fal fa-check-circle"></i> At Hospital</div>',
            5=>'<div class="status text-success"><i class="fal fa-check-circle"></i> Discharged</div>',
            3=>'<div class="status text-warning"><i class="fal fa-dot-circle"></i> Unknown Status</div>',
            8=>'<div class="status text-primary"><i class="fal fa-history"></i> Waiting New Date</div>',
            9=>'<div class="status text-danger"><i class="fal fa-times"></i> Cancelled</div>',
        ];
        return $state[$status];
    }
    public static function operationStatusTR($status){
        $state = [
            0=>'<span class="badge badge-inverse text-white p-1"><i class="fal fa-dot-circle"></i> Bekliyor</span>',
            1=>'<span class="badge badge-success text-white p-1"><i class="fal fa-check-circle"></i> Tamamlandı</span>',
            4=>'<span class="badge badge-success text-white p-1"><i class="fal fa-check-circle"></i> Yatışı Yapıldı</span>',
            5=>'<span class="badge badge-success text-white p-1"><i class="fal fa-check-circle"></i> Taburcu Edildi</span>',
            3=>'<span class="badge badge-warning text-white p-1"><i class="fal fa-dot-circle"></i> Bilinmiyor</span>',
            8=>'<span class="badge badge-primary text-white p-1"><i class="fal fa-history"></i> Yeni Tarih Bekliyor</span>',
            9=>'<span class="badge badge-danger text-white p-1"><i class="fal fa-times"></i> İptal</span>',
        ];
        return $state[$status];
    }

    public static function asaScore($score){
        $scores = array(
            0=> 'Not Set',
            1=> 'ASA I (A normal healthy patient)',
            2=> 'ASA II (A patient with mild systemic disease)',
            3=> 'ASA III (A patient with severe systemic disease)',
            4=> 'ASA IV (A patient with severe systemic disease that is a constant threat to life)',
            5=> 'ASA V (A moribund patient who is not expected to survive without the operation)',
            6=> 'ASA VI (A declared brain-dead patient whose organs are being removed for donor purposes)',
        );
        return $scores[$score];
    }

    public static function paymentMethod($method){
        $state = [
            1=>'<span class="badge badge-inverse text-white p-1"><i class="fal fa-dot-circle"></i> BH UK Account</span>',
            2=>'<span class="badge badge-warning text-white p-1"><i class="fal fa-check-circle"></i> SB UK Account</span>',
            3=>'<span class="badge badge-success text-white p-1"><i class="fal fa-dot-circle"></i> Cash</span>',
            4=>'<span class="badge badge-primary text-white p-1"><i class="fal fa-history"></i> BI POS</span>',
            5=>'<span class="badge badge-danger text-white p-1"><i class="fal fa-times"></i> Other</span>',
        ];
        return $state[$method];
    }

    public static function paymentMethod2023($method){
        $state = [
            6=>'<span class="badge badge-inverse text-white p-1"><i class="fal fa-dot-circle"></i> Bariatric Istanbul Brazil</span>',
            1=>'<span class="badge badge-secondary text-white p-1"><i class="fal fa-check-circle"></i> Bariatric Istanbul Wise</span>',
            3=>'<span class="badge badge-success text-white p-1"><i class="fal fa-dot-circle"></i> Cash</span>',
            4=>'<span class="badge badge-primary text-white p-1"><i class="fal fa-credit-card"></i> Bariatric Istanbul Online</span>',
            5=>'<span class="badge badge-danger text-white p-1"><i class="fal fa-times"></i> Other</span>',
            7=>'<span class="badge badge-danger text-white p-1"><i class="fal fa-credit-card"></i> Credit Card - Akbank</span>',
            8=>'<span class="badge badge-success text-white p-1"><i class="fal fa-credit-card"></i> Credit Card - Garanti</span>',
            9=>'<span class="badge badge-primary text-white p-1"><i class="fal fa-check-circle"></i> Bariatric Istanbul Silverbird</span>',
        ];
        return $state[$method];
    }
    public static function NewPaymentMethodStyle($method){
        $state = [
            1=>'<span class="badge text-success p-1"><i class="fas fa-sack-dollar"></i> Cash</span>',
            2=>'<span class="badge text-danger p-1"><i class="fab fa-cc-visa"></i> Credit Card</span>',
            3=>'<span class="badge text-behance p-1"><i class="fas fa-gem"></i> BI Wise EUR</span>',
            4=>'<span class="badge text-facebook p-1"><i class="fas fa-gem"></i> BI Wise GBP</span>',
            5=>'<span class="badge text-warning p-1"><i class="fas fa-university"></i> BI Garanti Bank EUR</span>',
            6=>'<span class="badge text-linkedin p-1"><i class="fas fa-university"></i> BI Garanti Bank GBP</span>',
            7=>'<span class="badge text-info p-1"><i class="fas fa-container-storage"></i> Simone Bello UK Account</span>',
            8=>'<span class="badge text-info p-1"><i class="fas fa-container-storage"></i> Simone Bello BR Account</span>',
            9=>'<span class="badge text-dribbble p-1"><i class="fab fa-cc-stripe"></i> Online Credit Card Payment</span>',
            10=>'<span class="badge text-github p-1"><i class="fas fa-ethernet"></i> Others</span>'
        ];
        return $state[$method];
    }




    public static function HospitalAccountant($id){
        $accountants = [0=>'undetected', 1=>'Kader Polat', 2=>'Eda Çiçek', 3=>'Tuğba Şahin'];
        return $accountants[$id];
    }
    public static function GetAllHospitalAccountant(){
        return [1=>'Kader Polat', 2=>'Eda Çiçek', 3=>'Tuğba Şahin'];
    }

    public static function private_str($str, $start, $end){
        /*$end=2;
        $secretName = "";
        $exp = explode(' ', $str);
        foreach ($exp as $name){
            $after = mb_substr($name, 0, $start, 'utf8');
            $repeat = str_repeat('*', $end);
            $before = mb_substr($name, (strlen($name) + $end), strlen($name), 'utf8');
            $secretName.= " ".$after.$repeat.$before;
        }

        //return$secretName;


        return $after.$repeat.$before;

        /*$str_array =str_split($str);
        foreach($str_array as $key => $char) {
            if($key == 0 || $key == count($str_array)-1) continue;
            if($char != '-') $str[$key] = '*';
        }
        return $str;*/

        $target = $str;
        $count = strlen($target) - $end;
        $output = substr_replace($target, str_repeat('*', $count), $start, $count);
        echo $output;
    }

    public static function trDate($format, $datetime = 'now'){
        $z = date("$format", strtotime($datetime));
        $gun_dizi = array(
            'Monday'    => 'Pazartesi',
            'Tuesday'   => 'Salı',
            'Wednesday' => 'Çarşamba',
            'Thursday'  => 'Perşembe',
            'Friday'    => 'Cuma',
            'Saturday'  => 'Cumartesi',
            'Sunday'    => 'Pazar',
            'January'   => 'Ocak',
            'February'  => 'Şubat',
            'March'     => 'Mart',
            'April'     => 'Nisan',
            'May'       => 'Mayıs',
            'June'      => 'Haziran',
            'July'      => 'Temmuz',
            'August'    => 'Ağustos',
            'September' => 'Eylül',
            'October'   => 'Ekim',
            'November'  => 'Kasım',
            'December'  => 'Aralık',
            'Mon'       => 'Pts',
            'Tue'       => 'Sal',
            'Wed'       => 'Çar',
            'Thu'       => 'Per',
            'Fri'       => 'Cum',
            'Sat'       => 'Cts',
            'Sun'       => 'Paz',
            'Jan'       => 'Oca',
            'Feb'       => 'Şub',
            'Mar'       => 'Mar',
            'Apr'       => 'Nis',
            'Jun'       => 'Haz',
            'Jul'       => 'Tem',
            'Aug'       => 'Ağu',
            'Sep'       => 'Eyl',
            'Oct'       => 'Eki',
            'Nov'       => 'Kas',
            'Dec'       => 'Ara',
        );
        foreach($gun_dizi as $en => $tr){
            $z = str_replace($en, $tr, $z);
        }
        if(strpos($z, 'Mayıs') !== false && strpos($format, 'F') === false) $z = str_replace('Mayıs', 'May', $z);
        return $z;
    }


    public static function activeSideLink($currectPage, $page){
        if($currectPage == $page){
            echo 'active';
        }
    }

    public static function isWeekend($date) {
        return (date('N', strtotime($date)) >= 7);
    }

    public static function getLanguage($id){
        $languages = array(1=>'English', 2=>'Portuguese', 3=>'Spanish', 4=>'Turkish', 5=>'German', 6=>'French', 7=>'Italian', 8=>'Albanian', 9=>'Romanian', 10=>'Arabic');
        return $languages[$id];
    }
    public static function getLanguageCode($id){
        $languages = array(1=>'en', 2=>'pt', 3=>'es', 4=>'tr', 5=>'de', 6=>'fr', 7=>'it', 8=>'al', 9=>'ro', 10=>'ar');
        return $languages[$id];
    }
    public static function setLanguageLocale($id){
        $languages = array(
            1   => 'en_GB',
            2   => 'pt_PT',
            3   => 'es_ES',
            4   =>'tr_TR',
            5   =>'de_DE',
            6   =>'fr_FR',
            7   =>'it_IT',
            8   =>'sq_AL',
            9   =>'ro_RO',
            10  =>'ar_SA');
        return $languages[$id];
    }

    public static function getAllLanguages(){
        $languages = array(
            array('id'=>1, 'language'=>'English', 'pt'=>'Inglês', 'es'=>'Inglés', 'tr'=>'İngilizce', 'de'=>'Englisch', 'fr'=>'Anglais'),
            array('id'=>2, 'language'=>'Portuguese', 'pt'=>'Português', 'es'=>'Portugués', 'tr'=>'Portekizce', 'de'=>'Portugiesisch', 'fr'=>'Portugais'),
            array('id'=>3, 'language'=>'Spanish', 'pt'=>'Espanhol', 'es'=>'Español', 'tr'=>'İspanyolca', 'de'=>'Spanisch', 'fr'=>'Espagnol'),
            array('id'=>4, 'language'=>'Turkish', 'pt'=>'Turco', 'es'=>'Turco', 'tr'=>'Türkçe', 'de'=>'Türkisch', 'fr'=>'Turc'),
            array('id'=>5, 'language'=>'German', 'pt'=>'Alemão', 'es'=>'Alemán', 'tr'=>'Almanca', 'de'=>'Deutsch', 'fr'=>'Allemand'),
            array('id'=>6, 'language'=>'French', 'pt'=>'Francês', 'es'=>'Francés', 'tr'=>'Fransızca', 'de'=>'Französisch', 'fr'=>'Français'),
            array('id'=>7, 'language'=>'Italian', 'pt'=>'Francês', 'es'=>'Francés', 'tr'=>'Fransızca', 'de'=>'Französisch', 'fr'=>'Français'),
            array('id'=>8, 'language'=>'Albanian', 'pt'=>'Francês', 'es'=>'Francés', 'tr'=>'Fransızca', 'de'=>'Französisch', 'fr'=>'Français'),
            array('id'=>9, 'language'=>'Romanian', 'pt'=>'Francês', 'es'=>'Francés', 'tr'=>'Fransızca', 'de'=>'Französisch', 'fr'=>'Français'),
            array('id'=>10, 'language'=>'Arabic', 'pt'=>'Francês', 'es'=>'Francés', 'tr'=>'Fransızca', 'de'=>'Französisch', 'fr'=>'Français')
        );
        return $languages;
    }
    public static function getAllPackages(){
        $packages = array(
            array('id'=>1, 'package'=>'Basic'),
            array('id'=>2, 'package'=>'Bronze'),
            array('id'=>3, 'package'=>'Gold'),
            array('id'=>4, 'package'=>'Platinum'),
            array('id'=>5, 'package'=>'Premium')
        );
        return $packages;
    }
    public static function getPackageEn($id, $class){
        $packages = array(0=>'No Package Setted! <strong class="text-danger">Update!</strong>', 1=>'<div class="text-'.$class.'" style="display: contents;">Basic</div>', 2=>'<div class="text-'.$class.'" style="display: contents;">Bronze</div>', 3=>'<div class="text-'.$class.'" style="display: contents;">Gold</div>', 4=>'<div class="text-'.$class.'" style="display: contents;">Platinum</div>', 5=>'<div class="text-'.$class.'" style="display: contents;">Premium</div>');
        return $packages[$id];
    }
    public static function getPackage($id){
        $packages = array(0=>'Paket Seçilmedi! <strong class="text-danger">Mutlaka Güncelle!</strong>', 1=>'<strong class="text-primary">Basic</strong>', 2=>'<strong class="text-secondary">Bronze</strong>', 3=>'<strong class="text-warning">Gold</strong>', 4=>'<strong class="text-danger">Platinum</strong>', 5=>'<strong class="text-danger">Platinum</strong>');
        return $packages[$id];
    }
    public static function getDeposit($id){
        $status = array(0=>'Bilgi Eksik! <strong class="text-danger">Mutlaka Güncelle!</strong>', 1=>'<strong class="text-success">Veri Girişi Başarılı</strong>', 2=>'<strong class="text-secondary">Bilinmiyor!</strong>');
        return $status[$id];
    }

    public static function dayPassed($date1){
        $date1 = strtotime($date1);
        $date2 = strtotime(date('Y-m-d'));
        $diff = $date2 - $date1;
        return floor($diff / (60 * 60 * 24));
    }

    public static function getMessage($type, $name, $lang, $data){
        $messages = array(
            1 => array(
                1 => array('message' => 'Hello, '.$name.'! How are you? *This is the official Whatsapp support line of Bariatric Istanbul Türkiye*. It\'s been exactly *_'.$data['dayPassed'].'_* since your weight loss surgery! I hope all is well. If you have any problems or questions, do not hesitate to contact me. I\'m always just a message away.'),//english
                2 => array('message' => 'Olá, '.$name.'! Como você está? *Esta é a linha de suporte oficial do Whatsapp da Bariátrica Istanbul Türkiye*. Já se passaram exatamente *_'.$data['dayPassed'].'_* dias desde a operação de perda de peso! Espero que tudo esteja bem. Se tiver algum problema ou dúvida, não hesite em entrar em contato comigo. Estou sempre a apenas uma mensagem de distância. '),//portuguese
                3 => array('message' => ''),//spanish
                4 => array('message' => ''),//turkish
                5 => array('message' => ''),//german
                6 => array('message' => ''),//french
            ),
        );
        return $messages[$type][$lang];
    }




    //finance

    public static function paymentTypes(){
        $types = array(
            1   => array('en'=>'Patient Down-Payment Payment', 'tr'=>'Peşinat Ödemesi'),
            2   => array('en'=>'Patient Surgery Payment','tr'=>'Cerrahi Ödemesi'),
            3   => array('en'=>'Patient Extra Payment','tr'=>'Diğer Cerrahi Ödemesi'),
            4   => array('en'=>'Hospital Charge','tr'=>'Hastane Masrafı'),
            5   => array('en'=>'Hospital Extra Charge','tr'=>'Ekstra Hastane Masrafı'),
            6   => array('en'=>'Doctor Charge','tr'=>'Doktor Hakedişi'),
            7   => array('en'=>'Agent Charge','tr'=>'Temsilci Hakedişi'),
            8   => array('en'=>'Hotel Charge','tr'=>'Otel Masrafı'),
            9   => array('en'=>'Transport Charge','tr'=>'Transfer Masrafı'),
            10   => array('en'=>'Product Charge','tr'=>'Ürün Masrafı'),
            11   => array('en'=>'Equipment Charge','tr'=>'Cerrahi Malzeme Masrafı'),
            12   => array('en'=>'Medicine Charge','tr'=>'İlaç Masrafı'),
            13   => array('en'=>'Extra Expenses','tr'=>'Diğer Ekstra Masraf'),
            14   => array('en'=>'Credit Card Commission Fee - Bank','tr'=>'Banka Kredi Kartı Komisyonu'),
            15   => array('en'=>'Credit Card Commission Fee - Stripe','tr'=>'Yurtdışı Banka Kredi Kartı Komisyonu'),
        );
        return $types;
    }
    public static function paymentTypeTR($type){
        $types = array(
            1   => array('en'=>'Patient Down-Payment Payment', 'tr'=>'Peşinat Ödemesi'),
            2   => array('en'=>'Patient Surgery Payment','tr'=>'Cerrahi Ödemesi'),
            3   => array('en'=>'Patient Extra Payment','tr'=>'Diğer Cerrahi Ödemesi'),
            4   => array('en'=>'Hospital Charge','tr'=>'Hastane Masrafı'),
            5   => array('en'=>'Hospital Extra Charge','tr'=>'Ekstra Hastane Masrafı'),
            6   => array('en'=>'Doctor Charge','tr'=>'Doktor Hakedişi'),
            7   => array('en'=>'Agent Charge','tr'=>'Temsilci Hakedişi'),
            8   => array('en'=>'Hotel Charge','tr'=>'Otel Masrafı'),
            9   => array('en'=>'Transport Charge','tr'=>'Transfer Masrafı'),
            10   => array('en'=>'Product Charge','tr'=>'Ürün Masrafı'),
            11   => array('en'=>'Equipment Charge','tr'=>'Cerrahi Malzeme Masrafı'),
            12   => array('en'=>'Medicine Charge','tr'=>'İlaç Masrafı'),
            13   => array('en'=>'Extra Expenses','tr'=>'Diğer Ekstra Masraf'),
            14   => array('en'=>'Credit Card Commission Fee - Bank','tr'=>'Banka Kredi Kartı Komisyonu'),
            15   => array('en'=>'Credit Card Commission Fee - Stripe','tr'=>'Yurtdışı Banka Kredi Kartı Komisyonu'),
        );
        return $types[$type]['tr'];
    }
    public static function paymentType($type){
        $types = array(
            1   => 'Patient Down-Payment Payment',
            2   => 'Patient Surgery Payment',
            3   => 'Patient Extra Payment',
            4   => 'Hospital Charge',
            5   => 'Hospital Extra Charge',
            6   => 'Doctor Charge',
            7   => 'Agent Charge',
            8   => 'Hotel Charge',
            9   => 'Transport Charge',
            10   => 'Product Charge',
            11   => 'Equipment Charge',
            12   => 'Medicine Charge',
            13   => 'Extra Expenses',
            14   => 'Credit Card Commission Fee - Bank',
            15   => 'Credit Card Commission Fee - Stripe',
        );
        return $types[$type];
    }
    public static function transferType($type){
        $types = array(
            1   => 'Bank Transfer',
            2   => 'Cash',
            3   => 'Payed on Credit Card',
            4   => 'Payed on Online Credit Card',
            5   => 'Bank Transfer - Brazil',
            6   => 'Bank Transfer - Europa',
        );
        return $types[$type];
    }
    public static function paymentStatus($type){
        $types = array(
            1=>'<i class="fal fa-check-circle text-success"></i>',
            2=>'<i class="fas fa-clock text-warning"></i>',
            3=>'<i class="fas fa-clock text-danger"></i>',
            4=>'<i class="fas fa-times text-danger"></i>',
            5=>'<i class="fas fa-clock text-primary"></i>',
            6=>'<i class="fas fa-clock text-danger"></i>',
            0=>'<i class="fas fa-clock text-danger"></i>',
        );
        return $types[$type];
    }


    public static function fileTypes($id=NULL){
        $types = array(
            1=> 'Passport',
            2=> 'Entrance Stamp',
            3=> 'E-Ticket',
            4=> 'Hospital Declare',
            5=> 'Hospital Invoice',
            6=> 'Invoice',
            7=> 'Before Photos',
            8=> 'After Photos',
            9=> 'Other Photos',
            10=> 'Video',
            11=> 'Other Video',
            12=> 'Travel Insurance',
            13=> 'Complication Insurance'
        );
        if($id!==NULL){
            return $types[$id];
        }else{
            return $types;
        }
    }


    public static function createAuthCode($digit){
        return substr(str_shuffle("0123456789"), 0, $digit);
    }
    public static function addAuthCode($authCode, $patientID){
        $db         = database::getInstance()->connect();
        $patient    = patient::getPatient($patientID);
        $patientL   = self::getLanguageCode($patient->language);
        $languageS  = language::loadLanguageCode($patientL, 995)[0]->text;
        $languageE  = language::loadLanguageCode($patientL, 996)[0]->text;
        $data   = array(
            'patientID'     => $patientID,
            'authCode'      => $authCode,
            'used'          => 0,
            'createdate'    => date('Y-m-d H:i:s'),
            'updatedate'    => date('Y-m-d H:i:s'),
            'usedate'       => date('Y-m-d H:i:s')
        );
        $r = $db->table('otp')->insert($data);
        if($r){
            $msg = ['status'=>1,'message'=>$languageS, 'type'=>'otp'];
        }else{
            $msg = ['status'=>2,'message'=>$languageE, 'type'=>'otp'];
        }
        echo json_encode($msg);
    }





    public static function getAgentTitle($role){
        $roles = [
            'manager'   => 'System Manager',
            'agent'     => 'Salesperson',
            'nurse'     => 'Nurse',
            'dietitian' => 'Dietitian'
        ];
        return $roles[$role];
    }

}