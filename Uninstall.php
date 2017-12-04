<?php

    session_start();

    require_once 'Classes/Bitrix24.php';

    require_once 'Classes/Db_work.php';

    require_once 'Classes/CheckInput.php';
    
    $bitrix24 = new Bitrix();    

    $event = $bitrix24->B24Method('event.unbind',
                                        array(
                                            'auth' => $_SESSION['AUTH_ID'],
                                            'event' => 'onCrmDealUpdate',
                                            'handler' => 'https://fishdayprod.ru/stadia3/changeRespUser.php',
                                        )
                                    );
    sleep(1);
    $event = $bitrix24->B24Method('event.unbind',
                                        array(
                                            'auth' => $_SESSION['AUTH_ID'],
                                            'event' => 'onCrmDealAdd',
                                            'handler' => 'https://fishdayprod.ru/stadia3/changeRespUser.php',
                                        )
                                    );      
    sleep(1);
    $event = $bitrix24->B24Method('event.unbind',
                                        array(
                                            'auth' => $_SESSION['AUTH_ID'],
                                            'event' => 'onCrmLeadUpdate',
                                            'handler' => 'https://fishdayprod.ru/stadia3/changeRespUserLead.php',
                                        )
                                    );
    sleep(1);
    $event = $bitrix24->B24Method('event.unbind',
                                        array(
                                            'auth' => $_SESSION['AUTH_ID'],
                                            'event' => 'onCrmLeadAdd',
                                            'handler' => 'https://fishdayprod.ru/stadia3/changeRespUserLead.php',
                                        )
                                    );      

    sleep(1);
    $event = $bitrix24->B24Method('event.unbind',
                                        array(
                                            'auth' => $_SESSION['AUTH_ID'],
                                            'event' => 'onCrmLeadDelete',
                                            'handler' => 'https://fishdayprod.ru/stadia3/deleteLead.php',
                                        )
                                    );
    sleep(1);
    $event = $bitrix24->B24Method('event.unbind',
                                        array(
                                            'auth' => $_SESSION['AUTH_ID'],
                                            'event' => 'onCrmDealDelete',
                                            'handler' => 'https://fishdayprod.ru/stadia/deleteDeal.php',
                                        )
                                    );      
    
