<?php
class patient{
    private static $instance;
    public static function getInstance() {
        if (!patient::$instance instanceof self) {
            patient::$instance = new self();
        }
        return patient::$instance;
    }

    public static function getPatient($id){
        $db = database::getInstance()->connect();
        return $db->table('patient')->where('id', $id)->get();
    }
    public static function getPatientwithPN($id){
        $db = database::getInstance()->connect();
        $db->table('patient')->where('passportNumber', $id)->getAll();
        return $db->numRows();
    }
    public static function getAllPatients($value, $option=null){
        $db = database::getInstance()->connect();
        if($value=='all') {
            $results =  $db->select('patient.id,patient.name,patient.passportNumber,patient.gender, patient.birthDate')->table('patient as patient')->getAll();
            // $db->getQuery();
        }elseif($value=='waiting'){
            $results = $db->select('patient.id,patient.name,patient.passportNumber,patient.gender,patient.birthDate,patient_operation.patientID')->table('patient as patient')->Join('patient_operation as patient_operation', 'patient.id', 'patient_operation.patientID')->where('patient_operation.operationStatus', 0)->getAll();
            // $db->getQuery();
        }elseif($value=='done'){
            $results = $db->select('patient.id,patient.name,patient.passportNumber,patient.gender,patient.birthDate,patient_operation.patientID')->table('patient as patient')->Join('patient_operation as patient_operation', 'patient.id', 'patient_operation.patientID')->where('patient_operation.operationStatus', 1)->getAll();
            // $db->getQuery();
        }elseif($value=='gender'){
            $results = $db->select('patient.id,patient.name,patient.passportNumber,patient.phoneNumber,patient.gender,patient.birthDate,patient_operation.patientID')->table('patient as patient')->Join('patient_operation as patient_operation', 'patient.id', 'patient_operation.patientID')->where('patient_operation.operationStatus', 1)->where('patient.gender', $option['gender'])->where('patient.gender', $option['language'])->getAll();
            // $db->getQuery();
        }
        return $results;
    }
    public static function getOperatedPatients(){
        $db = database::getInstance()->connect();
        return $db->table('patient')->where('status', 1)->getAll();
    }
    public static function getAwaitingPatients(){
        $db = database::getInstance()->connect();
        return $db->table('patient')->where('status', 0)->getAll();
    }
    public static function getNewPatients(){
        $db = database::getInstance()->connect();
        return $db->table('patient')->where('status', 2)->getAll();
    }
    public static function getPatientsAgent($id){
        if($id!==null){
            $db     = database::getInstance()->connect();
            return $db->table('manager')->where('id', $id)->get();
        }else{
            $agent          = new ArrayObject();
            $agent->name    = 'Unknown Agent!';
            return $agent;
        }
    }
    public static function getPatientsNoConsultation(){
        $db = database::getInstance()->connect();
        return $db->table('patient')->where('consultation', 0)->getAll();
    }
    public static function getPatientOperation($id){
        $db = database::getInstance()->connect();
            return $db->table('patient_operation')->where('patientID', $id)->get();
    }
    public static function getAllOperations($id){
        $db = database::getInstance()->connect();
            return $db->table('patient_operation')->where('patientID', $id)->getAll();
    }
    public static function getPatientOperationDate($date){
        $db = database::getInstance()->connect();
        $dateFirst  = date($date.' 00:00:00');
        $dateLast   = date($date.' 23:59:00');
            return $db->table('patient_operation')->between('operationDate', $dateFirst, $dateLast)->getAll();
    }
    public static function getPatientArrivalsDate($date){
        $db = database::getInstance()->connect();
        $dateFirst  = date($date.' 00:00:00');
        $dateLast   = date($date.' 23:59:00');
            return $db->table('patient_operation')->between('arriveDate', $dateFirst, $dateLast)->getAll();
    }
    public static function getPatientOperationPlan($month, $doctorID){
        $db         = database::getInstance()->connect();
        $month      = $month<=9 ? "0".$month:$month;
        $days       = cal_days_in_month(CAL_GREGORIAN, $month, date('Y'));
        $dateFirst  = date('2023-'.$month.'-01 00:00:00');
        $dateLast   = date('2023-'.$month.'-'.$days.' 23:59:00');
        if ($doctorID=='all') {
            return $db->table('patient_operation')->in('operationStatus', array(0,4))->between('operationDate', $dateFirst, $dateLast)->orderBy('operationDate', 'ASC')->getAll();
        }else{
            return $db->table('patient_operation')->in('operationStatus', array(0,4))->where('doctorID', $doctorID)->between('operationDate', $dateFirst, $dateLast)->orderBy('operationDate', 'ASC')->getAll();
        }
    }
    public static function getOperationPlanForNurse($year, $month){
        $db         = database::getInstance()->connect();
        $month      = $month<=9 ? "0".$month:$month;
        $days       = cal_days_in_month(CAL_GREGORIAN, $month, date('Y'));
        $dateFirst  = date($year.'-'.$month.'-01 00:00:00');
        $dateLast   = date($year.'-'.$month.'-'.$days.' 23:59:00');
        return $db->table('patient_operation')->between('operationDate', $dateFirst, $dateLast)->orderBy('operationDate', 'ASC')->getAll();
    }
    public static function getPatientOperationPlanforAdminPDF($month, $doctorID){
        $db         = database::getInstance()->connect();
        $month      = $month<=9 ? "0".$month:$month;
        $days       = cal_days_in_month(CAL_GREGORIAN, $month, date('Y'));
        $dateFirst  = date('2023-'.$month.'-01 00:00:00');
        $dateLast   = date('2023-'.$month.'-'.$days.' 23:59:00');
        if ($doctorID=='all') {
            return $db->table('patient_operation')->between('operationDate', $dateFirst, $dateLast)->in('operationStatus', [1,4,5])->orderBy('operationDate', 'ASC')->getAll();
        }else{
            return $db->table('patient_operation')->where('doctorID', $doctorID)->in('operationStatus', [1,4,5])->between('operationDate', $dateFirst, $dateLast)->orderBy('operationDate', 'ASC')->getAll();
        }
    }
    public static function getPatientOperationPlanforAdmin($year, $month, $doctorID){
        $db         = database::getInstance()->connect();
        $month      = $month<=9 ? "0".$month:$month;
        $days       = cal_days_in_month(CAL_GREGORIAN, $month, date('Y'));
        $dateFirst  = date($year.'-'.$month.'-01 00:00:00');
        $dateLast   = date($year.'-'.$month.'-'.$days.' 23:59:00');
        if ($doctorID=='all') {
            return $db->table('patient_operation')->between('operationDate', $dateFirst, $dateLast)->orderBy('operationDate', 'ASC')->getAll();
        }else{
            return $db->table('patient_operation')->where('doctorID', $doctorID)->between('operationDate', $dateFirst, $dateLast)->orderBy('operationDate', 'ASC')->getAll();
        }
    }
    public static function getPatientOperationPlanAgents($agentID){
        $db         = database::getInstance()->connect();
        if ($agentID=='All') {
            return $db->table('patient as patient')->join('patient_operation as patient_operation', 'patient.id', 'patient_operation.patientID')->where('patient_operation.AgentCommissionStatus', 0)->notIn('patient_operation.operationStatus', [1])->getAll();
        }else{
            return $db->table('patient as patient')->join('patient_operation as patient_operation', 'patient.id', 'patient_operation.patientID')->where('patient.agent', $agentID)->where('patient_operation.AgentCommissionStatus', 0)->getAll();//->notIn('patient_operation.operationStatus', [1])
        }
    }
    public static function getPatientOperationPlanForPayments($start, $end, $doctorID){
        $db         = database::getInstance()->connect();
        $dateFirst  = date('Y-m-d 00:00:00', strtotime($start));
        $dateLast   = date('Y-m-d 23:59:59', strtotime($end));
        return $db->table('patient_operation')->where('doctorID', $doctorID)->notIn('operationStatus', [8,9])->between('operationDate', $dateFirst, $dateLast)->orderBy('operationDate', 'ASC')->getAll();
    }
    public static function getPatientOperationPlanAll($doctorID){
        $db         = database::getInstance()->connect();
        $dateFirst  = date('2022-01-01 00:00:00');
        $dateLast   = date('2026-12-31 23:59:00');
        if ($doctorID=='all') {
            return $db->table('patient_operation')->between('operationDate', $dateFirst, $dateLast)->orderBy('operationDate', 'ASC')->getAll();
        }else{
            return $db->table('patient_operation')->where('doctorID', $doctorID)->between('operationDate', $dateFirst, $dateLast)->orderBy('operationDate', 'ASC')->getAll();
        }
    }
    public static function getPatientReturnPlan($month){
        $db         = database::getInstance()->connect();
        $month      = $month<=9 ? "0".$month:$month;
        $days       = cal_days_in_month(CAL_GREGORIAN, $month, date('Y'));
        $dateFirst  = date('Y-'.$month.'-01');
        $dateLast   = date('Y-'.$month.'-'.$days);
        return $db->table('patient_operation')->in('operationStatus', [0,3])->between('returnDate', $dateFirst, $dateLast)->orderBy('returnDate', 'ASC')->getAll();
    }
    public static function getPatientarrivalPlan($month){
        $db         = database::getInstance()->connect();
        $month      = $month<=9 ? "0".$month:$month;
        $days       = cal_days_in_month(CAL_GREGORIAN, $month, date('Y'));
        $dateFirst  = date('Y-'.$month.'-01');
        $dateLast   = date('Y-'.$month.'-'.$days);
        return $db->table('patient_operation')->in('operationStatus', [0,3])->between('arriveDate', $dateFirst, $dateLast)->orderBy('arriveDate', 'ASC')->getAll();
    }
    public static function getPatientarrivalPlanWorkers($month){
        $db         = database::getInstance()->connect();
        $month      = $month<=9 ? "0".$month:$month;
        $days       = cal_days_in_month(CAL_GREGORIAN, $month, date('Y'));
        $dateFirst  = date('2023-'.$month.'-01');
        $dateLast   = date('2023-'.$month.'-'.$days);
        return $db->table('patient_operation')->in('operationStatus', [0,3])->between('arriveDate', $dateFirst, $dateLast)->orderBy('arriveDate', 'ASC')->getAll();
    }

