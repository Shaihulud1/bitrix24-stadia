<?php 
    session_start();
    
    require_once 'Classes/Bitrix24.php';
    require_once 'Classes/Db_work.php';
    require_once 'Classes/Vk_work.php';
    require_once 'log/logger_class.php';
    require_once 'Classes/CheckInput.php';
    $_SESSION['MEMBER_ID'] = checkString($_REQUEST['member_id']);
    $_SESSION['AUTH_ID'] = checkString($_REQUEST['AUTH_ID']);
    $_SESSION['REFRESH_ID'] = checkString($_REQUEST['REFRESH_ID']);
    $_SESSION['PROTOCOL'] = checkString($_REQUEST['PROTOCOL']);
    $_SESSION['DOMAIN'] = checkString($_REQUEST['DOMAIN']);
    $_SESSION['userID'] = '';
    
/*    echo 'request: <br>';
    print_r($_REQUEST);
    echo '<br>';*/
    
    $logger = Logger::getInstance();
    $err_arr = [];
    $bitrix24 = new Bitrix();

    $userArray = $bitrix24->B24Method('user.current',
                                        array(
                                            'auth' => $_SESSION['AUTH_ID']
                                        )
                                    );
    $_SESSION['EMAIL'] = $userArray['result']['EMAIL'];
    if(($userArray['result']['EMAIL'] == '') || ($_SESSION['MEMBER_ID'] == ''))
    {
        $err_arr[]= [
            'USER_ERROR' => 'Вы не являетесь пользователем Битрикс24'
        ];
    }
    
    //добавление пользователя в БД
    $db = Database::getInstance();
    //
    $getID = $db->fetch_query("SELECT ID FROM USER WHERE DOMAIN=(?)", [$_SESSION['DOMAIN']]);
    
    if (!empty($getID))
    {
        $_SESSION['userID'] = $getID[0]['ID'];
    }
    else {
        $addUser = $db->do_query("INSERT INTO USER (DOMAIN) VALUES (?)", [$_SESSION['DOMAIN']]);

        $getID = $db->fetch_query("SELECT ID FROM USER WHERE DOMAIN=(?)", [$_SESSION['DOMAIN']]);
        
        $_SESSION['userID'] = $getID[0]['ID'];
    }
    
/*    $deal = new Deal();
    $stageList = $deal->stageList();*/

    $stages = array(
        "cmd" => array(
            "dealStages" => 'crm.dealcategory.stage.list'
                .http_build_query(array(
                )),
            )
    );
    
    
