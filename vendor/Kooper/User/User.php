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

/**
 * Common user object used to store the SSO token and allow the obtaining of the
 * identity and ACL validation of the token
 * 
 * @category   Kooper
 * @package    Kooper_User
 */
class User
{
    /**
     * Compile URI keys
     * @see self::_compileUri()
     */
    const URI_TYPE_HAS_AUTH = "has_identity_uri";
    const URI_TYPE_AUTH_IDENT = "get_auth_identity_uri";
    const URI_TYPE_IS_ALLOWED = "acl_is_allowed";
    const URI_TYPE_ACL_RULES = "acl_rules";
    
    /**
     * Default cUrl timeout (seconds)
     */
    const DEFAULT_REQUEST_TIMEOUT = 10;
    
    /**
     * Default ACL permission
     */
    const DEFAULT_PERMISSION = "read";
    
    /**
     * Authentication hasIdentity unformatted URI
     * First var is the sso
     * 
     * @var string
     */
    protected $_authUri;
    
    /**
     * Authentication getIdentity unformatted URI
     * First var is the sso
     * 
     * @var string
     */
    protected $_authIdentUri;
    
    /**
     * ACL get rules unformatted URI
     * First var is the sso
     * 
     * @var string
     */
    protected $_aclRulesUri;
    
    /**
     * ACL isAllowed unformatted URI
     * First var is the sso
     * Second var is the module
     * Third var is the resource
     * Fourth var is the method
     * 
     * @var string
     */
    protected $_isAllowedUri;
    
    /**
     * Users signed SSO token
     * 
     * @var string
     */
    protected $_sso;
    
    /**
     * Default module for the ACL
     * 
     * @var string
     */
    protected $_defaultModule;
    
    /**
     * cUrl timeout in seconds
     * 
     * @var int
     */
    protected $_requestTimeout = self::DEFAULT_REQUEST_TIMEOUT;
    
    /**
     * Local response cache
     * 
     * @var array
     */
    protected $_response = array();
    
    /**
     * Common responses for authenication
     * 
     * @see self::hasIdentity()
     * @see self::getIdentity()
     * @var array
     */
    protected $_authResponse = array(
        403 => array (
                'response' => array (
                    'code' => 403,
                    'errors' =>  array (
                            'reason' => 'Forbidden',
                            'message' => 'The server understood the request, but is refusing to fulfill it'
                        )
                    ),
                    'identity' => false
                ),
        401 => array(
                'response' => array (
                        'code' => 401,
                        'errors' =>  array ('message' => 'Invalid sso token'),
                        'identity' => false
                    )
                ),
        400 => array (
                'response' => array (
                        'code' => 400,
                        'errors' =>  array ('message' => 'Invalid sso token'),
                        'identity' => false
                    )
                ),
        200 => array (
                    'response' => array (
                        'code' => 200,
                        'identity' => true
                    )
                ),
        0 => array (
                    'response' => array (
                        'code' => 401,
                        'errors' =>  array (
                            'reason' => 'Unauthorized',
                            'message' => 'The request requires user authentication'
                        ),
                        'identity' => false
                    )   
            )
    );

    /**
     * Array of whitelisted IPs that dont require an ACL check
     * 
     * @var array
     */
    private $_ipWhitelist = array ();
    
