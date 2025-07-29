<?php
class manager{
    private static $instance;
    public static function getInstance() {
        if (!manager::$instance instanceof self) {
            manager::$instance = new self();
        }
        return manager::$instance;
    }

    public static function info($id){
        $agent = new ArrayObject();
        $db = database::getInstance()->connect();
        $a  = $db->table('manager')->where('id', $id)->get();
        if(!$a){
              $agent->name = 'No Agent!';
        }else{
            $agent = $a;
        }
        return $agent;
    }
    public static function getAllManagers(){
        $db = database::getInstance()->connect();
        return $db->table('manager')->getAll();
    }
    public static function getAgents(){
        $agent = new ArrayObject();
        $db = database::getInstance()->connect();
        return $db->table('manager')->in('role', array(1,2))->where('status', 1)->where('description', 99)->orderBy('orderby', 'ASC')->getAll();
    }
    public static function getAgent($id){
        $db = database::getInstance()->connect();
        return $db->table('manager')->where('id', $id)->get();
    }
}