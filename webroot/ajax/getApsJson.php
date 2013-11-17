<?php

// Local includes
require_once 'PEAR.php';
require_once '../../classes/config.php';
// Core
require_once '../../classes/core/databaseNoCache.php';

function queryForDatabase($mac, $network, $wps, $wep, $wpa, $wpa2) {
    $db = Database::getInstance();
    $wheres = array();
    $params = array();
    //
    if ($mac !== FALSE) {
        $wheres[] = '`mac` = :mac';
        $params[':mac'] = $mac;
    }
    if ($network !== FALSE) {
        $wheres[] = '`network` = :network';
        $params[':network'] = $network;
    }
    if ($wps) {
        $wheres[] = '`wps` = 1';
    }
    //
    $opts = array();
    if ($wep) {
        $opts[] = '`wep` = 1';
    }
    if ($wpa) {
        $opts[] = '`wpa` = 1';
    }
    if ($wpa2) {
        $opts[] = '`wpa2` = 1';
    }
    $optsText = implode(' OR ', $opts);
    if ($optsText) {
        $wheres[] = "($optsText)";
    }
    //
    $wheresText = implode(' AND ', $wheres);
    if ($wheresText) {
        $sql = "SELECT * FROM `aps` WHERE $wheresText";
    } else {
        $sql = "SELECT * FROM `aps` LIMIT 1000";
    }
    $points = $db->queryRows($sql, $params);
    return $points;
}

function parseParameters() {
    $wps = isset($_GET['wps'])?1:0;
    $wep = isset($_GET['wep'])?1:0;
    $wpa = isset($_GET['wpa'])?1:0;
    $wpa2 = isset($_GET['wpa2'])?1:0;
    $mac = isset($_GET['mac'])?$_GET['mac']:FALSE;
    $network = isset($_GET['network'])?$_GET['network']:FALSE;
    return array($mac, $network, $wps, $wep, $wpa, $wpa2);
}

global $databaseConfig;

// connect to Database (mysql&memcached)
$db = Database::getInstance();
$db->connect($databaseConfig['dsn'], $databaseConfig['user'], $databaseConfig['password'], $memcachedConfig['id'], $memcachedConfig['ip'], $memcachedConfig['port']);

list ($mac, $network, $wps, $wep, $wpa, $wpa2) = parseParameters();
echo json_encode(queryForDatabase($mac, $network, $wps, $wep, $wpa, $wpa2));
