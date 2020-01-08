# Laminas Auto-Wire Factory

[![Latest Stable Version](https://poser.pugx.org/bluepsyduck/laminas-autowire-factory/v/stable)](https://packagist.org/packages/bluepsyduck/laminas-autowire-factory) 
[![License](https://poser.pugx.org/bluepsyduck/laminas-autowire-factory/license)](https://packagist.org/packages/bluepsyduck/laminas-autowire-factory) 
[![Build Status](https://travis-ci.com/BluePsyduck/laminas-autowire-factory.svg?branch=master)](https://travis-ci.com/BluePsyduck/laminas-autowire-factory) 
[![codecov](https://codecov.io/gh/bluepsyduck/laminas-autowire-factory/branch/master/graph/badge.svg)](https://codecov.io/gh/bluepsyduck/laminas-autowire-factory)

This library provides few factories helping with auto-wiring service classes to make writing actual factories less
common. 

## AutoWireFactory

The `AutoWireFactory` uses reflection on the constructor of the actual service class to determine how to resolve the
dependencies and creating the actual service. The factory is adopting 
[Symfony's approach](https://symfony.com/doc/current/service_container/autowiring.html) of handling auto wiring,
especially [dealing with multiple implementations of the same type](https://symfony.com/doc/current/service_container/autowiring.html#dealing-with-multiple-implementations-of-the-same-type).

### Resolving strategies

The factory uses the following strategies to resolve a parameter of the constructor, depending on how it is type-hinted.
The first alias available in the container will be used to resolve the dependency. If no alias is available, an 
exception gets triggered.

Each parameter is resolved on its own, so they can be combined in any way.

#### Parameter with class type-hint

Example: ```__construct(FancyClass $fancy)```

If the parameter has a class name as type-hint, then the following aliases are checked in the container:

1. `FancyClass $fancy`: The combination of class name and parameter name. This allows for multiple implementations of
   the same interface as stated in the Symphony documentation.
2. `FancyClass`: "Default" case of registering a class with its name to the container.
3. `$fancy`: Fallback of using the parameter name alone, mostly to make the aliases uniform between cases.

The first alias which can be provided by the container will be used.

#### Parameter with scalar type-hint

Example: ```__construct(array $fancyConfig)```

If the parameter is type-hinted with a scalar type, e.g. to pull config values into the service, the following aliases
are checked:

1. `array $fancyConfig`: The combination of type and parameter name, the same as for class type-hints.
2. `$fancyConfig`: Fallback using only the parameter name. 

Note that the type alone, `array`, is not used as alias.

#### Parameter without type-hint

Example: ```__construct($fancyParameter)```

In this case, only one alias can be checked due to missing information:

1. `$fancyParameter`: Fallback is the only possible alias. 

### AutoWireFactory as AbstractFactory

Next to the `FactoryInterface` to use the `AutoWireFactory`as an explicit factory in the container configuration,
it also implements the `AbstractFactoryInterface`: If you add this factory as an abstract factory, it will try
to auto-wire everything it can. This will make configuring the container mostly obsolete, with the exception of 
parameters using scalar values or multiple implementations (where the parameter name is part of the container alias).

### Caching

The `AutoWireFactory` uses reflections to resolve dependencies. To make things faster, the factory offers building up
a cache on the filesystem to avoid using reflections on each script call. To enable the cache, add the following line
e.g. in the `config/container.php` file:

```php
\BluePsyduck\LaminasAutoWireFactory\AutoWireFactory::setCacheFile('data/cache/autowire-factory.cache.php');
```

## ConfigReaderFactory

To help further with making self-written factories obsolete, the `ConfigReaderFactory` is able to provide values from
the application config to the container to be e.g. used together with auto-wiring.

### Usage

The `ConfigReaderFactory` requires the application config to be added as array to the container. If the alias for the
config differs from the default "config", call `ConfigReaderFactory::setConfigAlias('yourAlias')` to set the
alias.

Then, use the `readConfig(string ...$keys)` function (or `new ConfigReaderFactory(string ...$keys)`) to read a config 
value for the container, where `$keys` are the array keys to reach your desired value in the config. Note that if a key 
is not set, an exception will be triggered by the factory.

## AliasArrayInjectorFactory

The `AliasArrayInjectorFactory` reads an array of aliases from the config (using the `ConfigReaderFactory`), and creates
all instances to these aliases and returns them to be injected into other services. All aliases must be known to the 
container.

To use this factory, simply call `injectAliasArray(string ...$configKeys)` (or 
`new AliasArrayInjectorFactory(string ...$configKeys)`) within the container config. 

## Example

The following example should show how to use both the `AutoWireFactory` and the `ConfigReaderFactory` to auto-wire a
service class.

Let's assume we have the following application config from which we want to take a value:

```php
[
    'fancy-service' => [
        'fancy-property' => 'Hello World!',
        'fancy-adapters' => [
            FancyAdapterAlpha::class,
            FancyAdapterOmega::class,
        ],
    ],
]
``` 

We want to auto-wire the following service class:

```php
class FancyService {
    public function __construct(FancyComponent $component, string $fancyProperty, array $fancyAdapters) {
    }
}

class FancyComponent {}
class FancyAdapterAlpha {}
class FancyAdapterOmega {}
```

The following configuration can be used for the container without writing any factories:

```php
<?php 

use BluePsyduck\LaminasAutoWireFactory\AutoWireFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;
use function BluePsyduck\LaminasAutoWireFactory\injectAliasArray;
use function BluePsyduck\LaminasAutoWireFactory\readConfig;

return [
    'dependencies' => [
        'factories' => [
            // Enable auto-wiring for the service itself.
            FancyService::class => AutoWireFactory::class,
            
            // FancyComponent does not need any factory as it does not have a constructor.
            // Both InvokableFactory and AutoWireFactory are usable here.
            FancyComponent::class => InvokableFactory::class,
            FancyAdapterAlpha::class => InvokableFactory::class,
            FancyAdapterOmega::class => InvokableFactory::class,
            
            // Enable the scalar property for auto-wiring into the service.
            // In this example, the factory would fetch "Hello World!" from the config.
            'string $fancyProperty' => readConfig('fancy-service', 'fancy-property'),
            
            // Inject an array of other services through their aliases into the service.
            // In this example, instances of FancyAdapterAlpha and FancyAdapterOmega would be injected. 
            'array $fancyAdapters' => injectAliasArray('fancy-service', 'fancy-adapters'),
        ],
    ],
];
```

This configuration can be made even shorter if we use the `AutoWireFactory` as an abstract factory:

```php
<?php 

use BluePsyduck\LaminasAutoWireFactory\AutoWireFactory;
use function BluePsyduck\LaminasAutoWireFactory\injectAliasArray;
use function BluePsyduck\LaminasAutoWireFactory\readConfig;

return [
    'dependencies' => [
        'abstract_factories' => [
            // Will auto-wire everything possible to be auto-wired, in our case both FancyService and FancyComponent.
            AutoWireFactory::class,
        ],
        'factories' => [
            // Any aliases using property names cannot be handled by the AutoWireFactory and must still get listed.
            'string $fancyProperty' => readConfig('fancy-service', 'fancy-property'),
            'array $fancyAdapters' => injectAliasArray('fancy-service', 'fancy-adapters'),
        ],
    ],
];
```

Of course it is always possible to add a concrete factory to any service if auto-wiring is not possible due to more 
complex initialization requirements.
