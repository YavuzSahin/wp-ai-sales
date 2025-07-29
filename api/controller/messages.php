<?php
class messages{
    private static $instance;
    public static function getInstance() {
        if (!messages::$instance instanceof self) {
            messages::$instance = new self();
        }
        return messages::$instance;
    }

    public static function getMessages($type, $language){
        $db = database::getInstance()->connect();
        return $db->table('message_templates')->where('code', $type)->where('language', $language)->get();
    }
}