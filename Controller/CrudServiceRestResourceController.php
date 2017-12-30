<?php

namespace Dontdrinkandroot\RestBundle\Controller;

use Dontdrinkandroot\RestBundle\Service\Normalizer;
use Dontdrinkandroot\RestBundle\Service\RestRequestParserInterface;
use Dontdrinkandroot\Service\CrudServiceInterface;
use Metadata\MetadataFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CrudServiceRestResourceController extends AbstractCrudServiceRestResourceController
{
    private $service;

    public function __construct(
        RestRequestParserInterface $requestParser,
        Normalizer $normalizer,
        ValidatorInterface $validator,
        RequestStack $requestStack,
        MetadataFactoryInterface $metadataFactory,
        PropertyAccessorInterface $propertyAccessor,
        CrudServiceInterface $service
    ) {
        parent::__construct(
            $requestParser,
            $normalizer,
            $validator,
            $requestStack,
            $metadataFactory,
            $propertyAccessor
        );
        $this->service = $service;
    }

    /**
     * @return CrudServiceInterface
     */
    protected function getService()
    {
        return $this->service;
    }
}
