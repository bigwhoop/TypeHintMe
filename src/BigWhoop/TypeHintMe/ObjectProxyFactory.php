<?php
namespace BigWhoop\TypeHintMe;

class ObjectProxyFactory extends Base\Object
{
    /**
     * @var array
     */
    protected $_options = array(
        'skipMethods'     => array(),
        'throwExceptions' => false,
        'errorLevel'      => E_USER_ERROR,
    );
    
    
    /**
     * Create a new instance of a specific class and validate
     * the constructor parameters
     * 
     * @param string $className
     * @param array $constructorArguments
     * @return object
     */
    public function create($className, array $constructorArguments = array())
    {
        if (!class_exists($className)) {
            throw new Exception\InvalidArgumentException("Class '$className' not found");
        }
        
        $reflectionClass = new \ReflectionClass($className);
        $object = $reflectionClass->newInstanceArgs($constructorArguments);
        
        $proxy = new ObjectProxy($object, $this->_options);
        $proxy->validateMethod('__construct', $constructorArguments);
        
        return $proxy;
    }
}