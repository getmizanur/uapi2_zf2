<?php
/**
 * Kooper library (https://bitbucket.org/ottilus/library-kooper)
 *
 * @link       https://bitbucket.org/ottilus/library-kooper for the canonical source repository
 * @copyright  Copyright (c) 2013 OTTilus Ltd. (http://www.ottilus.com)
 * @category   Kooper
 * @package    Kooper_User
 */
namespace Kooper\User;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Service factory layer to allow support for injecting endpoint URLs used for
 * interacting with the user API
 * 
 * @category   Kooper
 * @package    Kooper_User
 */
class UserServiceFactory implements FactoryInterface
{
    /**
     * Create db adapter service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Adapter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $uConfig = isset($config['user']) ? $config['user'] : array();
        return new User($uConfig);
    }
}
