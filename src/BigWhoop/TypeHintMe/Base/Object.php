<?php
namespace BigWhoop\TypeHintMe\Base;
use BigWhoop\TypeHintMe\Options;

class Object
{
    /**
     * @var array
     */
    protected $_options = array();
    
    
    /**
     * __constructor
     * 
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }
    
    
    /**
     * Merge and set the options
     * 
     * @param array $options
     * @return BigWhoop\TypeHintMe\Base\Object
     */
    public function setOptions(array $options)
    {
        $this->_options = Options::merge($this->_options, $options);
        return $this;
    }
}