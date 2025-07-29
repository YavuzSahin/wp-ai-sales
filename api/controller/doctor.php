<?php
class doctor{
    private static $instance;
    public static function getInstance() {
        if (!doctor::$instance instanceof self) {
            doctor::$instance = new self();
        }
        return doctor::$instance;
    }

    public static function getDoctor($id){
        if($id==0){
            $doctor         = new stdClass();
            $doctor->name   = '<span value="strong text-danger">No Doctor Yet!</span>';
            return $doctor;
        }else {
            $db = database::getInstance()->connect();
            return $db->table('doctors')->where('id', $id)->get();
        }
    }
    public static function getDoctorCost($id){
        $db = database::getInstance()->connect();
        return $db->table('doctor_cost')->where('doctorID', $id)->getAll();
    }
    public static function getAllDoctors(){
        $db = database::getInstance()->connect();
        return $db->table('doctors')->where('active', 1)->where('type', 1)->getAll();
    }
    public static function getAllSurgeons(){
        $db = database::getInstance()->connect();
        return $db->table('doctors')->where('active', 1)->where('hospital', 0)->where('type', 1)->getAll();
    }
    public static function getAllSurgeonsTogether(){
        $db = database::getInstance()->connect();
        return $db->table('doctors')->where('active', 1)->where('type', 1)->getAll();
    }
    public static function getAllAnesthesiologists(){
        $db = database::getInstance()->connect();
        return $db->table('doctors')->where('type', 2)->getAll();
    }
}