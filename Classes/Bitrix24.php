<?php 
session_start();

require_once dirName(__FILE__).'./../log/logger_class.php';
require_once dirName(__FILE__).'/../config.php';

class Bitrix{
    public $client_id = CLIENT_ID;
    public $client_secret = CLIENT_SECRET;
    
    public $domain;
    public $auth;
    public $member;
    private $mid;
    private $access_token;
    private $refresh_token;

    public function __construct()
    {
        $this->domain = 'https' . '://'.$_SESSION['DOMAIN'];
        
        $this->mid = $member_id;
//        $db = Database::getInstance();
        
        $this->refresh_token = $_SESSION['REFRESH_ID'];
                
        $params = [
            'grant_type' => 'refresh_token',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'refresh_token' => $this->refresh_token
        ];

        $url_query = 'https://oauth.bitrix.info/oauth/token/?'.http_build_query($params);
        $curl_handle=curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url_query);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Your application name');
        $query = curl_exec($curl_handle);

        $query = json_decode($query,true);

        $this->access_token = $query['access_token'];
        $this->refresh_token = $query['refresh_token'];
        if($query['error'] != ''){
            $logger = Logger::getInstance();
            $logger->log_save($query['error'].'('.$query['error_description'].')', true);
        }
    }

    public function B24Method($method, $params)
    {
        $url_query = $this->domain.'/rest/'.$method.'.json/?'.http_build_query($params);
        
        $curl_handle=curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url_query);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Your application name');
        $query = curl_exec($curl_handle);
        $query = json_decode($query,true);	
        $logger = Logger::getInstance();
        
        if($query['error'] != '')
        {		
            $logger->log_save($query['error'].'('.$query['error_description'].')', true);
        }
        else
        {
            $logger->log_save('Вызван метод: '.$method. ' с параметрами: '.json_encode($params));
        }
        
        return $query;	
    }
    
    public function createLead($vkData)
    {
        sleep(1);
        $addLeads = $this->B24Method('crm.lead.add', 
                                            array('auth' => $this->auth, 
                                                'fields'=>
                                                    array (
                                                        'TITLE' => $vkData['first_name'].' '.$vkData['last_name'].' [Лид из вконтакте]',
                                                        'NAME' => $vkData['first_name'], 
                                                        'LAST_NAME' => $vkData['last_name'],
                                                        'BIRTHDATE' => $vkData['bdate'],
                                                        'COMMENTS' => '<img src="'.$vkData['photo_max'].'">',
                                                        'SOURCE_ID' => 'VK ИНТЕГРАЦИЯ',
                                                        'SOURCE_DESCRIPTION' => 'https://vk.com/id'.$vkData['id']
                                                    )
                                            )
                                        );
        return $addLeads;
    }
    
    
    
    private function queryBatch($method, $url, $data = null)
    {
        echo 'url: <br>';
        print_r($url);
        echo '<br>';
	$query_data = "";

	$curlOptions = array(
		CURLOPT_RETURNTRANSFER => true
	);

	if($method == "POST")
	{
		$curlOptions[CURLOPT_POST] = true;
                
		$curlOptions[CURLOPT_POSTFIELDS] = http_build_query($data);
	}
	elseif(!empty($data))
	{
		$url .= strpos($url, "?") > 0 ? "&" : "?";
		$url .= http_build_query($data);
	}

	$curl = curl_init($url);
	curl_setopt_array($curl, $curlOptions);
	$result = curl_exec($curl);

	return json_decode($result, 1);
    }
    
    public function B24batch($inputArray)
    {
        $params['auth'] = $this->access_token;
        $params['halt'] = 0; 
        $params['cmd'] = array(
            //"dealStages" => 'crm.dealcategory.stage.list',
            "dealStages" => 'crm.lead.fields',
//            "leadStages" => 'crm.status.list'
/*                .http_build_query(array(
                    "id" => '46'
                )),*/
            //"userfield" => "crm.lead.fields",
        );
        //$url_query = $this->domain.'/rest/batch.xml?auth='.$params['auth'].'&halt='.$params['halt'].'&'.$arrayOut;
        return $this->queryBatch("POST", $this->domain."/rest/".'batch', $params);
    }

    public function addLeadsBatch($b24out){
        //$this->domain = 'https://test224.bitrix24.ru';
        $temp = [];
        $j=1;
        $logger = Logger::getInstance();
        for ($i=0; $i<count($b24out); $i++)
        {
            if ($i/$j == 50)
            {
                sleep(1);
                $logger->log_save('Вызван метод batch для добавления лидов, параметры: '.json_encode($temp));
                $data = $this->call($this->domain, "batch", array(
                    "auth" => $this->access_token,
                    "halt" => 0,
                    "cmd" => $temp
                ));

                $result = $data['result'];
                for ($r=0; $r<count($result['result']); $r++)
                {
                    $logger->log_save('Добавлен новый ЛИД, id: '.$result['result'][$r]);
                }
                
                $temp = [];
                $j++;
            }
            $temp += array(
                $i => 'crm.lead.add?'
                    .http_build_query(array(
                        "fields"=>array(
                            'TITLE' => $b24out[$i]['first_name'].' '.$b24out[$i]['last_name'].' [Лид из вконтакте]',
                            'NAME' => $b24out[$i]['first_name'], 
                            'LAST_NAME' => $b24out[$i]['last_name'],
                            'BIRTHDATE' => $b24out[$i]['bdate'],
                            'COMMENTS' => '<img src="'.$b24out[$i]['photo_max'].'">',
                            'SOURCE_ID' => 'VK ИНТЕГРАЦИЯ',
                            'SOURCE_DESCRIPTION' => 'https://vk.com/id'.$b24out[$i]['id']
                        )
                    )
                ),
            );
            if ($i == (count($b24out)-1))
            {
                sleep(1);
                $logger->log_save('Вызван метод batch для добавления лидов, параметры: '.json_encode($temp));
                $data = $this->call($this->domain, "batch", array(
                    "auth" => $this->access_token,
                    "halt" => 0,
                    "cmd" => $temp
                ));
                
                $result = $data['result'];


                for ($r=0; $r<count($result['result']); $r++)
                {
                    $logger->log_save('Добавлен новый ЛИД, id: '.$result['result'][$r]);
                }
            }
        }
    }
    
    private function query($method, $url, $data = null)
    {
        $query_data = "";

        $curlOptions = array(
            CURLOPT_RETURNTRANSFER => true
        );
        
        if($method == "POST")
        {
            $curlOptions[CURLOPT_POST] = true;
            $curlOptions[CURLOPT_POSTFIELDS] = http_build_query($data);
        }
        elseif (!empty($data))
        {
            $url .= strpos($url, "?") > 0 ? "&" : "?";
            $url .= http_build_query($data);
        }
        $curl = curl_init($url);
        curl_setopt_array($curl, $curlOptions);
        $result = curl_exec($curl);

        $logger = Logger::getInstance();
        $logger->log_save('Было произведено пакетное добавление ЛИДОВ: '.$result);
        
        return json_decode($result, 1);
    }

    private function call($domain, $method, $params)
    {
        return $this->query("POST", $domain."/rest/".$method, $params);
    }
}


