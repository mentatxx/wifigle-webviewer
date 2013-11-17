<?php

class Curl {
    /**
     *
     * @var Curl 
     */
    private static $p_Instance;

    private $curl;
    private $initialized = FALSE;
            
    private function __construct() {
        $this->curl = NULL;
        $this->initialized = FALSE;
    }
    
    /**
     * 
     * Get singleton object
     * 
     * @return Database
     */
    public static function getInstance() 
    { 
        if (!self::$p_Instance) 
        { 
            self::$p_Instance = new Curl(); 
        } 
        return self::$p_Instance; 
    }  

    function initCurl() {
        if ($this->initialized) return;
        
        // создание нового cURL ресурса
        $ch = curl_init();

        // установка URL и других необходимых параметров
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
        curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt");
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.1) Gecko/2008070208');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);

        $this->curl = $ch;
        $this->initialized = TRUE;
    }

    function finalCurl() {
        // завершение сеанса и освобождение ресурсов
        curl_close($this->curl);
        $this->curl = NULL;
        $this->initialized = FALSE;
    }

    function fetch($url) {
        if (!$this->initialized) {
            $this->initCurl();
        }
        curl_setopt($this->curl, CURLOPT_URL, $url);
        // загрузка страницы и выдача её браузеру
        $result = curl_exec($this->curl);
        return $result;
    }
}

?>