    public static function getPatientReturnPlanWorkers($month){
        $db         = database::getInstance()->connect();
        $month      = $month<=9 ? "0".$month:$month;
        $days       = cal_days_in_month(CAL_GREGORIAN, $month, date('Y'));
        //$dateFirst  = date('Y-'.$month.'-d', '-1 days');
        $dateFirst  = date('Y-m-d', strtotime('-2 day', strtotime(date('Y-m-d'))));
        $dateLast   = date('Y-'.$month.'-'.$days);
        return $db->table('patient_operation')->in('operationStatus', [0,3])->between('returnDate', $dateFirst, $dateLast)->orderBy('returnDate', 'ASC')->getAll();
    }
    public static function getPatientPlanMonth($month){
        $db         = database::getInstance()->connect();
        $month      = $month<=9 ? "0".$month:$month;
        $days       = cal_days_in_month(CAL_GREGORIAN, $month, date('Y'));
        $dateFirst  = date('Y-'.$month.'-01');
        $dateLast   = date('Y-'.$month.'-'.$days);
        return $db->table('patient_operation')->between('arriveDate', $dateFirst, $dateLast)->orderBy('arriveDate', 'ASC')->getAll();
    }
    public static function getPatientPayments($patientID){
        $db = database::getInstance()->connect();
        return $db->table('patient_payments')->where('patientID', $patientID)->getAll();
    }
    public static function getPatientDeposits($patientID){
        $db = database::getInstance()->connect();
        return $db->select('depositAmount, depositCurrency, depositPaymentMethod, depositPaymentDate')->table('patient_operation')->where('patientID', $patientID)->get();
    }


    public static function getLanguage($id){
        $db = database::getInstance()->connect();
        return $db->select('language')->table('patient')->where('id', $id)->get();
    }





    public static function getIncomes($id){
        $db = database::getInstance()->connect();
        return $db->table('finance')->where('patientID', $id)->where('type', 1)->getAll();
    }

    public static function getExpenses($id){
        $db = database::getInstance()->connect();
        return $db->table('finance')->where('patientID', $id)->where('type', 2)->getAll();
    }
    public static function getPayment($id){
        $db = database::getInstance()->connect();
        return $db->table('finance')->where('id', $id)->get();
    }






    //new functions 23-09-2022

    public static function getAgentPatients($month, $id){
        $db         = database::getInstance()->connect();
        $month      = $month<=9 ? "0".$month:$month;
        $days       = cal_days_in_month(CAL_GREGORIAN, $month, date('Y'));
        $dateFirst  = date('Y-'.$month.'-01');
        $dateLast   = date('Y-'.$month.'-'.$days);
        return $db->table('patient as patient')->join('patient_operation as operation', 'patient.id', 'operation.patientID')->where('patient.agent', $id)->between('operation.arriveDate', $dateFirst, $dateLast)->orderBy('operation.arriveDate', 'ASC')->getAll();
    }
}