    /**
     * Constructor
     * 
     * Accepts an array of options which proxy through to {@link self::setOptions()}
     * 
     * @param array $options
     * @return void
     */
    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }
    
    /**
     * Sets a series of options where the array key is the method name and the
     * value is the value to be assigned to the method
     * 
     * e.g array('sso' => '1234') proxies to self::setSso(1234)
     * 
     * @param array $options
     * @return \Bowser\Util\User
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = "set" . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        
        return $this;
    }
    
    /**
     * Set the users signed SSO token
     * 
     * @param string $sso
     * @return \Bowser\Util\User
     */
    public function setSso($sso)
    {
        $this->_sso = strval($sso);
        return $this;
    }
    
    /**
     * Get the users signed SSO token
     * 
     * @return string
     */
    public function getSso()
    {
        return $this->_sso;
    }
    
    /**
     * Set the cUrl request timeout
     * 
     * @param int $ttl
     * @return \Bowser\Util\User
     */
    public function setRequestTimeout($ttl)
    {
        $this->_requestTimeout = intval($ttl);
        return $this;
    }
    
    /**
     * Get the cUrl request timeout
     * 
     * @return int
     */
    public function getRequestTimeout()
    {
        return $this->_requestTimeout;
    }
    
    /**
     * Set the unformatted hasIdentity URI
     * 
     * @param string $uri
     * @return \Bowser\Util\User
     */
    public function setAuthUri($uri)
    {
        $this->_authUri = strval($uri);
        return $this;
    }
    
    /**
     * Get the unformatted hasIdentity URI
     * 
     * @return string
     * @throws \Exception
     */
    public function getAuthUri()
    {
        if (!$this->_authUri) {
            throw new \Exception("No auth URI provided");
        }
        
        return $this->_authUri;
    }
    
    /**
     * Set the unformatted getAuthenticatedIdentity URI
     * 
     * @param string $uri
     * @return \Bowser\Util\User
     */
    public function setAuthIdentUri($uri)
    {
        $this->_authIdentUri = strval($uri);
        return $this;
    }
    
    /**
     * Get the unformatted getAuthenticatedIdentity URI
     * 
     * @return string
     * @throws \Exception
     */
    public function getAuthIdentUri()
    {
        if (!$this->_authIdentUri) {
            throw new \Exception("No auth identity URI provided");
        }
        
        return $this->_authIdentUri;
    }
    
    /**
     * Set the unformatted ACL isAllowed URI
     * 
     * @param string $uri
     * @return \Bowser\Util\User
     */
    public function setIsAllowedUri($uri)
    {
        $this->_isAllowedUri = strval($uri);
        return $this;
    }
    
    /**
     * Get the unformatted ACL isAllowed URI
     * 
     * @return string
     * @throws \Exception
     */
    public function getIsAllowedUri()
    {
        if (!$this->_isAllowedUri) {
            throw new \Exception("No ACL is allowed URI provided");
        }
        
        return $this->_isAllowedUri;
    }
    
    /**
     * Set the unformatted ACL getRules URI
     * 
     * @param string $uri
     * @return \Bowser\Util\User
     */
    public function setAclRulesUri($uri)
    {
        $this->_aclRulesUri = strval($uri);
        return $this;
    }
    
    /**
     * Get the unformatted ACL getRules URI
     * 
     * @return string
     * @throws \Exception
     */
    public function getAclRulesUri()
    {
        if (!$this->_aclRulesUri) {
            throw new \Exception("No ACL rules URI provided");
        }
        
        return $this->_aclRulesUri;
    }
    
    /**
     * Set the default ACL module name
     * 
     * @param string $module
     * @return \Bowser\Util\User
     */
    public function setDefaultModule($module)
    {
        $this->_defaultModule = strval($module);
        return $this;
    }
    
    /**
     * Get the default ACL module name
     * 
     * @return string
     */
    public function getDefaultModule()
    {
        return $this->_defaultModule;
    }
    
    /**
     * Compile the URI based on the URI_TYPE_* constants
     * Formats the assigned URIs adding in the appropriate values succh as the sso token
     * You may pass in extra arguments as an associative array for those URIs which
     * require more than just the sso token assigned
     * 
     * @param string $type
     * @param array $args
     * @return string
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    protected function _compileUri($type = self::URI_TYPE_HAS_AUTH, array $args = null)
    {
        $sso = $this->getSso();
        
        switch ($type) {
            case self::URI_TYPE_IS_ALLOWED:
                $requiredKeys = array('module', 'resource', 'permission');
                
                if (count($requiredKeys) !== count(array_intersect_key(array_flip($requiredKeys), $args))) {
                    throw new \InvalidArgumentException(
                            "ACL is allowed call requires the keys: '" . implode("', '", $requiredKeys) . "'."
                            . "'" . implode("', '", array_keys($args)) . "' given."
                        );
                }
                
                $uri = sprintf($this->getIsAllowedUri(), $sso, $args['module'], $args['resource'], $args['permission']);
            break;
            
            case self::URI_TYPE_ACL_RULES:
                $uri = sprintf($this->getAclRulesUri(), $sso);
            break;
        
            case self::URI_TYPE_AUTH_IDENT:
                $uri = sprintf($this->getAuthIdentUri(), $sso);
            break;
        
            case self::URI_TYPE_HAS_AUTH:
            default :
                $uri = sprintf($this->getAuthUri(), $sso);
            break;
        }
        
        return $uri;
    }
    
    /**
     * Format the hasAuth URI adding in the SSO token
     * 
     * @return string
     */
    protected function _compileAuthUri()
    {
        return $this->_compileUri(self::URI_TYPE_HAS_AUTH);
    }
    
    /**
     * Format the getAuthIdentity URI adding in the SSO token
     * 
     * @return string
     */
    protected function _compileAuthIdentUri()
    {
        return $this->_compileUri(self::URI_TYPE_AUTH_IDENT);
    }
    
    /**
     * Format the ACL getRules URI adding in the SSO token
     * 
     * @return string
     */
    protected function _compileAclRulesUri()
    {
        return $this->_compileUri(self::URI_TYPE_ACL_RULES);
    }
    
    /**
     * Format the ACL hasAccess URI adding in the SSO token, module, resource and permission
     * 
     * @param string $module
     * @param string $resource
     * @param string $permission
     * @return string
     */
    protected function _compileIsAllowedUri($module, $resource, $permission)
    {
        return $this->_compileUri(self::URI_TYPE_IS_ALLOWED, array(
                'module' => $module,
                'resource' => $resource,
                'permission' => $permission
            ));
    }
    
    /**
     * Internal array cache to store the response from any of the cURL calls
     * 
     * @param mixed $response
     * @param string $type Cache key - typically a URI_TYPE_* constant
     * @return \Bowser\Util\User
     */
    protected function _stashResponse($response, $type = self::URI_TYPE_HAS_AUTH)
    {
        $this->_response[$type] = $response;
        return $this;
    }
    
    /**
     * Get the response from the local storage based on the given cache key
     * Returns null if key is not found
     * 
     * @param string $type Cache key - typically a URI_TYPE_* constant
     * @return mixed
     */
    protected function _getResponse($type = self::URI_TYPE_HAS_AUTH)
    {
        if (isset($this->_response[$type])) {
            return $this->_response[$type];
        }
    }
    
    /**
     * Get the users identity based on the assigned SSO token
     * Returns an array based on the template $this->_authResponse
     * If successful, $response['response']['identity'] will contain an array of the users identity
     * 
     * @return array
     */
    public function getIdentity()
    {
        if (null === ($response = $this->_getResponse(self::URI_TYPE_AUTH_IDENT))) {
            $url = $this->_compileAuthIdentUri();
            $ch = curl_init();
            
            if (false === $ch) {
                throw new \Exception("Failed to initialize $url");
            }
                
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->getRequestTimeout());
            $content = trim(curl_exec($ch));
            
            if (false === $content) {
                throw new \Exception(curl_error(), curl_errno());
            }
            
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if (isset($this->_authResponse[$statusCode])) {
                $response = $this->_authResponse[$statusCode];
            } else {
                $response = $this->_authResponse[0];
            }
         
            $response = (array)json_decode($content, true) + $response;
            
            if (!(isset($response['response'])
                  && isset($response['response']['identity'])
                  && $response['response']['identity'])
            ){
                $message = "Invalid response";
                
                if (isset($response['response'])) {
                    $response = $response['response'];
                    if (isset($response['errors'])) {
                        if (isset($response['errors']['message'])) {
                            $message = $response['errors']['message'];
                        } else if (isset($response['errors']['reason'])) {
                            $message = $response['errors']['reason'];
                        }
                    }
                    if (isset($response['code'])) {
                        $statusCode = $response['code'];
                    }
                }
                
                throw new \Exception($message, $statusCode);
            }
            
            $this->_stashResponse($response, self::URI_TYPE_AUTH_IDENT);
        }
        
        return $response;
    }
    
    /**
     * Check if the user has a valid identity based on the assigned SSO token
     * Returns an array based on the template $this->_authResponse
     * $response['response']['hasIdentity'] will be either true or false depending
     * on whether the user token is valid or not
     * 
     * @return array
     */
    public function hasIdentity()
    {
        if (null !== ($response = $this->_getResponse(self::URI_TYPE_HAS_AUTH))) {
            return $response;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_compileAuthUri()); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->getRequestTimeout());
        curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (isset($this->_authResponse[$statusCode])) {
            $response = $this->_authResponse[$statusCode];
        } else {
            $response = $this->_authResponse[0];
        }
        
        $response['response']['hasIdentity'] = 200 === $statusCode ? true : false;
        
        $this->_stashResponse($response, self::URI_TYPE_HAS_AUTH);
        return $response;
    }
    
    /**
     * Gets all the organisations associated to the authenticated user
     * Returns an array of organisation ids, or false if fail
     * 
     * NOTE: A super user will have no organisations assigned as it can access all
     * and will return an empty array
     * 
     * @return array|boolean
     */
    public function getOrganisations()
    {
        $ident = $this->getIdentity();
        if (is_array($ident)
            && isset($ident['data'])
            && isset($ident['data']['items'])
            && isset($ident['data']['items']['groups']) && is_array($ident['data']['items']['groups'])
        ) {
            $organisations = array();
            foreach ($ident['data']['items']['groups'] as $group) {
                if (isset($group['organisation'])
                    && is_array($group['organisation'])
                    && isset($group['organisation']['id'])
                ) {
                    $orgId = $group['organisation']['id'];
                    $organisations[$orgId] = $group['organisation'];
                }
            }
            
            return $organisations;
        }
        return false;
    }
    
    /**
     * Gets all the applications associated to the authenticated user
     * Returns an array of application objects
     * 
     * @param int $organisationId
     * @return array
     */
    public function getApplications($organisationId = null)
    {
        $organisations = (array)$this->getOrganisations();
        $applications = array();
        
        if (null !== $organisationId) {
            if (isset($organisations[$organisationId])) {
                $organisations = array($organisationId => $organisations[$organisationId]);
            } else {
                $organisations = array();
            }
        }
        
        foreach ($organisations as $organisation) {
            if (isset($organisation['applications']) && is_array($organisation['applications'])) {
                foreach ($organisation['applications'] as $application) {
                    $applicationId = $application['id'];
                    $applications[$applicationId] = $application;
                }
            }
        }
        
        return $applications;
    }
    
    /**
     * Check the ACL if the user is allowed access to the given module, resource and permission
     * 
     * @param string $resource e.g buckets/
     * @param string $permission e.g read (defaults to self::DEFAULT_PERMISSION)
     * @param string $module e.g marlow (defaults to self::getDefaultModule())
     * @return boolean TRUE if allowed access, otherwise, FALSE
     */
    public function isAllowed($resource, $permission = null, $module = null)
    {
        if(in_array($_SERVER['REMOTE_ADDR'], $this->_ipWhitelist)) {
            return true;
        }

        if (null === $module) {
            $module = $this->getDefaultModule();
        }
        
        if (null === $permission) {
            $permission = self::DEFAULT_PERMISSION;
        }

        $stashKey = self::URI_TYPE_IS_ALLOWED . "-{$module}-{$resource}-{$permission}";
        
        if (null === ($response = $this->_getResponse($stashKey))) {
            $url = $this->_compileIsAllowedUri($module, $resource, $permission);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->getRequestTimeout());
            curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $content = trim(curl_exec($ch));
            curl_close($ch);

            if (200 === $statusCode) {
                $response = json_decode($content, true);
                $this->_stashResponse($response, self::URI_TYPE_HAS_AUTH);
            }
        }
        
        if (isset($response['response']) && isset($response['response']['hasAccess'])) {
            return (bool)$response['response']['hasAccess'];
        }
        
        return false;
    }
    
    /**
     * Get all the ACL rules assigned to the user based on the assigned SSO token
     * 
     * @return array|boolean Array of rules or FALSE if request failed
     */
    public function getAclRules()
    {
        if (null === ($content = $this->_getResponse(self::URI_TYPE_ACL_RULES))) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->_compileAclRulesUri()); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->getRequestTimeout());
            $content = trim(curl_exec($ch));
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if (200 != $statusCode) {
                return false;
            }

            $this->_stashResponse($content, self::URI_TYPE_ACL_RULES);
        }

        return json_decode($content, true);
    }

    /**
     * Do a cUrl request
     * 
     * @param string $url
     * @return array
     */
    protected function _getQuery($url)
    {
        $ch = curl_init();
        $statusCode = 0;
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->getRequestTimeout());
        
        if(false === ($content = curl_exec($ch))) {
            $this->_log('Curl error: ' . curl_error($ch));
        } else {
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }
        
        curl_close($ch);
        
        return array($statusCode => trim($content));
    }
    
    /**
     * Send a message to the log
     * 
     * @param string $message
     * @return \Bowser\Util\User
     */
    protected function _log($message)
    {
        if (is_array($message)) {
            $message = print_r($message, true);
        }
        
        if (is_string($message)) {
            error_log($message);
        }
        
        return $this;
    }
}
