# Changelog

## 2.1.0 - 2023-10-03

### Added

- Support for using the default values of parameters in the constructors (thanks to @ricwein).

## 2.0.0 - 2021-12-13

- Re-implemented the library to allow using attributes to resolve parameters of the service classes.

### Added

- Attribute `ReadConfig` to be used in replacement of the `ConfigReaderFactory`.
- Attribute `InjectAliasArray` to be used in replacement of the `AliasArrayInjectorFactory`.
- Attribute `Alias` to use a custom alias instead of the default type-based one.

### Changed

- **[BC Break]**: Moved factories `ConfigReaderFactory` and `AliasArrayInjectorFactory` from the namespace
  `BluePsyduck\LaminasAutoWireFactory` to `BluePsyduck\LaminasAutoWireFactory\Factory`.
- **[BC Break]**: Moved the helper functions `readConfig()` and `injectAliasArray()` to a static class `AutoWireUtils`, 
  making them to `AutoWireUtils::readConfig()` and `AutoWireUtils::injectAliasArray()` respectively, removing the need
  to always load the `helpers.php` file.
- The cache file now uses the `serialize()` format of PHP instead of the `var_export()` format. Old cache files are 
  incompatible, but will be ignored and rewritten without errors.

### Removed

- Support for PHP 7.4. The minimal required version is now PHP 8.0.

## 1.1.1 - 2021-02-07

### Fixed

- Accidental hard dependency on PHP 8.0.0. Now it is ^8.0, as it should be.

## 1.1.0 - 2021-01-12

### Added

- Support for PHP 8.0.

### Removed

- Support for PHP 7.1, 7.2 and 7.3. Minimal required PHP version is now 7.4.

## 1.0.0 - 2020-01-08

- Initial version, migrated from bluepsyduck/zend-autowire-factory v1.1.0. 
