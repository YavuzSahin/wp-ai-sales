<?php
class employee{
    private static $instance;

    public static function getInstance(){
        if (!employee::$instance instanceof self) {
            employee::$instance = new self();
        }
        return employee::$instance;
    }

    public static function getAllEmployees($type){
        $db = database::getInstance()->connect();
        //if(!isset($type) || $type=='all'){$type= array(1,2);}else{$type=array($type);}
        //$type=array($type);
        return $db->table('employees')->where('workType', 1)->in('type', $type)->getAll();
    }
    public static function getEmployee($id){
        $db = database::getInstance()->connect();
        return $db->table('employees')->where('id', $id)->get();
    }
    public static function getEmployeeAgentID($id){
        $db = database::getInstance()->connect();
        return $db->table('employees')->where('agentID', $id)->get();
    }
}