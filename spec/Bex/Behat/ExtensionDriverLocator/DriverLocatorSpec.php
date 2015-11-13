<?php

namespace spec\Bex\Behat\ExtensionDriverLocator;

use Bex\Behat\ExtensionDriverLocator\DriverClassNameResolver;
use PhpSpec\ObjectBehavior;

class DriverLocatorSpec extends ObjectBehavior
{
    function let(DriverClassNameResolver $classNameResolver)
    {
        $this->beConstructedWith($classNameResolver);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Bex\Behat\ExtensionDriverLocator\DriverLocator');
    }
}