<?php
class database{
    private static $instance;
    public static function getInstance() {
        if (!database::$instance instanceof self) {
            database::$instance = new self();
        }
        return database::$instance;
    }
    public static function connect(){
        $configProd = [
            'host'		=> 'localhost',
            'driver'	=> 'mysql',
            'database'	=> 'bariatric_whatsapp',
            'username'	=> 'bariatric_whatsappuser',
            'password'	=> 'yVz3191:;ArC',
            'charset'	=> 'utf8mb4',
            'collation'	=> 'utf8mb4_unicode_ci',
            'prefix'	 => ''
        ];

        $configLocal = [
            'host'		=> 'localhost',
            'driver'	=> 'mysql',
            'database'	=> 'bicrm',
            'username'	=> 'root',
            'password'	=> '',
            'charset'	=> 'utf8',
            'collation'	=> 'utf8_general_ci',
            'prefix'	 => ''
        ];
        $db = new \Buki\Pdox($configProd);

        return $db;
    }
}