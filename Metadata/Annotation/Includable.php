<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;
use Doctrine\ORM\Mapping\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD"})
 */
class Includable
{
    /**
     * @var array<string>
     */
    public $paths;
}
