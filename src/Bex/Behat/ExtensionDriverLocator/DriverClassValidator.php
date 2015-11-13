<?php

namespace Bex\Behat\ExtensionDriverLocator;

use Bex\Behat\ExtensionDriverLocator\DriverInterface;

class DriverClassValidator
{
    /**
     * @var string
     */
    private $parent;

    /**
     * @param string $parent
     */
    public function __construct($parent = '')
    {
        $this->parent = $parent;
    }

    /**
     * @param  string  $className
     *
     * @return boolean
     */
    public function isValidDriverClass($className)
    {
        if (!class_exists($className)) {
            return false;
        }

        if (!is_subclass_of($className, 'Bex\\Behat\\ExtensionDriverLocator\\DriverInterface')) {
            return false;
        }

        if (!empty($this->parent) && !is_subclass_of($className, $this->parent)) {
            return false;
        }

        return true;
    }
}