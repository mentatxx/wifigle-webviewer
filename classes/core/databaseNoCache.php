<?php

/**
 * 
 * Database wrapper for PDO with memcached
 * Singleton pattern used.
 * 
 */

class Database 
{
    /**
     * Singleton instance storage
     * @var Database 
     */
    private static $p_Instance;
    /**
     * PDO wrapper for database
     * @var PDO 
     */
    private $dbh;
    private $connected;
    
    private function __construct() {
        $this->dbh = NULL;
        $this->connected = FALSE;
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
            self::$p_Instance = new Database(); 
        } 
        return self::$p_Instance; 
    }  
    
    /**
     * 
     * Connect to database & memcache
     * 
     * @param type $dsn - PDO source
     * @param type $username - PDO username
     * @param type $password - PDO password
     */
    public function connect($dsn, $username, $password) 
    {
        // Set UTF-8, php 5.3 bug workaround
        $dbOptions=array(1002 => 'SET NAMES utf8',);
        //
        $this->dbh = new PDO( $dsn, $username, $password, $dbOptions);
        $this->connected = TRUE;
    }
    
    /**
     * Выполняет SQL запрос, возвращающий одну строку
     * 
     * @param type $query
     * @param type $parameters
     */
    public function queryOneRow($query, $parameters=array())
    {
        if (!$this->connected) throw new Exception('Connection not initiated');
        
        $stmt = $this->dbh->prepare($query);
        $okay = $stmt->execute($parameters);
        if (!$okay) { $err = $stmt->errorInfo();  error_log ('Database error: '.$query."\n".var_export($err, TRUE)); };
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    
    /**
     * Выполняет запрос, возвращающий несколько строк
     * 
     * @param type $query SQL with placeholders
     * @param type $parameters Parameters
     */
    public function queryRows($query, $parameters=array())
    {
        if (!$this->connected) throw new Exception('Connection not initiated');
        
        $stmt = $this->dbh->prepare($query);
        $okay = $stmt->execute($parameters);
        if (!$okay) { $err = $stmt->errorInfo();  error_log ('Database error: '.$query."\n".var_export($err, TRUE)); };
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    
    /**
     * Выполняет запрос, изменяющий состояние базы, не возвращающий значение
     * 
     * @param string $query SQL with placeholders
     * @param Array $parameters Parameters
     */
    public function execute($query, $parameters=array())
    {
        if (!$this->connected) throw new Exception('Connection not initiated');
        
        $stmt = $this->dbh->prepare($query);
        $okay = $stmt->execute($parameters);
        if (!$okay) { $err = $stmt->errorInfo();  error_log ('Database error: '.$query."\n".var_export($err, TRUE)); };
        return $okay;
    }
    
    /**
     * Get last insert id
     * 
     */
    public function lastInsertId()
    {
        if (!$this->connected) throw new Exception('Connection not initiated');
        return $this->dbh->lastInsertId();
    }
    
    
    /**
     * Делает маппинг результатов по указнному ключу
     * 
     * @param Array $r
     * @param string $key
     * @return bool
     */
    public function mapResults( $r, $key )
    {
        $result = array();
        foreach ($r as $row)
        {
            $result[ $row[$key] ] = $row;
        }
        return $result;
    }
    
    /**
     * Делает маппинг результатов по указнному ключу
     * 
     * @param Array $r
     * @param string $key
     * @return bool
     */
    public function groupResults( $r, $key )
    {
        $result = array();
        foreach ($r as $row)
        {
            $result[ $row[$key] ][] = $row;
        }
        return $result;
    }
}

