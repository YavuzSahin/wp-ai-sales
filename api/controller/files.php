<?php
class files{
    public function __constructor(){}
    private static $instance;
    public static function getInstance() {
        if (!files::$instance instanceof self) {
            files::$instance = new self();
        }
        return files::$instance;
    }

    public static function checkPatientFile($id, $type){
        $db         = database::getInstance()->connect();
        $result     = $db->table('patient_files')->where('patientID', $id)->where('type', $type)->get();
        if(isset($result->id)){
            return $result;
        }else{
            return 'no_data';
        }
    }
}