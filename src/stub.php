<?php
spl_autoload_register(function($className) {
    if (0 !== strpos($className, 'BigWhoop\\TypeHintMe')) {
        return true;
    }
    
    $path = Phar::running() . '/' . str_replace('\\', '/', $className) . '.php';
    require_once $path;
    
    return true;
});