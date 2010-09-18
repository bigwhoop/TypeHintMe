<?php
namespace BigWhoop\TypeHintMe;

class DocBlockValidator
{
    public function validate($docBlock, array $params)
    {
        $typeHints = $this->extractTypeHints($docBlock);
        
        $errors = array();
        foreach ($params as $idx => $value) {
            if (!array_key_exists($idx, $typeHints)) {
                break;
            }
            
            $paramErrors = $this->validateValue($value, $typeHints[$idx]);
            
            if (count($paramErrors) == count($typeHints[$idx])) {
                $errors[$idx + 1] = $paramErrors;
            }
        }
        
        return $errors;
    }
    
    
    public function extractTypeHints($docBlock)
    {
        $words = preg_split('/\s/', $docBlock, null, PREG_SPLIT_NO_EMPTY);
        
        $typeHints = array();
        for ($c = count($words), $i = 1; $i < $c; $i++) {
            if ('@param' == $words[$i - 1]) {
                $typeHints[] = explode('|', $words[$i]);
            }
        }
        
        return $typeHints;
    }
    
    
    /**
     * Check a value for one or more specific data type(s).
     * 
     * @param mixed $value
     * @param array $types
     * @return array        An array containing the names of all mis-matched data types.
     */
    public function validateValue($value, array $types)
    {
        $errors = array();
        
        foreach ($types as $type) {
            switch ($type)
            {
                case 'bool':
                case 'boolean':
                    if (!is_bool($value)) {
                        $errors[] = 'boolean';
                    }
                    break;
                    
                case 'int':
                case 'integer':
                    if (!is_int($value)) {
                        $errors[] = 'integer';
                    }
                    break;
                    
                case 'float':
                    if (!is_float($value)) {
                        $errors[] = 'float';
                    }
                    break;
                    
                case 'numeric':
                    if (!is_numeric($value)) {
                        $errors[] = 'numeric';
                    }
                    break;
                    
                case 'string':
                    if (!is_string($value)) {
                        $errors[] = 'string';
                    }
                    break;
                    
                case 'null':
                    if (!is_null($value)) {
                        $errors[] = 'null';
                    }
                    break;
                    
                case 'scalar':
                    if (!is_scalar($value)) {
                        $errors[] = 'scalar';
                    }
                    break;
                    
                case 'object':
                    if (!is_object($value)) {
                        $errors[] = 'object';
                    }
                    break;
                    
                case 'array':
                    if (!is_array($value)) {
                        $errors[] = 'array';
                    }
                    break;
                    
                default:
                    if (!is_a($value, $type)) {
                        $errors[] = $type;
                    }
                    break;
            }
        }
        
        return $errors;
    }
}