<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class SubResource
{
    /**
     * @var \Dontdrinkandroot\RestBundle\Metadata\Annotation\Right
     */
    public $listRight;

    /**
     * @var \Dontdrinkandroot\RestBundle\Metadata\Annotation\Right
     */
    public $postRight;

    /**
     * @var string
     */
    public $path;
}
