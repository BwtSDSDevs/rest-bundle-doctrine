<?php

namespace Dontdrinkandroot\RestBundle\Service;

use Dontdrinkandroot\RestBundle\Metadata\RestMetadataFactory;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class RestResourceRegistry
{
    private $resourceClasses = [];

    /**
     * @var RestMetadataFactory
     */
    private $metadataFactory;

    public function __construct(RestMetadataFactory $metadataFactory, array $directories)
    {
        $this->metadataFactory = $metadataFactory;
    }
}
