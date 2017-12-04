<?php
    session_start();
    require_once 'Classes/Bitrix24.php';
    require_once 'Classes/Db_work.php';
    require_once 'Classes/CheckInput.php';
    
    $_SESSION['EMAIL'] = $_POST['auth']['domain']; 
    $_SESSION['DOMAIN'] = $_POST['auth']['domain'];
    $id = checkString($_POST['data']['FIELDS']['ID']);
    $_SESSION['AUTH_ID'] = checkString($_POST['auth']['access_token']);
    $_SESSION['DOMAIN']= checkString($_POST['auth']['domain']);

    $db = Database::getInstance();
    $bitrix_id = $db->fetch_query("SELECT ID FROM USER WHERE DOMAIN=(?)", [$_SESSION['DOMAIN']]);
    $bitrix_id = $bitrix_id[0]['ID'];

    $delLead = $db->do_query("DELETE FROM LEADS WHERE (BITRIXID = (?) AND LEADID = (?))", [$bitrix_id, $id]);
