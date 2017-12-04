<?php 
session_start();

class Logger {
    private static $_instance;
    
    public static function getInstance() {
        if(!self::$_instance) { 
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() { }

    private function __clone() { }

    private static function create_dir($member_id)
    {        
        $fileDir = dirname(__FILE__).'/'.$member_id;
        if (!file_exists($fileDir)) 
        {
            mkdir($fileDir, 0777);
        }
        return true;
    }
    
    public function log_save($text, $error_log = false)
    {   
        
        if ($_SESSION['EMAIL'] != '') 
        {
            $member_id = $_SESSION['DOMAIN'];
            //$member_id = $_SESSION['EMAIL'].'('.$_SESSION['DOMAIN'].')';   
        }else{
            $member_id = 'unknown';
        }
        $dirExisting = self::create_dir($member_id);
        if ($dirExisting)
        {
            $today = date("d.m.y");
            $now = date("d.m.y H:i:s");
            if ($error_log)
                $outText = $now." ".$_SESSION['EMAIL']." ОШИБКА: ".$text." \n";
            else
                $outText = $now." ".$_SESSION['EMAIL']." ".$text." \n";
            $filename = dirname(__FILE__).'/'.$member_id.'/'.$today.'.log';
            if (file_exists($filename))
            {
                file_put_contents($filename, $outText, FILE_APPEND | LOCK_EX);
            }
            else
            {
                file_put_contents($filename, $outText);
            }
            
        }
    }
}