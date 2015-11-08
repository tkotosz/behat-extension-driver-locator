<?php

namespace Bex\Behat\ExtensionDriverLocator;

use Bex\Behat\ExtensionDriverLocator\DriverClassValidator;
use Symfony\Component\DependencyInjection\Container as DIContainer;

class DriverClassNameResolver
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var DriverClassValidator
     */
    private $classValidator;

    /**
     * @param string               $namespace
     * @param DriverClassValidator $classValidator
     */
    public function __construct($namespace, DriverClassValidator $classValidator)
    {
        $this->namespace = $namespace;
        $this->classValidator = $classValidator;
    }

    /**
     * @param  string $driverKey
     *
     * @return string
     */
    public function getClassNameByDriverKey($driverKey)
    {
        $driverClass = $this->namespace . '\\' . ucfirst(DIContainer::camelize($driverKey));
        
        if (!$this->classValidator->isValidDriverClass($driverClass)) {
            throw new \Exception(sprintf('Driver %s was not found in %s', $driverKey, $driverClass));
        }

        return $driverClass;
    }
}