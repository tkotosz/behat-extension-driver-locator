Behat-ExtensionDriverLocator
=========================
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/tkotosz/behat-extension-driver-locator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/tkotosz/behat-extension-driver-locator/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/tkotosz/behat-extension-driver-locator/badges/build.png?b=master)](https://scrutinizer-ci.com/g/tkotosz/behat-extension-driver-locator/build-status/master)
[![Build Status](https://travis-ci.org/tkotosz/behat-extension-driver-locator.svg?branch=master)](https://travis-ci.org/tkotosz/behat-extension-driver-locator)

Behat-ExtensionDriverLocator helps you load external drivers/services (like image uploaders, output formatters, etc) dinamically.
The DriverLocator can find you a service in a preconfigured namespace by a given driverkey.
- It validates that the class implements the DriverInterface or your specific interface.
- It will call the configure method of the driver to get the config tree of the driver specific configurations
- It will validate the loaded config against the provided config tree.
- It will pass the valid config and the DI container to the load method of the driver in order to get a properly loaded service.
The package also provide a Driver Node Builder which can create the drivers node for your behat extension. (see usage below)

Installation
------------

Install by adding to your `composer.json`:

```bash
composer require --dev bex/behat-extension-driver-locator
```

Usage
-----

1. In your behat extension's configure method use the Driver Node Builder to build the drivers configuration node:

    ```php
        $driverNodeBuilder = DriverNodeBuilder::getInstance($driverNamespace, $driverParent);
        $driverNodeBuilder->buildDriverNodes($builder, $activeDriversNodeName, $driversCofigurationNodeName, $defaultDriverKeys);
    ```
    
    where:
    - the `$driverNamespace` is the namespace where the DriverNodeBuilder should look for the drivers when validating a given driver key
      e.g.: `My\\Awesome\\BehatExtension\\Driver`
    - the `$driverParent` is the parent class/interface which should be implemented by all driver
      e.g.: `My\\Awesome\\BehatExtension\\Driver\\MyAwesomeDriverInterface`
      (note that all driver need to implement the `Bex\Behat\ExtensionDriverLocator\DriverInterface`)
    - the `$builder` is an `Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition` instance which you get in your behat extension's configure method as a parameter, the DriverNodeBuilder will add the drivers node to this builder.
    - the `$activeDriversNodeName` is the name of the node where the user will be able to specify which driver should be used for your extension
      e.g.: `active_my_awesome_drivers`
    - the `driversCofigurationNodeName` is the name of the drivers node, the additional configuration of all driver will be under this node
      e.g.: `my_awesome_drivers`
    - the `$defaultDriverKeys` is the driverkey of the default driver(s) which will be used when the config is empty in the behat.yml
      e.g.: `first_awesomeness`
    - Note: The driverkey is always the lowercased and underlined version of the driver's classname.
      e.g. FirstAwesomeness -> first_awesomeness
      e.g. First -> first
    
    With the example configurations a valid config would look like this:
    ```yml
    default:
      extensions:
       My\\Awesome\\BehatExtension: ~
    ```
    or
    ```yml
    default:
      extensions:
       My\\Awesome\\BehatExtension:
         active_my_awesome_drivers: first_awesomeness
         my_awesome_drivers:
           first_awesomeness:
             # ... all driver specific configuration goes here ...
    ```

1. In your behat extension's load method use the Driver Locator to load the active driver(s):
Note that it will validate the driver specific configs automatically.

    ```php
        $driverLocator = DriverLocator::getInstance($driverNamespace, $driverParent);
        $drivers = $driverLocator->findDrivers($container, $activeDrivers, $driverConfigs);
    ```
    where:
    - the `$driverNamespace` is the namespace where the DriverLocator should look for the drivers
      e.g.: `My\\Awesome\\BehatExtension\\Driver`
    - the `$driverParent` is the parent class/interface which should be implemented by all driver
      e.g.: `My\\Awesome\\BehatExtension\\Driver\\MyAwesomeDriverInterface`
    - the `$container` is an `Symfony\Component\DependencyInjection\ContainerBuilder` instance which you get in your behat extension's load method as a parameter, the DriverLocator will pass this container to the load method of each driver
    - the `$activeDrivers` are the active image drivers from the $config param which you get in the load method
      e.g.: $config['active_my_awesome_drivers']
    - the `$driverConfigs` are the driver specific configuration values from the $config param
      e.g.: $config['my_awesome_drivers']
