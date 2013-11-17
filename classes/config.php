<?php

/**
 * Launch time - Configuration.
 * Replace settings below to what you want
 * 
 */


global $databaseConfig;
global $memcachedConfig;
global $twigConfig;
global $yandexMapsKey;

$databaseConfig = array(
    'dsn' => 'mysql:host=localhost;dbname=wifigle',
    'user' => 'wifigle',
    'password' => 'wifigle'
);

$memcachedConfig = array(
    'id' => 'wifigle',
    'ip' => 'localhost',
    'port' => 11211
);

$twigConfig = array(
    'templates' => '../templates',
    'cache' => '../cache'
);

$yandexMapsKey = 'ACY6h1IBAAAAY2juYwMA6eftMu_HGFAzXQTXDPetFptc3ecAAAAAAAAAAAAKEtqHdamVE1-clc6y3kGx-qeaVw==';


?>
