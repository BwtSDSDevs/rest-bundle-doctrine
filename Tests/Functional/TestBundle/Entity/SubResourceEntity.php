<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity;

use Doctrine\Common\Collections\Collection;
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
     * @ORM\ManyToMany(targetEntity="SecuredEntity", mappedBy="subResources")
     *
     * @var Collection|SecuredEntity[]
     */
    private $securedEntities;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
