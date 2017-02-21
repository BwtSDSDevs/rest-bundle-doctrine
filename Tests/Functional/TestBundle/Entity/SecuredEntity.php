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
 *     listRight=@REST\Right(attributes={"ROLE_USER"}),
 *     getRight=@REST\Right(attributes={"ROLE_USER"})
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

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime|null
     */
    private $dateTimeField;

    /**
     * @ORM\Column(type="date", nullable=true)
     *
     * @var \DateTime|null
     */
    private $dateField;

    /**
     * @ORM\Column(type="time", nullable=true)
     *
     * @var \DateTime|null
     */
    private $timeField;

    /**
     * @REST\Excluded()
     *
     * @var mixed
     */
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

    public function getDateTimeField(): ?\DateTime
    {
        return $this->dateTimeField;
    }

    public function setDateTimeField(?\DateTime $dateTimeField)
    {
        $this->dateTimeField = $dateTimeField;
    }

    public function getDateField(): ?\DateTime
    {
        return $this->dateField;
    }

    public function setDateField(?\DateTime $dateField)
    {
        $this->dateField = $dateField;
    }

    public function getTimeField(): ?\DateTime
    {
        return $this->timeField;
    }

    public function setTimeField(?\DateTime $timeField)
    {
        $this->timeField = $timeField;
    }
}
