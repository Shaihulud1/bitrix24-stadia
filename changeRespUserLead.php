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

    $deal = new Lead();
    $bitrix24 = new Bitrix();
    $db = Database::getInstance();    

    $bitrix_id = $db->fetch_query("SELECT ID FROM USER WHERE DOMAIN=(?)", [$_SESSION['DOMAIN']]);
    $bitrix_id = $bitrix_id[0]['ID'];
    
    $flag = false;
    
    if ($_POST['event'] == 'ONCRMLEADADD')
    {
        $curLead = $deal->getLead($id);
        $addLead = $db->do_query("INSERT INTO LEADS (LEADID, BITRIXID, STATUS) VALUES (?, ?, ?)", [$id, $bitrix_id, $curLead['result']['STATUS_ID']]);
        $flag = true;

    }
    else {
        $curLead = $deal->getLead($id);
        $getLead = $db->fetch_query("SELECT STATUS FROM LEADS WHERE (BITRIXID = (?) AND LEADID = (?))", [$bitrix_id, $id]);
        $getLead = $getLead[0]['STATUS'];
        if ($getLead != $curLead['result']['STATUS_ID'])
        {
            $addLead = $db->do_query("UPDATE LEADS SET STATUS = (?) WHERE (BITRIXID = (?) AND LEADID = (?))", [$curLead['result']['STATUS_ID'], $bitrix_id, $id]);
            $flag = true;
        }
    }

    if ($flag == true)
    {
        $stage_id = $curLead['result']['STATUS_ID'];


        if ($bitrix_id !== 0 && !empty($bitrix_id))
        {
            $resp_user = $db->fetch_query("SELECT user_id, group_id FROM STAGELEAD WHERE bitrix_id=(?) and stage_id=(?)", [$bitrix_id, $stage_id]);
            if($resp_user[0]['user_id'] != ''){
                $resp_user = $resp_user[0]['user_id'];
                $pos = strpos($resp_user, ']');
                if (($resp_user != '') && ($resp_user[0] == '[') && !empty($pos))
                {
                    $resp_user = substr($resp_user, 1, $pos-1);

                    if (is_numeric($resp_user))
                    {
                        echo '<br>';

                        if ($resp_user !== 0 && !empty($resp_user)){
                            $params = [];
                            $params['id'] = $id;
                            $params['resp_user'] = $resp_user;
                            sleep(1);
                            $temp = $deal->updateLead($params);
                            
                            print_r($temp);
                        }
                    }
                }
            }elseif($resp_user[0]['group_id'] != ''){

                $group_id = $resp_user[0]['group_id'];
                $resp_user = $resp_user[0]['group_id'];
                $pos = strpos($resp_user, ']');
                if (($resp_user != '') && ($resp_user[0] == '[') && !empty($pos))
                {
                    $resp_user = substr($resp_user, 1, $pos-1);

                    if (is_numeric($resp_user))
                    {
                        if ($resp_user !== 0 && !empty($resp_user)){
                            $last_user_id =  $db->fetch_query("SELECT * FROM GROUPSLEAD WHERE bitrix_id=(?) and stage_id=(?) and group_id =(?)", [$bitrix_id, $stage_id, $group_id]);
                            $user_group = $bitrix24->B24Method('sonet_group.user.get',
                                        array(
                                            'auth' => $_SESSION['AUTH_ID'],
                                            'ID' => $resp_user,
                                        )
                                    ); 
                            if ($last_user_id[0]['user_number'] < count($user_group['result']))
                            {
                                $params = [];
                                $params['id'] = $id;
                                $params['resp_user'] = $user_group['result'][$last_user_id[0]['user_number']]['USER_ID'];
                                $check_user = $last_user_id[0]['user_number'] + 1;
                            }
                            else
                            {
                                $params = [];
                                $params['id'] = $id;
                                $params['resp_user'] = $user_group['result'][0]['USER_ID'];
                                $check_user = 1;
                            }
                            sleep(1);
                            $temp = $deal->updateLead($params);

                            $addUser = $db->do_query("UPDATE GROUPSLEAD SET user_number=(?) WHERE bitrix_id=(?) and stage_id=(?) and group_id =(?)", [$check_user, $bitrix_id, $stage_id, $group_id]);

                        }
                    }
                }            
            }
        }
    }
