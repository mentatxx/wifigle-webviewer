<?php
/**
 * 
 * MF module system
 * 
 */
require_once __DIR__.'/databaseNoCache.php';

class Modules {
    /**
     * Singleton instance
     * @var Modules 
     */
    private static $p_Instance;
    // DB info
    private $loaded;
    private $pages;
    private $modules;
    // Список зарегистрированных модулей
    private static $registeredPageControllers = array();
    private static $registeredModuleControllers = array();

    private function __construct() {
        $this->loaded = FALSE;
    }
    
    /**
     * 
     * Get singleton object
     * 
     * @return Modules
     */
    public static function getInstance() 
    { 
        if (!self::$p_Instance) 
        { 
            self::$p_Instance = new Modules(); 
        } 
        return self::$p_Instance; 
    }  
    
    
    /**
     * Check if module enabled in configuration
     * 
     * @param type $moduleName
     * @return boolean
     */

    public function moduleEnabled($moduleName)
    {
        if (!$this->loaded) $this->loadFromDb();
        // look in cached modules
        foreach ($this->modules as $row)
            if ($row['name'] == $moduleName) return $row['enabled'];
        // If not found, assume it is visible
        error_log("MF: Module $moduleName not found in database");
        return TRUE;
    }

    /**
     * Get raw HTML block
     * 
     * @param type $moduleName
     * @return string
     */
    public function moduleRawHTML($moduleName)
    {
        if (!$this->loaded) $this->loadFromDb();
        // look in cached modules
        foreach ($this->modules as $row)
            if ($row['name'] == $moduleName) return $row['rawHTML'];
        // if not found, output stub
        error_log("MF: Module $moduleName not found in database");
        $stub = '<div style="background:#F2F2F2; border: 1px solid #E9E9E9; text-align:center; padding: 20px;"><h2>Мой HTML код</h2></div>';
        return $stub;
    }

    /**
     * Load or Reload routing info from database
     * 
     */
    private function loadFromDb()
    {
        $db = Database::getInstance();
        $this->pages = $db->queryRows('SELECT * FROM pages', array());
        $this->modules = $db->queryRows('SELECT * FROM modules', array());
        $this->loaded = TRUE;
    }
    
    /**
     * Parse URL and get matched page
     * 
     * @param string $route - path part of URL
     * @return array|boolean - return page info or FALSE
     */
    public function getPage($route)
    {
        if (!$this->loaded) $this->loadFromDb();
        // First try exact match
        foreach ($this->pages as $page) { if ($route == $page['url']) return $page; };
        // Next try pattern exact match
        foreach ($this->pages as $page) { if (preg_match('@^'.$page['url'].'$@', $route)) return $page; };
        // if not found
        return FALSE;
    }

    /**
     * Parse URL and get matched page
     * 
     * @param string $name - Page name
     * @return array|boolean - return page info or FALSE
     */
    public function getPageByName($name)
    {
        if (!$this->loaded) $this->loadFromDb();
        // First try exact match
        foreach ($this->pages as $page) { if ($name == $page['name']) return $page; };
        // if not found
        return FALSE;
    }

    /**
     * Extract parameters from URL
     * 
     * @param array $page
     * @param string $route
     * @return array
     */
    public function getParameters($page, $route)
    {
        $matches = array();
        if (preg_match('@^'.$page['url'].'$@', $route, $matches)) {
            return array_slice($matches, 1);
        } else {
            return array();
        }
    }
    
    /**
     * Call page specific controllers
     * 
     * @param array $page Page description
     * @param array $parameters URL parameters
     */
    public function callControllers($page, $parameters)
    {
        if (!$this->loaded) $this->loadFromDb();
        // call page controller
        if (isset(self::$registeredPageControllers[$page['name']])) {
            $pageControllerClass = self::$registeredPageControllers[$page['name']];
            $pageController = new $pageControllerClass;
            $pageController->call($parameters);
        }
        // call page modules controllers
        $pageId = $page['id'];
        foreach ($this->modules as $module)
        {
            if ( ($module['page'] == 0) || (($module['page'] == $pageId) && $module['enabled'] && !$module['isRaw'])) {
                if (isset(self::$registeredModuleControllers[$module['name']]))
                {
                    $moduleControllerClass = self::$registeredModuleControllers[$module['name']];
                    $moduleController = new $moduleControllerClass;
                    $moduleController->call($parameters);
                }
            }
        }
        return TRUE;
    }
    
    /**
     * Register page controller
     * 
     * @param string $pageName
     * @param PageExtension $controller
     */
    static public function registerPageController($pageName, $controller)
    {
        self::$registeredPageControllers[$pageName] = $controller;
    }
    
    /**
     * Register module controller
     * 
     * @param string $moduleName
     * @param ModuleExtension $controller
     */
    static public function registerModuleController($moduleName, $controller)
    {
        self::$registeredModuleControllers[$moduleName] = $controller;
    }
    
    /**
     * Render given template with array of substitutions
     * 
     * @param string $template
     * @param array $subs
     * @return string
     */
    static public function renderTemplate( $template, $subs )
    {
        $result = $template;
        foreach ($subs as $what => $to) { $result = str_replace($what, $to, $result); };
        return $result;
    }
    
    /**
     * Render given title. 
     * Works like renderTemplate, but knows {page}{/page} template
     * 
     * @param string $template
     * @param array $subs
     * @param boolean $isFirstPage
     * @return string
     */
    static public function renderTitle( $template, $subs, $isFirstPage )
    {
        if ($isFirstPage) {
            $pTemplate = preg_replace('/(.*)\{page\}(.*)\{\/page\}(.*)/', '$1$3', $template);
        } else {
            $pTemplate = preg_replace('/(.*)\{page\}(.*)\{\/page\}(.*)/', '$1$2$3', $template);
        }
        return self::renderTemplate($pTemplate, $subs);
    }
}    

