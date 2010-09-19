<?php
namespace BigWhoop\TypeHintMe;

class DocBlockValidator extends Base\Object
{
    protected $_docBlock = null;
    protected $_typeHints = array();
    
    
    /**
     * __construct
     * 
     * @param string $docBlock
     * @param array $options
     */
    public function __construct($docBlock, array $options = array())
    {
        $this->_docBlock  = $docBlock;
        $this->_typeHints = $this->_extractTypeHints();
        
        parent::__construct($options);
    }
    
    
    /**
     * Validate a set of params for this doc block
     * 
     * Return value is an array containing a key for each checked argument
     * with a boolean value which indicates whether the validation was
     * successful.
     * 
     *  array(
     *    '1' => true,
     *    '2' => false
     *  )
     * 
     * @param array $params
     * @return array
     */
    public function validate(array $params)
    {
        $result = array();
        
        foreach ($params as $idx => $value) {
            $argIdx = $idx + 1;
            
            $types = $this->getTypesForArgument($argIdx);
            
            // If argument is just out of range that's not a real
            // validation error. It's missing, but that's not what
            // we want to check.
            if (empty($types)) {
                $result[$argIdx] = true;
                continue;
            }
            
            $result[$argIdx] = $this->validateValue($value, $types);
        }
        
        return $result;
    }
    
    
    public function getTypesForArgument($argumentIndex)
    {
        $idx = $argumentIndex - 1;
        
        if (!array_key_exists($idx, $this->_typeHints)) {
            return array();
        }
        
        return $this->_typeHints[$idx];
    }
    
    
    /**
     * Check a value for one or more specific data type(s).
     * 
     * @param mixed $value
     * @param array $types
     * @return bool
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
        
        return count($errors) != count($types);
    }
    
    
    protected function _extractTypeHints()
    {
        $words = preg_split('/\s/', $this->_docBlock, null, PREG_SPLIT_NO_EMPTY);
        
        $typeHints = array();
        for ($c = count($words), $i = 1; $i < $c; $i++) {
            if ('@param' == $words[$i - 1]) {
                $typeHints[] = explode('|', $words[$i]);
            }
        }
        
        return $typeHints;
    }
}