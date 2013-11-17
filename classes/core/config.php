<?php

require_once __DIR__ . '/databaseNoCache.php';

class Config {

    /**
     * Хранитель синглетона
     * 
     * @var Config 
     */
    private static $hInstance;
    private $configItems;

    /**
     * Приватный конструктор
     * 
     */
    private function __construct() {
        $db = Database::getInstance();

        foreach ($db->queryRows('SELECT `name`, `section`, `val` FROM `settings`') as $configItem) {
            $val = $configItem['val'];
            if ($val !== '') {
                // try to decode json value, if is it
                if (($val[0] == '{') || ($val[0] == '[')) {
                    $temp = json_decode($val, true);
                    if (json_last_error() == JSON_ERROR_NONE) {
                        $val = $temp;
                    }
                }
            }
            $this->configItems[$configItem['section']][$configItem['name']] = $val;
        }
    }

    /**
     * 
     * Get singleton object
     * 
     * @return Config
     */
    public static function getInstance() {
        if (!self::$hInstance) {
            self::$hInstance = new Config();
        }
        return self::$hInstance;
    }

    public function __get($section) {
        if (array_key_exists($section, $this->configItems)) {
            return $this->configItems[$section];
        } else {
            error_log('Config: Section not found - ' . $section);
        }
    }

    public function __invoke($section, $name) {
        if (array_key_exists($section, $this->configItems)) {
            $sections = $this->configItems[$section];
            if (array_key_exists($name, $sections)) {
                return $sections[$name];
            } else {
                error_log("Config: Item not found - $section / $name");
                return NULL;
            }
        } else {
            error_log('Config: Section not found - ' . $section);
            return NULL;
        }
    }

}
