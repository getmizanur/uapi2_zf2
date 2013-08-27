<?php
ini_set('date.timezone',"Europe/London");

defined('APPLICATION_ENV')     
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? 
        getenv('APPLICATION_ENV') : 'production'));

chdir(dirname(__DIR__));

include 'init_autoloader.php';

session_start();

Zend\Mvc\Application::init(include 'config/application.config.php')->run();
