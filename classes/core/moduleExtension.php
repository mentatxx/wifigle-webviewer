<?php
/**
 * 
 * Abstract classes for controller registration
 * 
 */
require_once '../classes/core/modules.php';

interface IModuleExtension
{
    static public function register($name);
    public function call($parameters);
}

abstract class ModuleExtension implements IModuleExtension
{
    static public function register($name)
    {
        Modules::registerModuleController($name, get_called_class());
    }
    abstract public function call($parameters);
}

abstract class PageExtension implements IModuleExtension
{
    static public function register($name)
    {
        Modules::registerPageController($name, get_called_class());
    }
    abstract public function call($parameters);
}

