<?php

namespace Bex\Behat\ExtensionDriverLocator;

use Bex\Behat\ExtensionDriverLocator\DriverClassNameResolver;
use Bex\Behat\ExtensionDriverLocator\DriverClassValidator;
use Bex\Behat\ExtensionDriverLocator\DriverInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DriverLocator
{
    /**
     * @var DriverClassNameResolver
     */
    private $classNameResolver;

    /**
     * @var DriverInterface[]
     */
    private $drivers = [];

    /**
     * @param DriverClassNameResolver $classNameResolver
     */
    public function __construct(DriverClassNameResolver $classNameResolver)
    {
        $this->classNameResolver = $classNameResolver;
    }

    /**
     * @param  string $namespace
     * @param  string $parent
     *
     * @return Locator
     */
    public static function getInstance($namespace, $parent = '')
    {
        return new self(new DriverClassNameResolver($namespace, new DriverClassValidator($parent)));
    }

    /**
     * @param  ContainerBuilder $container
     * @param  array            $activeDrivers
     * @param  array            $driverConfigs
     *
     * @return DriverInterface[]
     */
    public function findDrivers(ContainerBuilder $container, array $activeDrivers, array $driverConfigs)
    {
        $driverConfigs = $this->removeUnusedDrivers($activeDrivers, $driverConfigs);
        $this->createDrivers($activeDrivers);
        $configTree = $this->configureDrivers();
        $driverConfigs = $this->processDriverConfiguration($configTree, $driverConfigs);
        $this->loadDrivers($container, $driverConfigs);

        return $this->drivers;
    }

    /**
     * @return DriverInterface[]
     */
    public function getDrivers()
    {
        return $this->drivers;
    }

    /**
     * @param  array $activeDrivers
     * @param  array $driverConfigs
     *
     * @return array
     */
    private function removeUnusedDrivers($activeDrivers, $driverConfigs)
    {
        foreach ($driverConfigs as $driverKey => $driverConfig) {
            if (!in_array($driverKey, $activeDrivers)) {
                unset($driverConfigs[$driverKey]);
            }
        }

        return $driverConfigs;
    }

    /**
     * @param array $driverKeys 
     *
     * @return DriverInterface[]
     */
    private function createDrivers($driverKeys)
    {
        $this->drivers = [];

        foreach ($driverKeys as $driverKey) {
            $driverClass = $this->classNameResolver->getClassNameByDriverKey($driverKey);
            $this->drivers[$driverKey] = new $driverClass();
        }

        return $this->drivers;
    }

    /**
     * @return NodeInterface
     */
    private function configureDrivers()
    {
        $tree = new TreeBuilder('drivers');

        foreach ($this->drivers as $driverKey => $driver) {
            $driver->configure($tree->getRootNode()->children()->arrayNode($driverKey));
        }

        return $tree->buildTree();
    }

    /**
     * @param  NodeInterface $configTree
     * @param  array         $configs
     *
     * @return array The processed configuration
     */
    private function processDriverConfiguration(NodeInterface $configTree, array $configs)
    {
        $configProcessor = new Processor();

        foreach ($this->drivers as $driverKey => $driver) {
            $configs[$driverKey] = isset($configs[$driverKey]) ? $configs[$driverKey] : [];
        }

        return $configProcessor->process($configTree, ['drivers' => $configs]);
    }

    /**
     * @param  ContainerBuilder $container
     * @param  array            $driverConfigs
     *
     * @return DriverInterface[]
     */
    private function loadDrivers(ContainerBuilder $container, array $driverConfigs)
    {
        foreach ($this->drivers as $driverKey => $driver) {
            $driver->load($container, $driverConfigs[$driverKey]);
        }

        return $this->drivers;
    }
}