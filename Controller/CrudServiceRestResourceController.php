<?php

namespace Dontdrinkandroot\RestBundle\Controller;

use Dontdrinkandroot\RestBundle\Metadata\RestMetadataFactory;
use Dontdrinkandroot\RestBundle\Service\Normalizer;
use Dontdrinkandroot\RestBundle\Service\RestRequestParserInterface;
use Dontdrinkandroot\Service\CrudServiceInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CrudServiceRestResourceController extends AbstractCrudServiceRestResourceController
{
    private $service;

    public function __construct(
        RestRequestParserInterface $requestParser,
        ValidatorInterface $validator,
        RequestStack $requestStack,
        RestMetadataFactory $metadataFactory,
        PropertyAccessorInterface $propertyAccessor,
        CrudServiceInterface $service,
        SerializerInterface $serializer
    ) {
        parent::__construct(
            $requestParser,
            $validator,
            $requestStack,
            $metadataFactory,
            $propertyAccessor,
            $serializer
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
