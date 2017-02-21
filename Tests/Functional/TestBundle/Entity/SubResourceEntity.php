<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 *
 * @ORM\Entity()
 */
class SubResourceEntity
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
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
