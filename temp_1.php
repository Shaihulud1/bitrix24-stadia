<?php
    session_start();
    require_once 'Classes/Bitrix24.php';
    
    $_SESSION['MEMBER_ID'] = '37cbe8c3f80d02ab2e3c5bbe8fa46b8a';
    $_SESSION['AUTH_ID'] = '4jb0ffdf9azuguzm6rrk0ib4wzap3290';
//    $_SESSION['REFRESH_ID'] =($_REQUEST['REFRESH_ID']);
    $_SESSION['PROTOCOL'] = 1;
    $_SESSION['DOMAIN'] = 'vkbitrix24.bitrix24.ru';
    $_SESSION['userID'] = '';

    $params = [];
    $params['TITLE'] = "api";
    
    $lead = new Lead();
    $list = $lead->addLead($params);
    print_r($list);
