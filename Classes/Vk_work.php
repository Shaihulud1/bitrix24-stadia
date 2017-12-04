<?php
require_once dirName(__FILE__).'./../log/logger_class.php';
require_once dirName(__FILE__).'./../Classes/Db_work.php';
require_once dirName(__FILE__).'./../Classes/Bitrix24.php';


class Vk_work
{
    public $token;
    
    public $ava_link;
    public $first_name;
    public $last_name;
    public $profile_id;
    public $mess_first_name;
    public $mess_last_name;
    public $mess_ava_link;
    public $mess_city;
    public $mess_home_town;
    public $mess_country;

    private $member_id;
    
    private $users_all_ids = [];

    public function set_user($mid)
    {
        $this->member_id = $mid;
        return $this->member_id;
    }

    public function set_token($tok)//установить токен
    {
        $this->token = $tok;
        return $this->token;
    }	
    public function profile_info()//метод вытаскивает имя, фамилию, ссылку на аватарку
    {
        $params = [
            'access_token' => $this->token,  // access_token можно вбить хардкодом, если работа будет идти из под одного юзера
            'fields' => 'photo_max',
            'v' => '5.53'
        ];
        $url_query = 'https://api.vk.com/method/users.get?'.http_build_query($params);
        $result = json_decode(file_get_contents($url_query), true);
        if($result['error']['error_msg'] != ''){
            $logger = Logger::getInstance();	
            $logger->log_save($result['error']['error_msg'], true);
        }	
        $this->ava_link = $result['response'][0]['photo_max'];
        $this->first_name = $result['response'][0]['first_name'];
        $this->last_name = $result['response'][0]['last_name'];	
        $this->profile_id = $result['response'][0]['id']; 
    }

    public function messages_get($time_offset,$offset)//получить сообщения, указать время в секундах 900с = 15 минут
    {
        $params = [
            'out' => '0',
            'offset' => $offset,
            'count' => '6',
            'time_offset' => $time_offset,
            'filters' => '0',
            'fields' => 'first_name',
            'access_token' => $this->token,
            'last_message_id' => '', 
            'v' => '5.52'
        ];

        $url_query = 'https://api.vk.com/method/messages.get?'.http_build_query($params);
        $result = json_decode(file_get_contents($url_query), true);
        if($result['error_msg'] != ''){
            $logger = Logger::getInstance();	
            $logger->log_save($result['error']['error_msg'], true);
        }		
        $ar_perfect_mess = [];	
        foreach ($result['response']['items'] as $mess){
            if ($mess['title'] != ' ... '){
                $mess['title'] = $mess['title'];
            }else{
                $this->get_user_byId($mess['user_id'],$this->token);
                $mess['title'] = $this->mess_first_name.' '.$this->mess_last_name;
            }
            $ar_perfect_mess[] = [
                'name' => $mess['title'],
                'first_name' => $this->mess_first_name,
                'last_name' => $this->mess_last_name,
                'photo_link' => $this->mess_ava_link,
                'user_id' => $mess['user_id'],
                'comment' => $mess['body'],
                'town' => $this->mess_city,
                'country' => $this->mess_country			            
            ];
        }	
        return $ar_perfect_mess;
    }	
    public function dialogs_get($time_offset,$offset)//получить сообщения, указать время в секундах 900с = 15 минут
    {
        $params = [
            'offset' => $offset,
            'count' => '6',
            'time_offset' => $time_offset,
            'access_token' => $this->token,
            'last_message_id' => '', 
            'v' => '5.52'
        ];


        $url_query = 'https://api.vk.com/method/messages.getDialogs?'.http_build_query($params);
        $result = json_decode(file_get_contents($url_query), true);
        if($result['error']['error_msg'] != ''){
            $logger = Logger::getInstance();    
            $logger->log_save($result['error']['error_msg'], true);
        }       
        $ar_perfect_mess = [];  
        foreach ($result['response']['items'] as $mess){
            if ($mess['message']['title'] != ' ... '){
                $mess['message']['title'] = $mess['title'];
            }else{
                $this->get_user_byId($mess['message']['user_id'],$this->token);
                $mess['message']['title'] = $this->mess_first_name.' '.$this->mess_last_name;
            }
            $ar_perfect_mess[] = [
                'name' => $mess['message']['title'],
                'first_name' => $this->mess_first_name,
                'last_name' => $this->mess_last_name,
                'photo_link' => $this->mess_ava_link,
                'user_id' => $mess['message']['user_id'],
                'comment' => $mess['message']['body'],
                'town' => $this->mess_city,
                'country' => $this->mess_country                        
            ];
        }   
        return $ar_perfect_mess;
    }   
    public function one_dialog($dialog_id)
    {
       $params =    [
                        'offset' => '0',
                        'count' => '200',
                        'user_id' => $dialog_id,
                        'rev' => '0',
                        'access_token' => $this->token,
                        'v' => '5.52'

                    ];

        $url_query = 'https://api.vk.com/method/messages.getHistory?'.http_build_query($params);
        $result = json_decode(file_get_contents($url_query), true);
        return $result;


    } 

