<?php

namespace Bex\Behat\ExtensionDriverLocator;

use Bex\Behat\ExtensionDriverLocator\DriverClassNameResolver;
use Bex\Behat\ExtensionDriverLocator\DriverClassValidator;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class DriverNodeBuilder
{
    /**
     * @var DriverClassNameResolver
     */
    private $driverClassNameResolver;

    /**
     * @param DriverClassNameResolver $driverClassNameResolver
     */
    public function __construct(DriverClassNameResolver $driverClassNameResolver)
    {
        $this->driverClassNameResolver = $driverClassNameResolver;
    }

    /**
     * @param  string $namespace
     * @param  string $parent
     *
     * @return NodeBuilder
     */
    public static function getInstance($namespace, $parent = '')
    {
        return new self(new DriverClassNameResolver($namespace, new DriverClassValidator($parent)));
    }

    /**
     * @param  ArrayNodeDefinition $builder
     * @param  string              $activeDriversNodeName
     * @param  string              $driversNodeName
     * @param  array               $defaultActiveDrivers
     *
     * @return void
     */
    public function buildDriverNodes(
        ArrayNodeDefinition $builder,
        $activeDriversNodeName,
        $driversNodeName,
        $defaultActiveDrivers
    ) {
        $builder
            ->children()
                ->arrayNode($activeDriversNodeName)
                    ->defaultValue($defaultActiveDrivers)
                    ->beforeNormalization()
                        ->ifString()
                        ->then($this->getDefaultValueInitializer())
                    ->end()
                    ->validate()
                        ->ifTrue($this->getDriverKeyValidator())
                        ->thenInvalid('%s')
                    ->end()
                    ->prototype('scalar')->end()
                ->end()
            ->end()
            ->fixXmlConfig($driversNodeName . '_child', $driversNodeName)
            ->children()
                ->arrayNode($driversNodeName)
                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @return \Closure
     */
    private function getDefaultValueInitializer()
    {
        return function ($value) {
            return [$value];
        };
    }

    /**
     * @return \Closure
     */
    private function getDriverKeyValidator()
    {
        $classNameResolver = $this->driverClassNameResolver;
        
        return function ($driverKeys) use ($classNameResolver) {
            foreach ($driverKeys as $driverKey) {
                $classNameResolver->getClassNameByDriverKey($driverKey);
            }

            return false;
        };
    }
}