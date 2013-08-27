<?php
return array(
    'modules' => array(
        'Synapse',
        //'Cron',
	//'Administration',
    ),
    'module_listener_options' => array( 
        'config_cache_enabled' => false,
        'cache_dir' => 'data/cache',
        'config_static_paths' => array(
            __DIR__ . '/../../config/autoload/development.config.php',
        ),
        'module_paths' => array(
            'Synapse' => __DIR__ . '/../../module/Synapse',
            //'Cron' => __DIR__ . '/../../module/Cron',
            //'Administration' => __DIR__ . '/../../module/Administration',
        ),
    ),
);
