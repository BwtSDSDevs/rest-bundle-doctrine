<?php

namespace Niebvelungen\RestBundleDoctrine\Service;

use Niebvelungen\RestBundleDoctrine\Metadata\RestMetadataFactory;

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