    public function message_send($user_id, $message){
         $params =    [
                        'user_id' => $user_id,
                        'message' => $message,
                        'access_token' => $this->token,
                        'v' => '5.52'

                    ];

        $url_query = 'https://api.vk.com/method/messages.send?'.http_build_query($params);
        $result = json_decode(file_get_contents($url_query), true);
        return $result;      
    }

    private function get_user_byId($id_user)//получить имя и фамилию по айдишнику
    {
        $params = [
            'user_ids' => $id_user,
            'fields' => ['photo_max','city','country','home_town'],
            'access_token' => $this->token,  
            'v' => '5.53'
        ];

        $url_query = 'https://api.vk.com/method/users.get?'.http_build_query($params);

        $result = json_decode(file_get_contents($url_query), true);
        if($result['error_msg'] != ''){
            $logger = Logger::getInstance();	
            $logger->log_save($result['error']['error_msg'], true);
        }		

        $this->mess_first_name = $result['response'][0]['first_name'];
        $this->mess_last_name = $result['response'][0]['last_name'];	
        $this->mess_ava_link = $result['response'][0]['photo_max'];
        $this->mess_city = $result['response'][0]['city'];
        $this->mess_country = $result['response'][0]['country'];
        $this->mess_home_town = $result['response'][0]['home_town'];
    }
    
    // получить диалоги
    private function get_dialogs($offset, $unread = false)
    {
        $params = [
            'offset' => $offset,
            'count' => '200',
            'unread' => $unread,
            'access_token' => $this->token,
            'v' => '5.52'
        ];

        $url_query = 'https://api.vk.com/method/messages.getDialogs?'.http_build_query($params);
        $result = json_decode(file_get_contents($url_query), true);
        if($result['error_msg'] != ''){
            $logger = Logger::getInstance();	
            $logger->log_save($result['error']['error_msg'], true);
        }
        return $result;
    }
    
    public function get_Dialogs_Senders()
    {
        $dialogs = $this->get_dialogs(0);
        $count = $dialogs['response']['count'];
        if ($count>=1000)
        {
            $count = 1000;
        }
        $items = $dialogs['response']['items'];

        $ids = [];
        foreach ($items as $item)
        {
            if($item['message']['title'] == " ... "):
                $ids[] = $item['message']['user_id'];
            endif;
        }

        $overallCount = ceil($count/200);
        
        for ($i=1; $i<$overallCount; $i++)
        {
            sleep(1);
            $offset = $i*200;
            $dialogs = $this->get_dialogs($offset);
            $count = $dialogs['response']['count'];
            $items = $dialogs['response']['items'];
            foreach ($items as $item)
            {
                if($item['message']['title'] == " ... "):
                    $ids[] = $item['message']['user_id'];
                endif;
            }
        }
        return $ids;
    }
    public function get_mess_Dialogs_Senders()
    {
        $dialogs = $this->get_dialogs(0);
        $count = $dialogs['response']['count'];
        $items = $dialogs['response']['items'];
        $ids = [];
        foreach ($items as $item)
        {
            if($item['message']['title'] == " ... "):
                $ids[] =    [
                                'body' => $item['message']['body'],
                                'out' => $item['message']['out']
                            ];
            endif;
        }

        $overallCount = ceil($count/200);
        
        for ($i=1; $i<$overallCount; $i++)
        {
            sleep(1);
            $offset = $i*200;
            $dialogs = $this->get_dialogs($offset);
            print_r($dialogs);
            $count = $dialogs['response']['count'];
            $items = $dialogs['response']['items'];
            foreach ($items as $item)
            {
                if($item['message']['body'] == " ... "):
                    $ids[] =    [
                                    'body' => $item['message']['body'],
                                    'out' => $item['message']['out']
                                ];
                endif;
            }
        }
        return $ids;
    }      
    
    private function users_get($user_id)//получить имя и фамилию по айдишнику
    {
        $params = [
            'user_ids' => $user_id,
            'fields' => ['first_name', 'last_name', 'photo_max', 'bdate'],
            'v' => '5.53'
        ];

        $url_query = 'https://api.vk.com/method/users.get?'.http_build_query($params);

        $result = json_decode(file_get_contents($url_query), true);
        if($result['error_msg'] != ''){
            $logger = Logger::getInstance();	
            $logger->log_save($result['error']['error_msg'], true);
        }
        return $result['response'];
    }
    
    public function users_get_all()//получить имя и фамилию по айдишнику
    {
        $senders = $this->get_Dialogs_Senders();
        
        $this->users_all_ids = $senders;

        
        $countSenders = count($senders);
        if ($countSenders>0)
        {
            $countSendersOut = false;
        }
        $j=0;
        $users = [];
        while (!$countSendersOut)
        {
            $begin = $j*1000;
            $end = $j*1000 + 1000;
            if ($end>$countSenders)
            {
                $end = $countSenders;
                $countSendersOut = true;
            }
     
            $users = array_merge($users, $this->users_get($senders));
            sleep(1);
            $j++;
        }
        return ($users);
    }
    
    public function get_users_ids()
    {
        return $this->users_all_ids;
    }
}
