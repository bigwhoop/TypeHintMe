<?php
namespace BigWhoop\TypeHintMe;

class ObjectProxy extends Base\Object
{
    /**
     * @var array
     */
    protected static $_defaultOptions = array(
        'trackMethods'    => array(),
        'throwExceptions' => false,
        'errorLevel'      => E_USER_WARNING,
    );
    
    /**
     * @var object
     */
    protected $_object = null;
    
    /**
     * @var array
     */
    protected $_cache = array();
    
    
    /**
     * Return the default options
     * 
     * @return array
     */
    static public function getDefaultOptions()
    {
        return self::$_defaultOptions;
    }
    
    
    /**
     * Set the default options
     * 
     * @param array $options
     */
    static public function setDefaultOptions(array $options)
    {
        $options = Options::merge(self::getDefaultOptions(), $options);
        
        self::$_defaultOptions = $options;
    }
    
    
    /**
     * __constructor
     * 
     * @param object $object
     * @param array $options
     */
    public function __construct($object, array $options = array())
    {
        if (!is_object($object)) {
            throw new Exception\InvalidArgumentException('Argument 1 passed to ' . get_called_class() . '::__construct() must be an object.');
        }
        
        $this->_object = $object;
        
        parent::__construct($options);
    }
    
    
    /**
     * Set the proxy options
     * 
     * @param array $options
     * @return BigWhoop\TypeHintMe\ObjectProxy
     */
    public function setOptions(array $options)
    {
        $this->_options = Options::merge(self::getDefaultOptions(), $options);
        
        return $this;
    }
    
    
    /**
     * Validate a specific method with a given set of parameters
     * 
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function proxyMethod($method, array $params)
    {
        $classMethod = get_class($this->_object) . '::' . $method . '()';
        
        // Simple validator caching per method
        if (!array_key_exists($method, $this->_cache)) {
            $classReflection = new \ReflectionClass(get_class($this->_object));
            
            if (!$classReflection->hasMethod($method)) {
                throw new Exception\BadMethodCallException('Method ' . $classMethod . ' does not exist.');
            }
            
            $methodReflection = $classReflection->getMethod($method);   
            $docBlock = $methodReflection->getDocComment();
            
            unset($classReflection, $methodReflection);
            $this->_cache[$method] = new DocBlockValidator($docBlock);
        }
        
        $validator = $this->_cache[$method];
        $result = $validator->validate($params);
        
        foreach ($result as $argumentIdx => $success) {
            if ($success) {
                continue;
            }
            
            $msg = sprintf(
                'Argument %d passed to %s must be of type %s.',
                $argumentIdx,
                $classMethod,
                join(' or ', $validator->getTypesForArgument($argumentIdx))
            );
            
            if ($this->_options['throwExceptions']) {
                throw new Exception\InvalidArgumentException($msg);
            } else {
                trigger_error($msg, $this->_options['errorLevel']);
            }
        }
        
        return call_user_func_array(array($this->_object, $method), $params);
    }
    
    
    /**
     * Watch and validate every method call.
     * 
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function __call($method, array $params)
    {
        $methodsToTrack = (array)$this->_options['trackMethods'];
        
        if (count($methodsToTrack) && !in_array($method, $methodsToTrack)) {
            return call_user_func_array(array($this->_object, $method), $params);
        }
        
        return $this->proxyMethod($method, $params);
    }
}