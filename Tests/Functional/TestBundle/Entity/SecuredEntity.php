<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\RestBundle\Metadata\Annotation as REST;
use Ramsey\Uuid\Uuid;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 *
 * @ORM\Entity()
 * @REST\RootResource(
 *     pathPrefix="secured",
 *     listRight=@REST\Right(attributes={"ROLE_USER"})
 * )
 */
class SecuredEntity
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
     * @ORM\Column(type="guid", nullable=false)
     *
     * @var string
     */
    private $uuid;

    private $unmappedField;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function setUuid(string $uuid)
    {
        $this->uuid = $uuid;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}
