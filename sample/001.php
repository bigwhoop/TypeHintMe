<?php
namespace App;

require __DIR__ . '/../build/build.php';
use BigWhoop\TypeHintMe;


class User
{
    /**
     * @param string $forename
     * @param string $surname
     */
    public function __construct($forename, $surname)
    {
        $this->_forename = $forename;
        $this->_surname  = $surname;
    }
    

    /**
     * @param string $a
     */
    function format($format)
    {
        if (null === $format) {
            $format = '%s %s';
        }
        
        return sprintf($format, $this->_forename, $this->_surname);
    }
}

TypeHintMe\ObjectProxy::setDefaultOptions(array(
    'throwExceptions' => false,
    'errorLevel' => E_USER_WARNING,
));

// No warnings
$user1 = new User('Philippe', 123);
echo $user1->format(null);

// Warnings
$user2 = TypeHintMe\ObjectProxyFactory::create('App\User', array('Philippe', 123));
echo $user2->format(null);