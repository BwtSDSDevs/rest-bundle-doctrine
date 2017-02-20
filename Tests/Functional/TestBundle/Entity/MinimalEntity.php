<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\RestBundle\Metadata\Annotation as REST;

/**
 * @ORM\Entity()
 * @REST\RootResource()
 */
class MinimalEntity
{
    /**
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue()
     *
     * @var int
     */
    private $id;

    /**
     * @return mixed
     */
    public function getId(): int
    {
        return $this->id;
    }
}
