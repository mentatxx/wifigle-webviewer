<?php
require_once '../classes/core/databaseNoCache.php';
require_once '../classes/core/moduleExtension.php';

class commonModule extends ModuleExtension
{
    public function call($parameters) {
        global $data; // template data output
        global $yandexMapsKey;
        $data['yandexMapsKey'] = $yandexMapsKey;
        $data['ngapp'] = 'wifigle';
    }
}

commonModule::register('commonModule');
?>
