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
     * @var Operation[]
     */
    public $operations;
}
