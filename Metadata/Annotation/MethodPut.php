<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
class MethodPut extends Method
{
    public function getName(): string
    {
        return Method::PUT;
    }
}
