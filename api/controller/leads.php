<?php
class leads{
    private $db;
    private static $instance;
    public static function getInstance() {
        if (!leads::$instance instanceof self) {
            leads::$instance = new self();
        }
        return leads::$instance;
    }

    public function __construct() {
        $this->db        = database::getInstance()->connect();
    }




    public function allLeads($agent){
        $db = $this->db;
        return $db->table('leads')->where('agent', $agent)->orderBy('order_num', 'ASC')->getAll();
    }
    public function allLeadsOrder($orderColumn, $sort){
        $db = $this->db;
        return $db->table('leads')->orderBy($orderColumn, $sort)->getAll();
    }
    public function getLeads($id){
        $db = $this->db;
        return $db->table('leads')->where('id', $id)->get();
    }
}