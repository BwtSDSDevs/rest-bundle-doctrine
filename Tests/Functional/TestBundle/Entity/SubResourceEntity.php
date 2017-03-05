<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\RestBundle\Metadata\Annotation as REST;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 *
 * @ORM\Entity(
 *     repositoryClass="Dontdrinkandroot\Service\DoctrineCrudService"
 * )
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
     * @ORM\ManyToOne(targetEntity="SecuredEntity", inversedBy="subResources")
     *
     * @var SecuredEntity
     */
    private $parentEntity;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=true)
     *
     * @var User|null
     */
    private $creator;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function setParentEntity(?SecuredEntity $parentEntity)
    {
        $this->parentEntity = $parentEntity;
    }

    public function getParentEntity(): ?SecuredEntity
    {
        return $this->parentEntity;
    }

    /**
     * @return User|null
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param User|null $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }
}
