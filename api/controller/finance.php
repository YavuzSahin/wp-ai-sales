<?php
class finance
{
    private static $instance;
    public static function getInstance() {
        if (!finance::$instance instanceof self) {
            finance::$instance = new self();
        }
        return finance::$instance;
    }

    public static function getIncome($id){

        return $db->table('incomes')->where('id', $id)->get();
    }
    public static function getAllIncomes($month, $year){
        $maxDay = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $start  = $year."-".$month."-01 00:00:00";
        $end    = $year."-".$month."-".$maxDay." 23:59:59";
        $db     = database::getInstance()->connect();
        return $db->table('finance')->where('type', 1)->between('paymentDate', $start, $end)->getAll();
    }
    public static function getIncomeTypes($type){
        $types = array(
            1=>'Cash',
            2=>'Bank Transfer',
            3=>'Credit Card',
            4=>'Online Payment',
            5=>'Hospital Payment'
        );
        return $types[$type];
    }
    public static function getIncomeSource($source){
        $sources = array(
            1=>'BI Cash',
            2=>'BI Bank Account',
            3=>'BH Revolut',
            4=>'BH Lloyds',
            5=>'BH Barclays',
            6=>'BH Starling',
            7=>'BH Santander Brazil',
            8=>'BI Online Payment'
        );
        return $sources[$source];
    }
    public static function getExpense($id, $type){
        $db = database::getInstance()->connect();
        return $db->table('finance')->where('patientID', $id)->where('paymentType', $type)->get();
    }
    public static function getAllExpenses($month, $year){
        $maxDay = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $start  = $year."-".$month."-01 00:00:00";
        $end    = $year."-".$month."-".$maxDay." 23:59:59";
        $db = database::getInstance()->connect();
        return $db->table('finance')->where('type', 2)->between('paymentDate', $start, $end)->getAll();
    }
    public static function getInvoice($id){
        $db = database::getInstance()->connect();
        return $db->table('invoice')->where('id', $id)->get();
    }
    public static function getAllInvoices($month, $year, $type){
        $maxDay = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $start  = $year."-".$month."-01 00:00:00";
        $end    = $year."-".$month."-".$maxDay." 23:59:59";
        $db = database::getInstance()->connect();
        return $db->table('invoice')->where('type', $type)->between('createdate', $start, $end)->getAll();
    }
    public static function getPatientAllInvoices($id){
        $db = database::getInstance()->connect();
        return $db->table('invoice')->where('patientID', $id)->notIn('type', [1])->getAll();
    }
    public static function getPatientAllInvoice($id){
        $db = database::getInstance()->connect();
        return $db->table('invoice')->where('patientID', $id)->notIn('type', [1])->get();
    }
    public static function getPatientAllExpenseInvoices($id){
        $db = database::getInstance()->connect();
        return $db->table('invoice')->where('patientID', $id)->in('type', [1])->getAll();
    }
    public static function getPatientPayments($id){
        $db = database::getInstance()->connect();
        return $db->table('patient_payment')->where('patientID', $id)->getAll();
    }
    public static function checkPatientPayment($id, $type){
        $db         = database::getInstance()->connect();
        if($type==1){
            $result = $db->table('patient_operation')->where('patientID', $id)->where('depositAmount', '>', 0)->get();
        }else {
            $result = $db->table('finance')->where('patientID', $id)->where('paymentType', $type)->get();
        }
        if(isset($result->id)){
            return $result;
        }else{
            return 'no_data';
        }
    }
    public static function getHospitalPayments($id){
        $db = database::getInstance()->connect();
        return $db->table('finance')->where('paymentType', 4)->where('patientID', $id)->get();
    }
    public static function getHospitalExtraPayments($id, $hospitalID){
        $db = database::getInstance()->connect();
        return $db->table('finance')->where('paymentType', 5)->where('hospitalID', $hospitalID)->where('patientID', $id)->getAll();
    }
    public static function getHospitalEquipmentPayments($id, $hospitalID){
        $db                 = database::getInstance()->connect();
        $hospitaltoSupplier = array(1=>15, 2=>16);
        $hospital           = $hospitaltoSupplier[$hospitalID];
        return $db->table('finance')->where('paymentType', 11)->where('supplierID', $hospital)->where('patientID', $id)->get();
    }
    public static function getDoctorPayments($id){
        $db = database::getInstance()->connect();
        return $db->table('finance')->where('paymentType', 6)->where('patientID', $id)->get();
    }
    public static function getAgentPayments($id){
        $db = database::getInstance()->connect();
        return $db->table('finance')->where('paymentType', 7)->where('patientID', $id)->get();
    }
    public static function getExpensesPayments($id){
        $db = database::getInstance()->connect();
        return $db->table('patient_payment')->in('invoiceType', array(121, 122, 123, 124, 125, 126))->where('patientID', $id)->getAll();
    }


