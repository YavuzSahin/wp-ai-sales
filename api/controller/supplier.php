<?php
class supplier{
    private static $instance;

    public static function getInstance(){
        if (!supplier::$instance instanceof self) {
            supplier::$instance = new self();
        }
        return supplier::$instance;
    }

    public static function getAllSuppliers(){
        $db = database::getInstance()->connect();
        return $db->table('supplier')->getAll();
    }
    public static function getSupplier($id){
        $db = database::getInstance()->connect();
        return $db->table('supplier')->where('id', $id)->get();
    }
}