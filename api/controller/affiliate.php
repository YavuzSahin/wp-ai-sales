<?php
class affiliate{
    private static $instance;
    protected static $chars = "abcdfghjkmnpqrstvwxyz|ABCDFGHJKLMNPQRSTVWXYZ|0123456789";
    public static function getInstance(){
        if (!affiliate::$instance instanceof self) {
            affiliate::$instance = new self();
        }
        return affiliate::$instance;
    }

    public static function getAllAffiliates($type){
        $db = database::getInstance()->connect();
        return $db->table('bap_user')->in('approve', $type)->getAll();
    }
    public static function getAffiliate($id){
        $db = database::getInstance()->connect();
        return $db->table('bap_user')->where('id', $id)->get();
    }
    public static function getAffiliateQR($id){
        $db = database::getInstance()->connect();
        return $db->table('bap_user')->where('qrLink', $id)->get();
    }
    public static function getAffiliateClick($id){
        $db = database::getInstance()->connect();
        $db->table('bap_clicks')->where('affiliateID', $id)->getAll();
        return $db->numRows();
    }
    public static function updateAffiliateLink($id, $link){
        $db = database::getInstance()->connect();
        $db->table('bap_user')->where('id', $id)->update(array('qrLink'=>$link, 'updatedate'=>date('Y-m-d H:i:s')));
        return 1;
    }

    public static function checkAffiliateLinkEmpty($id){
        $db = database::getInstance()->connect();
        $r = $db->table('bap_user')->where('id', $id)->get();
        if(empty($r->qrLink) || $r->qrLink==NULL){return 1;}else{return 0;}
    }
    public static function checkAffiliateLink($qrLink){
        $db = database::getInstance()->connect();
        $db->table('bap_user')->where('qrLink', $qrLink)->getAll();
        $num = $db->numRows();
        if($num>=1){return 1;}else{return 0;}
    }

    public static function getInstagramAccountFollowerCount($instagramHandle){

    }

    public static function generateRandomString($length = 6){
        $sets = explode('|', self::$chars);
        $all = '';
        $randString = '';
        foreach($sets as $set){
            $randString .= $set[array_rand(str_split($set))];
            $all .= $set;
        }
        $all = str_split($all);
        for($i = 0; $i < $length - count($sets); $i++){
            $randString .= $all[array_rand($all)];
        }
        $randString = str_shuffle($randString);
        return $randString;
    }
}