<?php


class stock{
    private static $instance;

    public static function getInstance(){
        if (!stock::$instance instanceof self) {
            stock::$instance = new self();
        }
        return stock::$instance;
    }

    public static function getStockProduct($id){
        $db = database::getInstance()->connect();
        return $db->table('stock_products')->where('id', $id)->get();
    }
    public static function getAllStockProduct(){
        $db     = database::getInstance()->connect();
        return $db->table('stock_products')->getAll();
    }
    public static function getAllStorage(){
        $db     = database::getInstance()->connect();
        return $db->table('stock_storage')->where('active', 1)->getAll();
    }
    public static function getStorage($id){
        if($id==0){
            @$storage               = new ArrayObject();
            $storage->storageName   = '<strong class="text-danger">not set!</strong>';
        }else {
            $db      = database::getInstance()->connect();
            $storage =  $db->table('stock_storage')->where('id', $id)->get();
        }
        return $storage;
    }
    public static function getStock($id){
        $db     = database::getInstance()->connect();
        $r      =  $db->table('stock')->where('itemID', $id)->orderBy('id', 'DESC')->limit(1)->get();
        @$stock = new ArrayObject();
        if(!isset($r->id)){
            $stock->stockDate               = date('2020-12-31');
            $stock->stockNumber             = '<strong class="text-danger">not set!</strong>';
            $stock->storage                 =  0;
        }else{
            $stock = $r;
        }
        return $stock;
    }
}