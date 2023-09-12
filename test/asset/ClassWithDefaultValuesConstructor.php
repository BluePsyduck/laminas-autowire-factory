<?php

declare(strict_types=1);

namespace BluePsyduckTestAsset\LaminasAutoWireFactory;

/**
 * @author richard.weinhold
 * @since 12.09.2023
 */
class ClassWithDefaultValuesConstructor
{
    /** @param string[] $defaultArray */
    public function __construct(
        public ClassWithoutConstructor $foo,
        public string $defaultString = 'default-string',
        public array $defaultArray = ['default-array', '1234'],
    ) {
    }
}