/*    $temp = $bitrix24->B24batch($stages);
    echo 'temp: <br>';
    print_r($temp);
    echo '<br>';*/
    
    $stagesList = $bitrix24->B24Method('crm.status.list',
                                        array(
                                            'auth' => $_SESSION['AUTH_ID'],
                                        )
                                    );
    
    foreach ($stagesList['result'] as $stage) {
        if($stage['ENTITY_ID'] == "STATUS"){
            $leadStages[] = array(
                            'NAME' => $stage['NAME'],
                            'STATUS_ID' => $stage['STATUS_ID'], 
                        );
        }
        if ($stage['ENTITY_ID'] == "DEAL_STAGE"){
            $dealStages[] = array(
                            'NAME' => $stage['NAME'],
                            'STATUS_ID' => $stage['STATUS_ID'], 
                        );
        }
    }
    
    $shift = 50;
    $i = 0;
    $user = new User();
    $group = new Group();
    $flag = true;
    $userList = [];
    $groupList = [];
    while ($flag == true)
    {
        $result = $user->getList($shift*$i);
        $userTemp = $result['result'];
        $userList = array_merge($userList, $userTemp);
        if ($result['total'] == 50)
        {
            $i++;
            usleep (500000);
        }
        else{
            $flag = false;
        }
    }
    $flag = true;
    $shift = 50;
    $i = 0;      
    while ($flag == true)
    {
        $result = $group->getList($shift*$i);
        $groupTemp = $result['result'];
        $groupList = array_merge($groupList, $groupTemp);
        if ($result['total'] == 50)
        {
            $i++;
            usleep (500000);
        }
        else{
            $flag = false;
        }
    }    
    $outerArray = [];
    $i=0;
    for ($i=0; $i<count($userList); $i++)
    {
        $outerTemp['label'] = $userList[$i]['NAME'].' '.$userList[$i]['LAST_NAME'];
        $outerTemp['value'] = '['.$userList[$i]['ID'].'] '.$userList[$i]['NAME'].' '.$userList[$i]['LAST_NAME'];
        $outerArray[] = $outerTemp;
    }
    $outerJSON = json_encode($outerArray);

    $groupArray=[];
    $i=0;
    for ($i=0; $i<count($groupList); $i++)
    {
        $groupTemp1['label'] = $groupList[$i]['NAME'];
        $groupTemp1['value'] = '['.$groupList[$i]['ID'].'] '.$groupList[$i]['NAME'];
        $groupArray[] = $groupTemp1;
    }
    $groupArray = json_encode($groupArray);  
 
        // $event = $bitrix24->B24Method('event.unbind',
        //                                     array(
        //                                         'auth' => $_SESSION['AUTH_ID'],
        //                                         'event' => 'ONAPPUNINSTALL',
        //                                         'handler' => 'https://fishdayprod.ru/stadia3/Uninstall.php',
        //                                     )
        //                                 ); 
        // sleep(1);
        
     $event = $bitrix24->B24Method('event.get',
                                array(
                                    'auth' => $_SESSION['AUTH_ID'],
                                )
                            ); 
                                         
    //print_r($event);

    
    $stageStorage = $db->fetch_query("SELECT * FROM STAGE WHERE bitrix_id=(?)", [$_SESSION['userID']]); 
    $leadStorage = $db->fetch_query("SELECT * FROM STAGELEAD WHERE bitrix_id=(?)", [$_SESSION['userID']]); 
    if($err_arr[0]['USER_ERROR'] == ''):?>

	<!DOCTYPE html>
	<html lang="en">
	<head>
            <meta charset="UTF-8">
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">	
	    <link rel="stylesheet" href="css/style.css"/>
            <link rel="stylesheet" href="css/bitrix24-guide-style.css"/>
	    <link rel="stylesheet" href="css/main.css"/>
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
            <script src="js/common.js"></script>
            <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
            <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
            
	    <title>Стадии сделок</title>
	</head>
	<body>
	    <div class="logo">
	        <div class="container-fluid">
	            <div class="row">
	                <div class="col-md-12">
                        <h1>
                            <img class="arrows rightar" src="img/left_ar.png" alt=""><t>Стадии сделок</t><img class="arrows leftar" src="img/right_ar.png" alt="">
                        </h1>
	                </div>			
	            </div>
	        </div>
	    </div>
        <div class="podskazka" style="text-align: center; padding-top: 21px; color: rgb(66, 120, 1);">
            Ввод пользователей осуществляется в следующем формате: [Идентификатор пользователя] Имя Фамилия
        </div>
        <div class="podskazka" style="text-align: center; padding-top: 21px; color: rgb(66, 120, 1);">
            Ввод групп осуществляется в следующем формате: [Идентификатор группы] Название группы
        </div>
        <!-- СДЕЛКИ -->
	    <div class="deals" >
	    	<div class="container">
	    		<div class="row">
		    		<div class="col-md-12">
                                    <?php
                                    foreach ($dealStages as $stage)
                                    {
                                    ?>
		    			<div class="deal-item true_deal <?php echo $stage['STATUS_ID']; ?>" stage-id="<?php echo $stage['STATUS_ID']; ?>" user-id="<?php $fuTemp = findUserID($stageStorage, $stage['STATUS_ID']); echo $fuTemp; ?>">
                                            <h2 style="padding-right: 38px;"><?php echo $stage['NAME']; ?></h2>
                                            <div class="dropdown selectordrop">
                                                <input type="text" class="choose_worker input-stadia dropdown-toggle drop_result" style="height: 34px; width: 340px;" placeholder="Не выбрано" value="<?php $fuTemp = findUserID($stageStorage, $stage['STATUS_ID']); echo $fuTemp; ?>">
                                                <?/*
                                                <button aria-expanded="true"contenteditable="true" class="choose_worker btn btn-primary dropdown-toggle drop_result" style="height: 41px; width: 190px;" type="button" data-toggle="dropdown">
                                                    <div class="downtext"><?php echo findUserName($userList['result'], $fuTemp); ?></div>
                                                </button>
                                                <ul class="dropdown-menu" style="text-align: center;">
                                                    <li class="stylist-list">
                                                        <a href="#">Не назначен</a>
                                                        <input class="uid" value="0" type="hidden">
                                                    </li>
                                                    
                                                    <?php
                                                    foreach ($userList['result'] as $userTemp)
                                                    {
                                                    ?>
                                                        <li class="stylist-list">
                                                            <a href="#"><?php echo $userTemp['NAME'].' '.$userTemp['LAST_NAME']; ?></a>
                                                            <input class="uid" value="<?php echo $userTemp['ID']; ?>" type="hidden">
                                                        </li>
                                                    <?php
                                                    }
                                                    ?>
                                                </ul>
                                                */?>
                                                <img class="renew" style="height: 30px; width: 30px; cursor:pointer;" src="img/cross.png" alt="">
                                                <div class="radio" style="margin-right: 85px;">
                                                    <label class="radio-inline"><input type="radio" value = "Пользователи" class = "radiob users" <? if(groupNOT($stageStorage, $stage['STATUS_ID']) != "YES"){ echo 'checked ';}?>name="<?php echo 'deal_'.$stage['NAME']; ?>">Пользователи</label>
                                                    <label class="radio-inline"><input type="radio" value = "Группы" class = "radiob group" <? if(groupNOT($stageStorage, $stage['STATUS_ID']) == "YES"){ echo 'checked ';}?> name="<?php echo 'deal_'.$stage['NAME']; ?>">Группы</label>
                                                </div>
                                            </div>
            
                        </div>
                                        <?php if($stage['STATUS_ID'] != 'WON'):?>
                                            <div class="arrow_down deal_arr">
                                                <img src="img/arrow.png" alt="">
                                            </div>
                                        <?endif;?>
                                    <?php
                                    }
                                    ?>
		    		</div>
		    	</div>	
	    	</div>
	    </div>
       <!--  ЛИДЫ -->
        <div class="leads off">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                                    <?php
                                    foreach ($leadStages as $stage)
                                    {
                                    ?>
                        <div class="deal-item <?php echo $stage['STATUS_ID']; ?>" stage-id="<?php echo $stage['STATUS_ID']; ?>" user-id="<?php $fuTemp = findUserID($leadStorage, $stage['STATUS_ID']); echo $fuTemp; ?>">
                                            <h2 style="padding-right: 38px;"><?php echo $stage['NAME']; ?></h2>
                                            <div class="dropdown selectordrop">
                                                <input type="text" class="choose_worker input-stadia dropdown-toggle drop_result" style="height: 34px; width: 340px;" placeholder="Не выбрано" value="<?php $fuTemp = findUserID($leadStorage, $stage['STATUS_ID']); echo $fuTemp; ?>">
                                                <?/*
                                                <button aria-expanded="true"contenteditable="true" class="choose_worker btn btn-primary dropdown-toggle drop_result" style="height: 41px; width: 190px;" type="button" data-toggle="dropdown">
                                                    <div class="downtext"><?php echo findUserName($userList['result'], $fuTemp); ?></div>
                                                </button>
                                                <ul class="dropdown-menu" style="text-align: center;">
                                                    <li class="stylist-list">
                                                        <a href="#">Не назначен</a>
                                                        <input class="uid" value="0" type="hidden">
                                                    </li>
                                                    
                                                    <?php
                                                    foreach ($userList['result'] as $userTemp)
                                                    {
                                                    ?>
                                                        <li class="stylist-list">
                                                            <a href="#"><?php echo $userTemp['NAME'].' '.$userTemp['LAST_NAME']; ?></a>
                                                            <input class="uid" value="<?php echo $userTemp['ID']; ?>" type="hidden">
                                                        </li>
                                                    <?php
                                                    }
                                                    ?>
                                                </ul>
                                                */?>
                                                <img class="renew" style="height: 30px; width: 30px; cursor:pointer;" src="img/cross.png" alt="">
                                                <div class="radio" style="margin-right: 85px;">
                                                    <label class="radio-inline"><input type="radio" value = "Пользователи" class = "radiob users" <? if(groupNOT($leadStorage, $stage['STATUS_ID']) != "YES"){ echo 'checked ';}?> name="<?php echo 'lead_'.$stage['NAME']; ?>">Пользователи</label>
                                                    <label class="radio-inline"><input type="radio" value = "Группы" class = "radiob group" <? if(groupNOT($leadStorage, $stage['STATUS_ID']) == "YES"){ echo 'checked ';}?> name="<?php echo 'lead_'.$stage['NAME']; ?>">Группы</label>
                                                </div>                                                
                                            </div>
            
                        </div>
                                        <?php if($stage['STATUS_ID'] != 'WON'):?>
                                            <div class="arrow_down">
                                                <img src="img/arrow.png" alt="">
                                            </div>
                                        <?endif;?>
                                    <?php
                                    }
                                    ?>
                    </div>
                </div>  
            </div>
        </div>        
		<div class="bitsend">
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-6">
						<span class="bx-button bx-button-accept accept-leads">Cохранить</span>
					</div>			
				</div>
			</div>
		</div> 
        <!--download area-->
        <div class="download" style="display: none;">
            <div class="modal-box"></div>
            <div class="downloadBox">
                <img src="img/load.gif" alt="loading">
            </div>
        </div>
        <!-- modal -->           
 
        <script>
        $(document).ready(function(){
            var availableTags = eval('<?php echo $outerJSON; ?>');
            var availableGroup = eval('<?php echo $groupArray; ?>');
            $(".radiob").on('change', function(){
                $(this).closest('.dropdown').find('.choose_worker').val('');              
                if($(this).val() == "Группы"){
                    $(this).closest('.dropdown').find('.choose_worker').autocomplete({
                        source: availableGroup                            
                    });  
                }else if($(this).val() == "Пользователи"){
                    $(this).closest('.dropdown').find('.choose_worker').autocomplete({
                        source: availableTags                            
                    });                    
                } 
            });            
            console.log(availableTags);

            $('.deal-item').each(function(index, element){
                if($(this).find('.users').is(':checked')){
                    $(this).find('.choose_worker').autocomplete({
                        source: availableTags
                    });
                }else if($(this).find('.group').is(':checked')){
                    $(this).find('.choose_worker').autocomplete({
                        source: availableGroup
                    });
                }
            });

            $('.choose_worker').on('keyup', function(){
                $('.bitsend').show();
            });
            
            $('.renew').on('click', function(){
                $('.bitsend').show();
                $(this).parent().find('input').val('');
            });

            $('.choose_worker').on('change', function(){
                $('.bitsend').show();
                var flag = true;
                for (var i=0; i<availableTags.length; i++)
                {
                    if ($(this).val() == availableTags[i]['value'])
                    {
                        flag = false;
                    }else if($(this).val() == availableGroup[i]['value'])
                    {
                        flag = false;
                    }
                }
                if (flag != false)
                {
                    $(this).val('');
                }
            });
            
        })
        </script>
	</body>
	</html>
<?php else:
	echo 'Вы не являетесь пользователем битрикс24';
endif;