<?php

require_once __DIR__ . '/../core/databaseNoCache.php';
require_once __DIR__ . '/../core/moduleExtension.php';
require_once __DIR__ . '/../core/twigModules.php';

class upload extends PageExtension {

    public function call($parameters) {
        if (isset($_POST['btnUpload'])) {
            $this->uploadPosted();
        }
    }

    public function uploadPosted() {
        global $data;
        if (isset($_FILES['fileSelector']) && !$_FILES['fileSelector']['error']) {
            $tmpFilename = $_FILES['fileSelector']['tmp_name'];
            $content = utf8_encode(file_get_contents($tmpFilename));
            $points = $this->parseKML($content);
            $this->saveToDatabase($points);
            if ($points) {
                $data['success'] = 'Parsed succesfully';
            } else {
                $data['error'] = 'Invalid .KML file';
            }
        } else {
            $data['error'] = 'Please choose .KML file';
        }
    }

    public function parseKML($content) {
        global $data;
        $xml = simplexml_load_string($content);
        if (!$xml) {
            $data['error'] = 'File not found';
        }
        $xml->registerXPathNamespace('kml', 'http://www.opengis.net/kml/2.2');
        $placemarks = $xml->xpath('//kml:Placemark');
        $cnt = 0;
        $result = array();
        foreach ($placemarks as $placemark) {
//            $attributes = $placemark->attributes();
            $coordinates = (string) $placemark->Point->coordinates;
            $name = (string) $placemark->name;
            $description = (string) $placemark->description;

            $result[] = array('name' => $name, 'coordinates' => $coordinates, 'description' => $description);
            $cnt++;
        }
        $data['count'] = $cnt;
        return $result;
    }

    public function saveToDatabase($points) {
        $db = Database::getInstance();
        foreach ($points as $point) {
            $name = $point['name'];
            $coordinates = $point['coordinates'];
            list($lat, $lon) = explode(',', $coordinates);
            $description = $point['description'];
            $mac = $this->extractMAC($description);
            $capabilities = $this->extractCapabilities($description);
            $wps = $this->extractWPS($capabilities);
            $wep = $this->extractWEP($capabilities);
            $wpa2 = $this->extractWPA2($capabilities);
            $wpa = $this->extractWPA($capabilities);

            $db->execute('REPLACE INTO aps(`mac`, `lat`, `lon`, `wps`, `wep`, `wpa`, `wpa2`, `network`, `point`) VALUES (:mac, :lat, :lon, :wps, :wep, :wpa, :wpat, :network, POINT(:lat, :lon))', 
                    array(':mac' => $mac, ':lat' => $lat, ':lon' => $lon, ':wps' => $wps, ':wep' => $wep, ':wpa' => $wpa, ':wpat' => $wpa2, ':network' => $name));
        }
    }

    public function extractMAC($description) {
        $matches = array();
        if (preg_match('/BSSID: <b>(.*?)<\/b>/', $description, $matches)) {
            return strtoupper($matches[1]);
        } else {
            return '';
        }
    }

    public function extractCapabilities($description) {
        $matches = array();
        if (preg_match('/Capabilities: <b>(.*?)<\/b>/', $description, $matches)) {
            return strtoupper($matches[1]);
        } else {
            return '';
        }
    }

    public function extractWPS($capabilities) {
        return (strpos($capabilities, '[WPS')!==FALSE) ? 1 : 0;
    }

    public function extractWEP($capabilities) {
        return (strpos($capabilities, '[WEP')!==FALSE) ? 1 : 0;
    }

    public function extractWPA2($capabilities) {
        return (strpos($capabilities, '[WPA2')!==FALSE) ? 1 : 0;
    }

    public function extractWPA($capabilities) {
        return preg_match('/\[WPA[^2]/', $capabilities) ? 1 : 0;
    }

}

upload::register('upload');
