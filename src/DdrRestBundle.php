<?php

namespace Dontdrinkandroot\RestBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DdrRestBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
