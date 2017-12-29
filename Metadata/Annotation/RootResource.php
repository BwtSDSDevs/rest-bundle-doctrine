<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class RootResource
{
    /**
     * @var string
     */
    public $pathPrefix;

    /**
     * @var string
     */
    public $namePrefix;

    /**
     * @var string
     */
    public $controller;

    /**
     * @var string
     */
    public $idField;

    /**
     * @var \Dontdrinkandroot\RestBundle\Metadata\Annotation\Method[]
     */
    public $methods;
}
