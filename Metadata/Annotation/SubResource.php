<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class SubResource
{
    /**
     * @var string
     */
    public $entityClass;

    /**
     * @var \Dontdrinkandroot\RestBundle\Metadata\Annotation\Right
     */
    public $listRight;

    /**
     * @var \Dontdrinkandroot\RestBundle\Metadata\Annotation\Right
     */
    public $postRight;
}
