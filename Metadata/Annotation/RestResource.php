<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class RestResource
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
    public $service;

    /**
     * @var string
     */
    public $controller;
}
