<?php
return array(
    'routes' => array(
        'home' => array(
            'type' => 'literal',
            'options' => array(
                'route'    => '/',
                'defaults' => array(
                    'controller' => 'synapse-index',
                    'action' => 'index',
                ),
            ),
        ),
        'synapse-login' => array(
            'type' => 'segment',
            'options' => array(
                'route'    => '/login',
                'defaults' => array(
                    'controller' => 'synapse-login',
                ),
            ),
        ), 
        'synapse-register' => array(
            'type' => 'segment',
            'options' => array(
                'route'    => '/registerDevice',
                'defaults' => array(
                    'controller' => 'synapse-register',
                ),
            ),
        ), 
        'synapse-session' => array(
            'type' => 'segment',
            'options' => array(
                'route'    => '/authsession',
                'defaults' => array(
                    'controller' => 'synapse-session',
                ),
            ),
        ),
    ),
);
