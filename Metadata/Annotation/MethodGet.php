<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
class MethodGet extends Method
{
    public function getName(): string
    {
        return Method::GET;
    }
}
