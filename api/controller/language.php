<?php
class language{
    private static $instance;
    public static function getInstance() {
        if (!language::$instance instanceof self) {
            language::$instance = new self();
        }
        return language::$instance;
    }

    public static function loadLanguage($lang){
        if (!isset($lang)){$lang = 'en';}else {$lang = $lang;}
        $db = database::getInstance()->connect();
        return $db->table('language')->where('language', $lang)->getAll();
    }
    public static function loadLanguageCode($lang, $code){
        $db = database::getInstance()->connect();
        return $db->table('language')->where('value', $code)->where('language', $lang)->getAll();
    }
}