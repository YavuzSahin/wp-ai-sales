<?php
class workers{
    private static $instance;
    public static function getInstance() {
        if (!workers::$instance instanceof self) {
            workers::$instance = new self();
        }
        return workers::$instance;
    }

    public static function info($id){
        $db = database::getInstance()->connect();
        return $db->table('manager')->where('id', $id)->get();
    }
    public static function getAllManagers(){
        $db = database::getInstance()->connect();
        return $db->table('manager')->getAll();
    }
}