<?php
/**
 * Kooper library (https://bitbucket.org/ottilus/library-kooper)
 *
 * @link       https://bitbucket.org/ottilus/library-kooper for the canonical source repository
 * @copyright  Copyright (c) 2013 OTTilus Ltd. (http://www.ottilus.com)
 * @category   Kooper
 * @package    Kooper_Mvc
 * @subpackage Service
 */
namespace Kooper\Mvc\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Kooper\Mvc\View\Http\RestManager;

/**
 * Simple factory layer to fetch the RestManager instance
 * 
 * @category   Kooper
 * @package    Kooper_Mvc
 * @subpackage Service
 */
class RestManagerFactory
    implements FactoryInterface
{
    /**
     * Create and return the rest manager instance
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return RestManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new RestManager();
    }
}