<?php

//require_once '../config.php';

class Database {

    private $_connection;

    private static $_instance = null; //The single instance

    private $_host = DB_HOST;

    private $_username = DB_USERNAME;

    private $_password = DB_PASS;

    private $_database = DB_DBNAME;

    private $result;

    private $pdo;

    /*

    Get an instance of the Database

    @return Instance

    */

    public static function getInstance() {

        if(!self::$_instance) { 

            self::$_instance = new self();

        }

        return self::$_instance;

    }

      // Constructor

    private function __construct() {

        $logger = Logger::getInstance();        

        try

        {

            $this->pdo = new PDO('mysql:dbname='.$this->_database.';host='.$this->_host, $this->_username, $this->_password,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

        } 

        catch (PDOException $e)

        {

            //logger->log_save("DATABASE ERROR : ".$e->getMessage());

            echo 'Возникла непредвиденная ошибка, свяжитесь с администратором приложения';

            $logger->log_save(' Файл: '.$e->getFile().' Строка: '.$e->getLine().' Сообщение: '.$e->getMessage(), true);

            exit;            

        }

    }

      // Magic method clone is empty to prevent duplication of connection

    private function __clone() { }

    // Get mysqli connection

    public function getConnection() {

      return $this->_connection;

    }



    public function fetch_query($sql, $parameters = null){

        $this->result = $this->pdo->prepare($sql);

        $this->result->execute($parameters);

        $arr = $this->result->fetchAll(PDO::FETCH_ASSOC);

        return $arr;

    }

    

    public function fetch_query_num($sql, $parameters = null){

/*echo 'paramQuery:';

print_r($parameters);*/

        $this->result = $this->pdo->prepare($sql);

        $this->result->execute($parameters);

        $arr = $this->result->fetchAll(PDO::FETCH_NUM);

        $newarr = [];

        foreach ($arr as $ar)
        {
            $newarr[] = $ar[0];
        }
        return $newarr;
    }

    public function do_query($sql, $parameters = null){

        $this->result = $this->pdo->prepare($sql);

        $this->result->execute($parameters);

        return $this->result;
    }
}





