<?php

class hospital{
    public $hospital;
    private static $instance;
    public static function getInstance() {
        if (!hospital::$instance instanceof self) {
            hospital::$instance = new self();
        }
        return hospital::$instance;
    }

    public function getHospital($id){
        $db         = database::getInstance()->connect();
        $r          = $db->table('hospital')->where('id', $id)->get();
        if(count((array)$r)>=1){
            $this->hospital['id']   = $r->id;
            $this->hospital['name'] = $r->name;
        }elseif($id==0){
            $this->hospital['id']   = 0;
            $this->hospital['name'] = '<strong class="text-danger">unsetted!</strong>';
        }else{
            $this->hospital['id']   = 0;
            $this->hospital['name'] = '<strong class="text-danger">unsetted!</strong>';
        }
        return $this->hospital;
    }
    public static function getAllHospitals(){
        $db = database::getInstance()->connect();
        return $db->table('hospital')->where('active', 1)->getAll();
    }
    public static function getAllHospitalExams($id){
        $db = database::getInstance()->connect();
        return $db->table('hospital_exams')->where('hospitalID', $id)->getAll();
    }

}