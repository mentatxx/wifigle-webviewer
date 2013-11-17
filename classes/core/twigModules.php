<?php

require_once '../classes/core/modules.php';
require_once '../classes/markitup/markitup.bbcode-parser.php';

/**
 * Регистрация фильтров TWIG для их вызовов из шаблона
 * @param type $twig
 */
function registerTwigModules($twig) {
    $twig->addFunction('moduleEnabled', new Twig_Function_Function('_moduleEnabled'));
    $twig->addFunction('moduleRawHTML', new Twig_Function_Function('_moduleRawHTML'));
    //
    $twig->addFilter('boldItems', new Twig_Filter_Function('_boldItems'));
    $twig->addFilter('minusify', new Twig_Filter_Function('_minusify'));
    $twig->addFilter('coolify', new Twig_Filter_Function('_coolify'));
    $twig->addFilter('spacify', new Twig_Filter_Function('_spacify'));
    $twig->addFilter('translit', new Twig_Filter_Function('_translit'));
    $twig->addFilter('dump', new Twig_Filter_Function('_dump'));
    $twig->addFilter('russianDate', new Twig_Filter_Function('_russianDate'));
    $twig->addFilter('fromBBCode', new Twig_Filter_Function('_fromBBCode'));
    $twig->addFilter('beforeCut', new Twig_Filter_Function('_beforeCut'));
    $twig->addFilter('urlify', new Twig_Filter_Function('_urlify'));
}


/*
 * Internal magic wrappers. Dontuse outside
 */
function _moduleEnabled($moduleName)
{
    $modules = Modules::getInstance();
    return $modules->moduleEnabled($moduleName);
}

function _moduleRawHTML($moduleName)
{
    $modules = Modules::getInstance();
    return $modules->moduleRawHTML($moduleName);
}

function _boldItems($string, $items)
{
    $result = strip_tags($string);
    foreach (array_unique($items) as $item) 
    {
        $rule = '/('.preg_quote($item).')/i';
        $result = preg_replace ($rule, '<b>${1}</b>', $result);
    }
    return $result;
}

function _minusify($string)
{
    $s = str_replace(' ', '-', $string);
    $ss = str_replace('--', '-', $s);
    return $ss;
}

function _coolify($string)
{
    $s = strtolower( preg_replace('/[^a-zA-Z0-9.\p{Cyrillic}]/u', '-', $string) );
//    $s = strtolower( preg_replace('/[^a-zA-Z0-9.]/', '-', $string) );
    $ss = '';
    while ($s <> $ss) {
        $ss = $s;
        $s = str_replace('--', '-', $s);
    }
    return $s;
}

function _spacify($string)
{
    $s = preg_replace('/[^a-zA-Z0-9.\p{Cyrillic}]/u', '-', $string);
    $ss = '';
    while ($s <> $ss) {
        $ss = $s;
        $s = str_replace('--', '-', $s);
    }
    $sss = str_replace('-', ' ', $s);
    return trim($sss);
}

function _translit($str) 
{
    $tr = array(
        "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
        "Д"=>"D","Е"=>"E","Ж"=>"J","З"=>"Z","И"=>"I",
        "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
        "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
        "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
        "Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"YI","Ь"=>"",
        "Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
        "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
        "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
        "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
        "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
        "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
        "ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya"
    );
    return strtr($str,$tr);
}


function _dump($string)
{
    return var_export($string, TRUE);
}

function _fromBBCode($string)
{
    return BBCode2Html($string);
}

function _beforeCut($string)
{
    $p = strpos($string, '[cut]');
    if ($p===FALSE) {
        return $string;
    } else {
        return substr($string, 0, $p);
    }
}

function _urlify($string)
{
    $loweredString = mb_strtolower($string, 'utf-8');
    $s = strtolower( preg_replace('/[^a-zA-Z0-9.\p{Cyrillic}]/u', '-', $loweredString) );
    $ss = '';
    while ($s <> $ss) {
        $ss = $s;
        $s = str_replace('--', '-', $s);
    }
    if (strlen($s)>0)
    return ltrim(rtrim($s, '-'),'-');
}

function _russianDate($dt){
$date=explode(".", date("d.m.Y", strtotime($dt)));
switch ($date[1]){
case 1: $m='января'; break;
case 2: $m='февраля'; break;
case 3: $m='марта'; break;
case 4: $m='апреля'; break;
case 5: $m='мая'; break;
case 6: $m='июня'; break;
case 7: $m='июля'; break;
case 8: $m='августа'; break;
case 9: $m='сентября'; break;
case 10: $m='октября'; break;
case 11: $m='ноября'; break;
case 12: $m='декабря'; break;
}
echo $date[0].'&nbsp;'.$m.'&nbsp;'.$date[2];
}


?>
