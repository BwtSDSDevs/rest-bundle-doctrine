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
    public $service;

    /**
     * @var string
     */
    public $controller;

    /**
     * @var \Dontdrinkandroot\RestBundle\Metadata\Annotation\Right
     */
    public $postRight;

    /**
     * @var \Dontdrinkandroot\RestBundle\Metadata\Annotation\Right
     */
    public $putRight;

    /**
     * @var \Dontdrinkandroot\RestBundle\Metadata\Annotation\Right
     */
    public $deleteRight;

    /**
     * @var \Dontdrinkandroot\RestBundle\Metadata\Annotation\Right
     */
    public $listRight;

    /**
     * @var \Dontdrinkandroot\RestBundle\Metadata\Annotation\Right
     */
    public $getRight;
}
