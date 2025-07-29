<?php
class transfer{
    private static $instance;
    public static function getInstance() {
        if (!transfer::$instance instanceof self) {
            transfer::$instance = new self();
        }
        return transfer::$instance;
    }

    public static function getAllTransfers($month){
        $month      = $month<=9 ? "0".$month:$month;
        $days       = cal_days_in_month(CAL_GREGORIAN, $month, date('Y'));
        $dateFirst  = date('Y-'.$month.'-01 00:00:00');
        $dateLast   = date('Y-'.$month.'-'.$days.' 23:59:00');
        $db = database::getInstance()->connect();
        return $db->table('transfers')->between('transferDate', $dateFirst, $dateLast)->getAll();
    }
}