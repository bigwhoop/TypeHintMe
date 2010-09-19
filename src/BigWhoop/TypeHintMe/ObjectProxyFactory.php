<?php
namespace BigWhoop\TypeHintMe;

class ObjectProxyFactory extends Base\Object
{
    /**
     * Create a new instance of a specific class and validate
     * the constructor parameters
     * 
     * @param string $className
     * @param array $constructorArguments
     * @param array $proxyOptions
     * @return object
     */
    static public function create($className, array $constructorArguments = array(), array $proxyOptions = array())
    {
        if (!class_exists($className)) {
            throw new Exception\InvalidArgumentException("Class '$className' not found");
        }
        
        $reflectionClass = new \ReflectionClass($className);
        $object = $reflectionClass->newInstanceArgs($constructorArguments);
        
        $proxy = new ObjectProxy($object, $proxyOptions);
        $proxy->proxyMethod('__construct', $constructorArguments);
        
        return $proxy;
    }
}