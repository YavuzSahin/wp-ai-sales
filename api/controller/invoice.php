<?php
class invoice{
    private static $instance;

    public static function getInstance(){
        if (!invoice::$instance instanceof self) {
            invoice::$instance = new self();
        }
        return invoice::$instance;
    }

    public static function getAllInvoices($type){
        $db = database::getInstance()->connect();
        if($type=='all'){
            return $db->table('invoices')->getAll();
        }else {
            return $db->table('invoices')->where('invoiceType', $type)->getAll();
        }
    }
    public static function getInvoice($id){
        $db = database::getInstance()->connect();
        return $db->table('employees')->where('id', $id)->get();
    }
    public static function invoiceStatus($id){
        return '<span class="alert alert-warning">Waiting</span>';
    }
    public static function invoiceType($id){
        return '<span class="alert alert-danger">Bariatric</span>';
    }
}