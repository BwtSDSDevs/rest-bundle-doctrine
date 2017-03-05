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
    public $path;

    /**
     * @var \Dontdrinkandroot\RestBundle\Metadata\Annotation\Method[]
     */
    public $methods;
}
