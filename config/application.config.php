<?php
return array(
    'modules' => array(
        'Synapse',
    ),
    'module_listener_options' => array( 
        'config_cache_enabled' => false,
        'cache_dir' => 'data/cache',
        'config_static_paths' => array(
            //__DIR__ . '/autoload/' . APPLICATION_ENV . '.config.php',
            __DIR__ . '/autoload/development.config.php',
        ),
        'module_paths' => array(
            'Synapse' => __DIR__ . '/../module/Synapse',
        ),
        'lazy_loading' => array(
            'synapse' => array (
                'hostname' => 'uapi2.staging.simplestream.com',
                'port' => 80,
            ),
        ),
    ),
    'service_manager' => array(
        'factories'    => array(
            'ModuleManager' => 'Mm\Mvc\Service\ModuleManagerFactory',
        ),
    ),
);