class Deal extends Bitrix{
    public function listDeals(){
        $list = $this->B24Method('crm.deal.list',
                                    array(
                                        'auth' => $_SESSION['AUTH_ID'],
                                        //'filter' => array('>PROBABILITY' => 50),
                                        'select' => [ "ID", "TITLE", "STAGE_ID", "PROBABILITY", "OPPORTUNITY", "CURRENCY_ID" ]
                                    )
                                );
        return $list;
    }

    public function productrowsGet($id){
        $list = $this->B24Method('crm.deal.productrows.get',
                                    array(
                                        'auth' => $_SESSION['AUTH_ID'],
                                        'id' => $id
                                    )
                                );
        return $list;
    }

    public function dealFields(){
        
        $list = $this->B24Method('crm.deal.fields',
                                    array(
                                        'auth' => $_SESSION['AUTH_ID'],
                                    )
                                );
        return $list;
    }

    public function stageList(){
        $list = $this->B24Method('crm.dealcategory.stage.list',
                                    array(
                                        'auth' => $_SESSION['AUTH_ID'],
                                    )
                                );
        return $list;
    }
    
    public function getDeal($id){
        $list = $this->B24Method('crm.deal.get',
                                    array(
                                        'auth' => $_SESSION['AUTH_ID'],
                                        'id' => $id
                                    )
                                );
        return $list;
    }
    
