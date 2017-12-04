<?php
session_start();
require_once dirName(__FILE__).'./../Classes/Bitrix24.php';
require_once dirName(__FILE__).'./../Classes/Db_work.php';


$db = Database::getInstance();
$delUser = $db->do_query("DELETE FROM STAGE WHERE (bitrix_id)=(?)", [$_SESSION['userID']]);
foreach($_POST['stageArray'] as $stage)
{
    $temp[] = $stage['userID'];
    $addUser = $db->do_query("INSERT INTO STAGE (bitrix_id, stage_id, user_id) VALUES (?, ?, ?)", [$_SESSION['userID'], $stage['stageID'], $stage['userID']]);
}
$bitrix24 = new Bitrix();
$event = $bitrix24->B24Method('event.get',
                                    array(
                                        'auth' => $_SESSION['AUTH_ID'],
                                    )
                                );
sleep(1);
if ($event['result']['count'] == 0)
{
    $event = $bitrix24->B24Method('event.bind',
                                        array(
                                            'auth' => $_SESSION['AUTH_ID'],
                                            'event' => 'onCrmDealAdd',
                                            'handler' => 'https://fishdayprod.ru/stadia/changeRespUser.php',
                                        )
                                    );
    sleep(1);
    $event = $bitrix24->B24Method('event.bind',
                                        array(
                                            'auth' => $_SESSION['AUTH_ID'],
                                            'event' => 'onCrmDealUpdate',
                                            'handler' => 'https://fishdayprod.ru/stadia/changeRespUser.php',
                                        )
                                    );
    sleep(1);
    $event = $bitrix24->B24Method('event.bind',
                                        array(
                                            'auth' => $_SESSION['AUTH_ID'],
                                            'event' => 'OnAppUninstall',
                                            'handler' => 'https://fishdayprod.ru/stadia/Uninstall.php',
                                        )
                                    );      
}