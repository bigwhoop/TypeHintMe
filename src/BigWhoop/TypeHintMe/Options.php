<?php
namespace BigWhoop\TypeHintMe;

class Options extends Base\Object
{
    static public function merge(array $defaults, array $values)
    {
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $innerKey = isset($defaults[$key])
                          ? $defaults[$key]
                          : array();
                $value = self::merge($innerKey, $value);
            }
            
            $defaults[$key] = $value;
        }
        
        return $defaults;
    }
}