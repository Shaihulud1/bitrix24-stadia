<?php
session_start();
require_once dirName(__FILE__).'./../Classes/Bitrix24.php';
require_once dirName(__FILE__).'./../Classes/Db_work.php';



$db = Database::getInstance();
$dealLead = $_POST['dealLead'];
if ($dealLead == 'lead')
{
    $stageTrigger = 'STAGELEAD';
    $groupsTrigger = 'GROUPSLEAD';
}
else
{
    $groupsTrigger = 'GROUPS';
    $stageTrigger = 'STAGE';
}

$delUser = $db->do_query("DELETE FROM ".$stageTrigger." WHERE (bitrix_id)=(?)", [$_SESSION['userID']]);
$bitrix24 = new Bitrix();
foreach($_POST['stageArray'] as $stage)
{
        $temp[] = $stage['userID'];
        $addUser = $db->do_query("INSERT INTO ".$stageTrigger." (bitrix_id, stage_id, user_id, group_id) VALUES (?, ?, ?, ?)", [$_SESSION['userID'], $stage['stageID'], $stage['userID'], $stage['groupID']]);
        if($stage['groupID'] != ''){
            $cur_group = '';
            echo $groupsTrigger;
            $cur_group = $db->fetch_query("SELECT * FROM ".$groupsTrigger." WHERE bitrix_id=(?) and stage_id=(?) and group_id=(?)", [$_SESSION['userID'],  $stage['stageID'], $stage['groupID']]);

            if(!empty($cur_group[0])){

            }else{//если нет группы
                preg_match_all('/\[\d+\]/',$stage['groupID'], $arr); 
                $group_numb = $arr[0][0];
                $group_numb = str_replace("[","",$group_numb);
                $group_numb = str_replace("]","",$group_numb);
                $user_group = $bitrix24->B24Method('sonet_group.user.get',
                                                    array(
                                                        'auth' => $_SESSION['AUTH_ID'],
                                                        'ID' => $group_numb,
                                                    )
                                                );                
                $addGroup = $db->do_query("INSERT INTO ".$groupsTrigger." (stage_id, bitrix_id, group_id, last_user, user_number) VALUES (?, ?, ?, ?, ?)", [$stage['stageID'], $_SESSION['userID'], $stage['groupID'], $user_group['result'][0]['USER_ID'], '0']);  

            }

        }

}

$event = $bitrix24->B24Method('event.get',
                                    array(
                                        'auth' => $_SESSION['AUTH_ID'],
                                    )
                                );
usleep(400000);
if ($event['result']['count'] == 0)
{
    $event = $bitrix24->B24Method('event.bind',
                                        array(
                                            'auth' => $_SESSION['AUTH_ID'],
                                            'event' => 'onCrmLeadAdd',
                                            'handler' => 'https://fishdayprod.ru/stadia3/changeRespUserLead.php',
                                        )
                                    );
    usleep(400000);
    $event = $bitrix24->B24Method('event.bind',
                                        array(
                                            'auth' => $_SESSION['AUTH_ID'],
                                            'event' => 'onCrmLeadUpdate',
                                            'handler' => 'https://fishdayprod.ru/stadia3/changeRespUserLead.php',
                                        )
                                    );
    usleep(400000);
    $event = $bitrix24->B24Method('event.bind',
                                        array(
                                            'auth' => $_SESSION['AUTH_ID'],
                                            'event' => 'onCrmDealAdd',
                                            'handler' => 'https://fishdayprod.ru/stadia3/changeRespUser.php',
                                        )
                                    );
    usleep(400000);
    $event = $bitrix24->B24Method('event.bind',
                                        array(
                                            'auth' => $_SESSION['AUTH_ID'],
                                            'event' => 'onCrmDealUpdate',
                                            'handler' => 'https://fishdayprod.ru/stadia3/changeRespUser.php',
                                        )
                                    );
    usleep(400000);
    $event = $bitrix24->B24Method('event.bind',
                                        array(
                                            'auth' => $_SESSION['AUTH_ID'],
                                            'event' => 'OnAppUninstall',
                                            'handler' => 'https://fishdayprod.ru/stadia3/Uninstall.php',
                                        )
                                    );      
    usleep(400000);
    $event = $bitrix24->B24Method('event.bind',
                                        array(
                                            'auth' => $_SESSION['AUTH_ID'],
                                            'event' => 'onCrmLeadDelete',
                                            'handler' => 'https://fishdayprod.ru/stadia3/deleteLead.php',
                                        )
                                    );
    usleep(400000);
    $event = $bitrix24->B24Method('event.bind',
                                        array(
                                            'auth' => $_SESSION['AUTH_ID'],
                                            'event' => 'onCrmDealDelete',
                                            'handler' => 'https://fishdayprod.ru/stadia3/deleteDeal.php',
                                        )
                                    );
    
}