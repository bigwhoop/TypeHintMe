<?php
namespace BigWhoop\TypeHintMe;

class ObjectObserver
{
    /**
     * @var object
     */
    protected $_object;
    
    /**
     * @var array
     */
    static protected $_defaultOptions = array(
        'skipMethods' => array(),
        'throwExceptions' => false,
        'errorLevel' => E_USER_ERROR,
    );
    
    /**
     * @var array
     */
    protected $_cache = array();
    
    
    static public function create($className, array $constructorArguments = array(), array $options = array())
    {
        if (!class_exists($className)) {
            throw new Exception\InvalidArgumentException('Class bla...');
        }
        
        $options = self::_mergeOptions(self::$_defaultOptions, $options);
        
        $reflectionMethod = new \ReflectionMethod($className, '__construct');
        
        $validator = new DocBlockValidator();
        $errors = $validator->validate($reflectionMethod->getDocComment(), $constructorArguments);
        
        foreach ($errors as $argumentIdx => $requestedTypes) {
            $msg = sprintf(
                'Argument %d passed to %s::__construct() must be of type %s.',
                $argumentIdx,
                $className,
                join(' or ', $requestedTypes)
            );
            
            if ($options['throwExceptions']) {
                throw new Exception\InvalidArgumentException($msg);
            } else {
                trigger_error($msg, $options['errorLevel']);
            }
        }
        
        $object = $reflectionMethod->getDeclaringClass()->newInstanceArgs($constructorArguments);
        return new self($object, $options);
    }
    
    
    static protected function _mergeOptions(array $defaults, array $values)
    {
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $innerKey = isset($defaults[$key])
                          ? $defaults[$key]
                          : array();
                $value = self::_mergeOptions($innerKey, $value);
            }
            
            $defaults[$key] = $value;
        }
        
        return $defaults;
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
        $this->setOptions($options);
    }
    
    
    public function setOptions(array $options)
    {
        $this->_options = self::_mergeOptions(self::$_defaultOptions, $options);
    }
    
    
    /**
     * Observe every method call.
     * 
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function __call($method, array $params)
    {
        $methodsToSkip = (array)$this->_options['skipMethods'];
        
        if (count($methodsToSkip) && !in_array($method, $methodsToSkip)) {
            return call_user_func_array(array($this->_object, $method), $params);
        }
        
        $classMethod = get_class($this->_object) . '::' . $method . '()';
        
        if (!array_key_exists($method, $this->_cache)) {
            $classReflection = new \ReflectionClass(get_class($this->_object));
            
            if (!$classReflection->hasMethod($method)) {
                throw new Exception\BadMethodCallException('Method ' . $classMethod . ' does not exist.');
            }
            
            $methodReflection = $classReflection->getMethod($method);   
            $docBlock = $methodReflection->getDocComment();
            
            unset($classReflection, $methodReflection);
            $this->_cache[$method] = $docBlock;
        }
        
        $validator = new DocBlockValidator();
        $errors = $validator->validate($this->_cache[$method], $params);
        
        foreach ($errors as $argumentIdx => $requestedTypes) {
            $msg = sprintf(
                'Argument %d passed to %s must be of type %s.',
                $argumentIdx,
                $classMethod,
                join(' or ', $requestedTypes)
            );
            
            if ($this->_options['throwExceptions']) {
                throw new Exception\InvalidArgumentException($msg);
            } else {
                trigger_error($msg, $this->_options['errorLevel']);
            }
        }
        
        return call_user_func_array(array($this->_object, $method), $params);
    }
}