<?php
$mktimeStart = microtime(TRUE); // иззмеряем время выполнения скрипта

session_start();
if (!isset($_SERVER['REMOTE_ADDR'])) {
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
};

// Local includes
require_once 'PEAR.php';
require_once 'Twig/Autoloader.php';
require_once '../classes/config.php'; 
// Core
require_once '../classes/core/databaseNoCache.php'; 
require_once '../classes/core/twigModules.php'; 
// Pages
require_once '../classes/pages/upload.php';
// Modules
require_once '../classes/modules/commonModule.php';


// import from config.php
global $databaseConfig;
global $memcachedConfig;
global $twigConfig;

global $data; // Data for template. Global for all modules
global $template;
$data = array();

// connect to Database (mysql&memcached)
$db = Database::getInstance();
$db->connect($databaseConfig['dsn'], $databaseConfig['user'], $databaseConfig['password'], $memcachedConfig['id'], $memcachedConfig['ip'], $memcachedConfig['port']);

// Включение шаблонизатора
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem( $twigConfig['templates'] );
$twig = new Twig_Environment($loader, array('cache' => $twigConfig['cache'], 'auto_reload' => 'True'));
$twig->addExtension(new Twig_Extensions_Extension_I18n());
registerTwigModules($twig);

//Routing
$modules = Modules::getInstance();
$q = isset($_GET['q']) ? $_GET['q'] :'/';
$page = $modules->getPage($q);
if ($page) {
    $template = $page['name'].'.twig';
    $parameters = $modules->getParameters($page, $q);
} else {
    // Apache one
    header("HTTP/1.0 404 Not Found");
    // Fastcgi one
    header("Status: 404 Not Found");
    $template = "404.twig";
    $parameters = array();
    $page = array('id'=>10, 'name'=>'404', 'url'=>'/404', 'description'=>'Страница не найдена', 'cacheable'=>0);
};

// Fill common data
$modules->callControllers($modules->getPageByName('_common'), array());
// Fill page
$modules->callControllers($page, $parameters);
// check if template exists
if (!file_exists($twigConfig['templates'].'/'.$template)) {
    // Apache one
    header("HTTP/1.0 404 Not Found");
    // Fastcgi one
    header("Status: 404 Not Found");
    //
    $data['template'] = $template;
    $template = '404template.twig';
}
// load template into engine
try {
    $templateEngine = $twig->loadTemplate($template);
} catch (Twig_Error_Syntax $e) {
    // turn the error message into a validation error 
    die("TWIG syntax error: ".$e->getRawMessage()."<br>\n File: ".$e->getTemplateFile()." : ".$e->getTemplateLine());
}
// make output
$data['renderTime'] = microtime(TRUE) - $mktimeStart;
if (isset($data['debug'])) {
    $data['debug'] = var_export($data, TRUE);
}
$cachedPage = $templateEngine->render($data);
echo $cachedPage;

?>