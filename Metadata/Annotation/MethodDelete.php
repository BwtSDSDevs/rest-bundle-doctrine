<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
class MethodDelete extends Method
{
    public function getName(): string
    {
        return Method::DELETE;
    }
}
