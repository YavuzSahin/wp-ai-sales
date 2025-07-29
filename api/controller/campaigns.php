<?php


class campaigns{
    private static $instance;
    protected static $chars = "abcdfghjkmnpqrstvwxyz|ABCDFGHJKLMNPQRSTVWXYZ|0123456789";
    public static function getInstance(){
        if (!campaigns::$instance instanceof self) {
            campaigns::$instance = new self();
        }
        return campaigns::$instance;
    }

    public static function getAllCampaigns(){
        $db = database::getInstance()->connect();
        return $db->table('campaigns')->where('status', 1)->getAll();
    }
    public static function getCampaign($id){
        $db = database::getInstance()->connect();
        return $db->table('campaigns')->where('id', $id)->get();
    }
}