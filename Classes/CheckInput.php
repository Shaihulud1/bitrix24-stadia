<?php
function checkNumber($inputData)
{
    return intval($inputData);
}

function checkString($inputData)
{
    $inputData = strip_tags($inputData);
    $inputData = htmlspecialchars($inputData);
    $inputData = mysql_escape_string($inputData);
    return $inputData;
}

function findUserID($stageStorage, $status)
{
    $i=0; 
    while ($i<count($stageStorage))
    {
        if ($stageStorage[$i]['stage_id'] == $status)
        {
            if($stageStorage[$i]['user_id'] != ''){
                return $stageStorage[$i]['user_id']; 
            }else if($stageStorage[$i]['group_id'] != ''){
                return $stageStorage[$i]['group_id'];
            }
           
        }
        $i++;
    }

}
function groupNOT($stageStorage, $status)
{
    $i=0; 
    while ($i<count($stageStorage))
    {
        if ($stageStorage[$i]['stage_id'] == $status)
        {
            if($stageStorage[$i]['group_id'] != ''){
                return 'YES';
            }
           
        }
        $i++;
    }

}

function findUserName($users, $user_id)
{
    $i=0; 
    while ($i<count($users))
    {
        if ($users[$i]['ID'] == $user_id)
        {
            return ($users[$i]['NAME'].' '.$users[$i]['LAST_NAME']);
        }
        $i++;
    }
    return 'Не назначен';
}
