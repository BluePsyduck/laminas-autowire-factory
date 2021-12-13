# Laminas Auto-Wire Factory

[![GitHub release (latest SemVer)](https://img.shields.io/github/v/release/BluePsyduck/laminas-autowire-factory)](https://github.com/BluePsyduck/laminas-autowire-factory/releases)
[![GitHub](https://img.shields.io/github/license/BluePsyduck/laminas-autowire-factory)](LICENSE.md)
[![build](https://img.shields.io/github/workflow/status/BluePsyduck/laminas-autowire-factory/CI?logo=github)](https://github.com/BluePsyduck/laminas-autowire-factory/actions)
[![Codecov](https://img.shields.io/codecov/c/gh/BluePsyduck/laminas-autowire-factory?logo=codecov)](https://codecov.io/gh/BluePsyduck/laminas-autowire-factory)

This library provides few factories and attributes helping with auto-wiring service classes for the 
[Laminas ServiceManager](https://github.com/laminas/laminas-servicemanager/), to make writing actual factories less 
common. 

## Revolver strategies

The library provides several strategies to resolve the parameters of a class. All have in common that only constructor
parameters are resolved, and never the properties directly. Each parameter is resolved on its own, so one parameter
using a certain strategy does not influence other parameters.

The resolving strategy is specified by providing an attribute on the parameter of the constructor. If no resolving 
attribute is specified, the AutoWireFactory will use the default strategy for resolving.

### Default strategy

If no other strategy is specified, then the AutoWireFactory will use the default strategy, trying to derive the service
from the parameter types and names.

The default strategy is adopting [Symfony's approach](https://symfony.com/doc/current/service_container/autowiring.html) 
of handling auto wiring, especially [dealing with multiple implementations of the same type](https://symfony.com/doc/current/service_container/autowiring.html#dealing-with-multiple-implementations-of-the-same-type).

The following cases can be handled by the default strategy:

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

### Resolve by alias

The parameter is resolved by specifying the exact alias to request from the container. This is done by using the 
`Alias` attribute:

```php
use BluePsyduck\LaminasAutoWireFactory\Attribute\Alias;

class ClassWithAliasParameter {
    public function __construct(
        #[Alias('alias-for-fancy-class')]
        private FancyClass $fancy,
    ) {}
}
```

In this case, the AutoWireFactory will use the service registered as "alias-for-fancy-class" from the container to
resolve `$fancy`.

### Use a scalar value from the config

The AutoWireFactory is also able to inject a value from the application config to the service, using the `ReadConfig`
attribute. The attribute expects the config keys.

```php
use BluePsyduck\LaminasAutoWireFactory\Attribute\ReadConfig;

class ClassWithConfigValue {
    public function __construct(
        #[ReadConfig('foo', 'bar')]
        private string $timeout,
    ) {}
}
```

In this case, the value of `$config['foo']['bar']` is injected into the service. Pay attention that the types match.

As default, the resolver uses the alias `config` to fetch the application config from the container. If your config is 
available through another alias, set the alias to use via `ReadConfig::$configAlias = 'fancy-config'`. All config-bases
resolvers will use this alias.

### Inject an array of services by their aliases

There may be the case where the config specifies a list of aliases, of which the corresponding services are needed in 
the service. For this, the `InjectAliasArray` attribute can be used. Again, the attribute expects the config keys to 
read the aliases from.

```php
use BluePsyduck\LaminasAutoWireFactory\Attribute\InjectAliasArray;

/*
 
Config: [
    'resolvers' => [
        FancyResolver::class,
        NotSoFancyResolver::class,
    ],
]

 */

class ClassWithAliasArray {
    public function __construct(
        #[InjectAliasArray('resolvers')]
        private array $resolvers,
    ) {}
}
```

In this example, the resolver will read the aliases provided in the "resolvers" config key, and will request the
services from the container using these aliases, here the class names of the `FancyResolver` and `NotSoFancyResolver`.
The resulting array of services is then passed to the service as `$resolvers`.

## AutoWireFactory

The `AutoWireFactory` uses reflection on the constructor of the actual service class to determine how to resolve the
dependencies and creating the actual service. It will check for any of the attributes mentioned above to select the 
strategy, or will fall back to the default strategy if no attribute can be found. 

### AutoWireFactory as AbstractFactory

Next to the `FactoryInterface` to use the `AutoWireFactory`as an explicit factory in the container configuration,
the AutoWireFactory also implements the `AbstractFactoryInterface`: If you add this factory as an abstract factory, 
it will try to auto-wire everything it can. This will make configuring the container mostly obsolete, except for
services which still need custom factories.

### Caching

The `AutoWireFactory` uses reflections to resolve dependencies. To make things faster, the factory offers building up
a cache on the filesystem to avoid using reflections on each script call. To enable the cache, add the following line
e.g. in the `config/container.php` file:

```php
\BluePsyduck\LaminasAutoWireFactory\AutoWireFactory::setCacheFile('data/cache/autowire-factory.cache');
```

## Additional Factories

The library provides additional factories with which you can specify how certain parameters should be resolved. All
these factories have the same functionality as their corresponding attribute mentioned above. The factories are 
intended to be used directly in the container configuration, instead of using an attribute on the constructor. Note 
though that the attributes will be preferred, as the additional configs are only used by the default strategy (when no
attribute is present).

### ConfigReaderFactory

This factory can be used instead of the `ReadConfig` attribute, taking again the config keys to read the value from.

There are two ways to use the factory:

```php
// dependencies.php

use BluePsyduck\LaminasAutoWireFactory\Factory\ConfigReaderFactory;
use BluePsyduck\LaminasAutoWireFactory\AutoWireUtils;

    'factories' => [
        // Either instantiate the factory directly:
        'int $timeout' => new ConfigReaderFactory('fancy-service', 'timeout'),
        // Or use the Utils class instead:
        'int $timeout' => AutoWireUtils::readConfig('fancy-service', 'timeout'),
    ]
```

In both cases, the `$config['fancy-service']['timeout']` would be registered to the container to be available for the
default resolving strategy.

### AliasArrayInjectorFactory

This factory can be used instead of the `InjectAliasArray` attribute, taking again the config keys to read the aliases
from.

There are again two ways to use the factory:

```php
// dependencies.php

use BluePsyduck\LaminasAutoWireFactory\Factory\AliasArrayInjectorFactory;
use BluePsyduck\LaminasAutoWireFactory\AutoWireUtils;

    'factories' => [
        // Either instantiate the factory directly:
        'array $resolvers' => new AliasArrayInjectorFactory('resolvers'),
        // Or use the Utils class instead:
        'array $resolvers' => AutoWireUtils::injectAliasArray('resolvers'),
    ]
```

In both cases, the `$config['resolvers']` is used to as aliases for the container, for which the received instances can
be used in the default resolving strategy. 

## Examples

To help better understand of how the AutoWireFactory works, two full examples shall be given. Both examples do the same
thing, with the first one using attributes, and the second one using the additional factories. It is up to you to
decide which variant you want to use.

While the examples use constructor property promotion to specify the properties and parameters at the same time, all
features also work on non-promoted parameters as well.

### Example 1: Using Attributes

The following example shows how to use the `AutoWireFactory` and the attributes to auto-wire a service class.

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
use BluePsyduck\LaminasAutoWireFactory\Attribute\ReadConfig;
use BluePsyduck\LaminasAutoWireFactory\Attribute\InjectAliasArray;

class FancyService {
    public function __construct(
        private FancyComponent $component,
        #[ReadConfig('fancy-service', 'fancy-property')]
        private string $fancyProperty, 
        #[InjectAliasArray('fancy-service', 'fancy-adapters')]
        private array $fancyAdapters,
    ) {}
}

class FancyComponent {}
class FancyAdapterAlpha {}
class FancyAdapterOmega {}
```

The first parameter of the constructor does not have an attribute specified, so the default type-based resolving 
strategy is used. For the other two parameters, an attribute is specified, so those will be resolved accordingly.

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
            
            // FancyComponent and the other classes do not need any factory as they do not have a constructor.
            // Both InvokableFactory and AutoWireFactory are usable here.
            FancyComponent::class => InvokableFactory::class,
            FancyAdapterAlpha::class => InvokableFactory::class,
            FancyAdapterOmega::class => InvokableFactory::class,
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
            // Will auto-wire everything possible to be auto-wired, in our case the FancyService, FancyComponent,
            // and the adapters.
            AutoWireFactory::class,
        ],
    ],
];
```

### Example 2: Using Additional Factories

The following example shows how to use both the `AutoWireFactory` and the `ConfigReaderFactory` to auto-wire a service
class.

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
    public function __construct(
        private FancyComponent $component,
        private string $fancyProperty,
        private array $fancyAdapters
     ) {}
}

class FancyComponent {}
class FancyAdapterAlpha {}
class FancyAdapterOmega {}
```

The FancyService does not have any attributes specified on the constructor, meaning that the default type-based 
resolving strategy is used for all of its parameters. 

The following configuration can be used for the container without writing any factories:

```php
<?php 

use BluePsyduck\LaminasAutoWireFactory\AutoWireFactory;
use BluePsyduck\LaminasAutoWireFactory\AutoWireUtils;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'dependencies' => [
        'factories' => [
            // Enable auto-wiring for the service itself.
            FancyService::class => AutoWireFactory::class,
            
            // FancyComponent and the other classes do not need any factory as they do not have a constructor.
            // Both InvokableFactory and AutoWireFactory are usable here.
            FancyComponent::class => InvokableFactory::class,
            FancyAdapterAlpha::class => InvokableFactory::class,
            FancyAdapterOmega::class => InvokableFactory::class,
            
            // Enable the scalar property for auto-wiring into the service.
            // In this example, the factory would fetch "Hello World!" from the config.
            'string $fancyProperty' => AutoWireUtils::readConfig('fancy-service', 'fancy-property'),
            
            // Inject an array of other services through their aliases into the service.
            // In this example, instances of FancyAdapterAlpha and FancyAdapterOmega would be injected. 
            'array $fancyAdapters' => AutoWireUtils::injectAliasArray('fancy-service', 'fancy-adapters'),
        ],
    ],
];
```

This configuration can be made even shorter if we use the `AutoWireFactory` as an abstract factory:

```php
<?php 

use BluePsyduck\LaminasAutoWireFactory\AutoWireFactory;
use BluePsyduck\LaminasAutoWireFactory\AutoWireUtils;

return [
    'dependencies' => [
        'abstract_factories' => [
            // Will auto-wire everything possible to be auto-wired, in our case the FancyService, FancyComponent,
            // and the adapters.
            AutoWireFactory::class,
        ],
        'factories' => [
            // Any additional factories must still be specified in the config to make the corresponding parameters
            // resolvable by the AutoWireFactory.
            // Any aliases using property names cannot be handled by the AutoWireFactory and must still get listed.
            'string $fancyProperty' => AutoWireUtils::readConfig('fancy-service', 'fancy-property'),
            'array $fancyAdapters' => AutoWireUtils::injectAliasArray('fancy-service', 'fancy-adapters'),
        ],
    ],
];
```
