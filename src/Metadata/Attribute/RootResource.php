<?php

namespace Niebvelungen\RestBundleDoctrine\Metadata\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class RootResource
{
    /**
     * @param Operation[] $operations
     */
    public function __construct(
        public array $operations,
        public string $idField = 'id',
        public ?string $pathPrefix = null,
        public ?string $namePrefix = null,
        public ?string $controller = null,
    ) {
    }
}
