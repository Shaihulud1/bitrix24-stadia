<?php
    session_start();
    require_once 'Classes/Bitrix24.php';
    require_once 'Classes/Db_work.php';
    require_once 'Classes/CheckInput.php';
    $db = Database::getInstance(); 
    $_SESSION['DOMAIN'] = 'vkbitrix24.bitrix24.ru';
    $addLead = $db->do_query("UPDATE LEADS SET STATUS = (?) WHERE (BITRIXID = (?) AND LEADID = (?))", ['tess', 2, 72]);
    //$getLead = $db->fetch_query("SELECT STATUS FROM LEADS WHERE (BITRIXID = (?) AND LEADID = (?))", [2, 74]);
    //$getLead = $db->fetch_query("SELECT STATUS FROM LEADS", []);
    //print_r($getLead);