    public function updateDeal($params) {
        $list = $this->B24Method('crm.deal.update',
                                    array(
                                        'auth' => $_SESSION['AUTH_ID'],
                                        'id' => $params['id'],
                                        'fields' => [
                                            'ASSIGNED_BY_ID' => $params['resp_user'],
                                        ]
                                    )
                                );
        return $list;
    }
    
}

class Lead extends Bitrix{
    public function listDeals(){
        $list = $this->B24Method('crm.deal.list',
                                    array(
                                        'auth' => $_SESSION['AUTH_ID'],
                                        //'filter' => array('>PROBABILITY' => 50),
                                        'select' => [ "ID", "TITLE", "STAGE_ID", "PROBABILITY", "OPPORTUNITY", "CURRENCY_ID" ]
                                    )
                                );
        return $list;
    }

    public function productrowsGet($id){
        $list = $this->B24Method('crm.deal.productrows.get',
                                    array(
                                        'auth' => $_SESSION['AUTH_ID'],
                                        'id' => $id
                                    )
                                );
        return $list;
    }

    public function dealFields(){
        
        $list = $this->B24Method('crm.deal.fields',
                                    array(
                                        'auth' => $_SESSION['AUTH_ID'],
                                    )
                                );
        return $list;
    }

    public function stageList(){
        $list = $this->B24Method('crm.dealcategory.stage.list',
                                    array(
                                        'auth' => $_SESSION['AUTH_ID'],
                                    )
                                );
        return $list;
    }
    
    public function getLead($id){
        $list = $this->B24Method('crm.lead.get',
                                    array(
                                        'auth' => $_SESSION['AUTH_ID'],
                                        'id' => $id
                                    )
                                );
        return $list;
    }
    
    public function updateLead($params) {
        $list = $this->B24Method('crm.lead.update',
                                    array(
                                        'auth' => $_SESSION['AUTH_ID'],
                                        'id' => $params['id'],
                                        'fields' => [
                                            'ASSIGNED_BY_ID' => $params['resp_user'],
                                        ]
                                    )
                                );
        return $list;
    }

    public function addLead($params) {
        echo 'params: <br>';
        print_r($params);
        echo '<br>';
        $list = $this->B24Method('crm.lead.add',
                                    array(
                                        'auth' => $_SESSION['AUTH_ID'],
                                        'fields' => [
                                            'TITLE' => $params['TITLE'],
                                        ]
                                    )
                                );
        return $list;
    }

    
}


class User extends Bitrix{
    public function getList($shift)
    {
        $list = $this->B24Method('user.get',
                                    array(
                                        'auth' => $_SESSION['AUTH_ID'],
                                        'order' => 'ASC',
                                        'filter' => ['ACTIVE' => '1'],
                                        'start' => $shift
                                    )
                                );
        return $list;
    }
}
class Group extends Bitrix{
    public function getList($shift)
    {
        $list = $this->B24Method('sonet_group.get',
                                    array(
                                        'auth' => $_SESSION['AUTH_ID'],
                                        'order' => 'ASC',
                                        'filter' => ['ACTIVE' => '1'],
                                        'start' => $shift
                                    )
                                );
        return $list;
    }
}


define('CLIENT_ID', 'local.588043ad89eb47.69901685');
/**
 * client_secret приложения
 */
define('CLIENT_SECRET', 'Ljx1x9c04w4eyf7sDgD7e9KyKmuCBLAtiRFW6aZvOs6jLIbDCC');
/**
 * относительный путь приложения на сервере
 */
define('PATH', '/bitrix24/addData/index.php');
define('REDIRECT_PATH', 'fishdayprod.ru/bitrix24/addData/index.php');
/**
 * полный адрес к приложения
 */
define('REDIRECT_URI', 'https://fishdayprod.ru'.PATH);
/**
 * scope приложения
 */
define('SCOPE', 'crm,log,user');

/**
 * протокол, по которому работаем. должен быть https
 */
define('PROTOCOL', "https");