    public static function getProduct($id){
        $db = database::getInstance()->connect();
        return $db->table('products')->where('id', $id)->get();
    }
    public static function getSurgeryItem($type, $part){
        $db = database::getInstance()->connect();
        return $db->table('products')->where('type', $type)->where('part', $part)->getAll();
    }
    public static function getPackages($type, $part){
        $db = database::getInstance()->connect();
        return $db->table('packages')->where('type', $type)->where('part', $part)->getAll();
    }
    public static function getPackage($id){
        $db = database::getInstance()->connect();
        return $db->table('packages')->where('id', $id)->get();
    }
    public static function getProductName($id){
        $db = database::getInstance()->connect();
        if($id==0){
            return "Unknown";
        }else {
            $s = $db->table('products')->where('id', $id)->get();
            return $s->productName;
        }
    }
    public static function getProductClinic($id){
        $clinic = array(
            3 => 'Genel Cerrahi', 4 => 'Genel Cerrahi', 5 => 'Genel Cerrahi', 6 => 'Genel Cerrahi', 7 => 'Genel Cerrahi', 14 => 'Genel Cerrahi', 32 => 'Genel Cerrahi', 33 => 'Genel Cerrahi', 34 => 'Genel Cerrahi', 38 => 'Genel Cerrahi', 35 => 'Estetik, Plastik ve Rekonstrüktif Cerrahi', 37=> 'Estetik, Plastik ve Rekonstrüktif Cerrahi', 39=> 'Saç Ekimi',
        );
        return $clinic[$id];
    }
    public static function getProductDoctorCost($id){
        $db = database::getInstance()->connect();
        if($id==0){
            return 0;
        }else {
            $s = $db->table('products')->where('id', $id)->get();
            return $s->doctorCost;
        }
    }
    public static function getAllProducts(){
        $db = database::getInstance()->connect();
        return $db->table('products')->notIn('type', [2])->getAll();
    }
    public static function getAllProductsWithType($type){
        $db = database::getInstance()->connect();
        return $db->table('products')->in('type', $type)->getAll();
    }
    public static function getAllSurgeries(){
        $db = database::getInstance()->connect();
        return $db->table('products')->where('type', 2)->getAll();
    }




    public static function getFinanceType($id){
       $types = array(
           1=>'Patient Down-Payment Payment',
           2=>'Patient Surgery Payment',
           3=>'Patient Extra Payment',
           4=>'Hospital Charge',
           5=>'Hospital Extra Charge',
           6=>'Doctor Charge',
           7=>'Agent Charge',
           8=>'Hotel Charge',
           9=>'Transport Charge',
           10=>'Product Charge',
           11=>'Equipment Charge',
           12=>'Medicine Charge',
           13=>'Extra Expenses',
           14=>'Credit Card Commission Fee - Bank',
           15=>'Credit Card Commission Fee - Stripe'
       );
    return $types[$id];
    }

    public static function getPaymentType($id){
        $types = array(
            1=>'Bank Transfer',
            2=>'Cash',
            3=>'Payed on Credit Card',
            4=>'Payed on Online Credit Card'
        );
        return $types[$id];
    }



    public static function getAllDoctorExpenses($month, $year, $doctorID){
        $maxDay = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $start  = $year."-".$month."-01 00:00:00";
        $end    = $year."-".$month."-".$maxDay." 23:59:59";
        $db     = database::getInstance()->connect();
        if($doctorID=='all') {
            $data = $db->table('finance')->between('operationDate', $start, $end)->where('type', 2)->where('paymentType', 6)->getAll();
        }else{
            $data = $db->table('finance')->between('operationDate', $start, $end)->where('doctorID', $doctorID)->where('paymentType', 6)->where('type', 2)->getAll();
        }
        return $data;
    }
    public static function getDoctorExpense($id){
        $db = database::getInstance()->connect();
        return $db->table('finance')->where('id', $id)->get();
    }
    public static function getAllHospitalExpenses($month, $year){
        $maxDay = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $start  = $year."-".$month."-01 00:00:00";
        $end    = $year."-".$month."-".$maxDay." 23:59:59";
        $db     = database::getInstance()->connect();
        $data   = $db->table('finance')->between('operationDate', $start, $end)->where('paymentType', 4)->where('type', 2)->getAll();
        return $data;
    }


    public static function getTransportList($start, $finish){
        $db     = database::getInstance()->connect();
        $data   = $db->table('finance')->between('paymentDate', $start, $finish)->where('paymentType', 9)->where('type', 2)->getAll();
        return $data;
    }
    public static function getDoctorSurgeryList($start, $finish, $doctorID){
        $db         = database::getInstance()->connect();
        return $db->table('patient_operation')->where('doctorID', $doctorID)->between('operationDate', $start, $finish)->orderBy('operationDate', 'ASC')->getAll();
    }
    public static function getAgentPatientList($start, $finish, $agentID, $currency){
        $db         = database::getInstance()->connect();
        return $db->table('patient as patient')->leftJoin('patient_operation as operation', 'patient.id', 'operation.patientID')->where('agent', $agentID)->between('operation.operationDate', $start, $finish)->where('operation.depositCurrency', $currency)->orderBy('operation.operationDate', 'ASC')->getAll();
    }
    public static function getHospitalSurgeryList($start, $finish, $hospital, $invoice){
        $db         = database::getInstance()->connect();
        if($invoice==0) {
            return $db->table('patient_operation')->where('hospital', $hospital)->between('operationDate', $start, $finish)->orderBy('operationDate', 'ASC')->getAll();
        }else if($invoice==1){
            return $db->table('patient_operation as operation')->where('operation.hospital', $hospital)->between('operation.operationDate', $start, $finish)->leftJoin('finance as finance', 'operation.patientID', 'finance.patientID')->where('finance.status', 1)->orderBy('operation.operationDate', 'ASC')->getAll();
        }else if($invoice==2){
            return $db->table('patient_operation as operation')->where('operation.hospital', $hospital)->between('operation.operationDate', $start, $finish)->leftJoin('finance as finance', 'operation.patientID', 'finance.patientID')->in('finance.status', [2,3,5])->orderBy('operation.operationDate', 'ASC')->getAll();
        }else if($invoice==3){
            return $db->table('patient_operation as operation')->where('operation.hospital', $hospital)->between('operation.operationDate', $start, $finish)->leftJoin('finance as finance', 'operation.patientID', 'finance.patientID')->whereNull('finance.description')->orderBy('operation.operationDate', 'ASC')->getAll();
        }else {
            return $db->table('patient_operation')->where('hospital', $hospital)->between('operationDate', $start, $finish)->orderBy('operationDate', 'ASC')->getAll();
        }
    }


}