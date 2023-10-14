<?php

namespace Dontdrinkandroot\RestBundle\Tests\TestApp\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\Common\CrudOperation;
use Dontdrinkandroot\RestBundle\Metadata\Attribute as REST;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[REST\RootResource(
    operations: [
        new REST\Operation(method: CrudOperation::LIST, granted: "ROLE_USER"),
        new REST\Operation(method: CrudOperation::CREATE, defaultIncludes: ["details"], granted: "ROLE_ADMIN"),
        new REST\Operation(method: CrudOperation::DELETE, granted: "ROLE_ADMIN"),
        new REST\Operation(method: CrudOperation::READ, defaultIncludes: ["details"], granted: "ROLE_USER"),
        new REST\Operation(method: CrudOperation::UPDATE, defaultIncludes: ["details"], granted: "ROLE_ADMIN")
    ],
    pathPrefix: "secured"
)]
#[ORM\Entity]
class SecuredEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", nullable: false)]
    private int $id;

    #[ORM\Column(type: "uuid", nullable: false, unique: true)]
    private Uuid $uuid;

    #[REST\Includable(["details"])]
    #[REST\Puttable]
    #[ORM\Column(type: "datetime", nullable: true)]
    private ?DateTime $dateTimeField = null;

    #[REST\Includable(["details"])]
    #[REST\Puttable]
    #[ORM\Column(type: "date", nullable: true)]
    private ?DateTime $dateField = null;

    #[REST\Includable(["details"])]
    #[REST\Puttable]
    #[ORM\Column(type: "time", nullable: true)]
    private ?DateTime $timeField = null;

    #[REST\Includable(["details"])]
    #[REST\Postable]
    #[Assert\Type(type: "integer")]
    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $integerField = null;

    /**
     * @var Collection<array-key,SubResourceEntity>
     */
    #[REST\Includable]
    #[REST\SubResource([
        new REST\Operation(method: CrudOperation::LIST, granted: "ROLE_USER"),
        new REST\Operation(method: CrudOperation::CREATE, defaultIncludes: ["parentEntity"], granted: "ROLE_ADMIN"),
        new REST\Operation(method: CrudOperation::UPDATE, granted: "ROLE_ADMIN"),
        new REST\Operation(method: CrudOperation::DELETE, granted: "ROLE_ADMIN")
    ])]
    #[ORM\OneToMany(targetEntity: SubResourceEntity::class, mappedBy: "parentEntity")]
    private $subResources;

    #[REST\Puttable]
    #[REST\Includable(["details"])]
    #[ORM\Embedded(class: EmbeddableEntity::class)]
    private EmbeddableEntity $embeddedEntity;

    #[REST\Excluded]
    private mixed $unmappedField;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
        $this->subResources = new ArrayCollection();
        $this->embeddedEntity = new EmbeddableEntity();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setUuid(Uuid $uuid)
    {
        $this->uuid = $uuid;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getDateTimeField(): ?DateTime
    {
        return $this->dateTimeField;
    }

    public function setDateTimeField(?DateTime $dateTimeField)
    {
        $this->dateTimeField = $dateTimeField;
    }

    public function getDateField(): ?DateTime
    {
        return $this->dateField;
    }

    public function setDateField(?DateTime $dateField)
    {
        $this->dateField = $dateField;
    }

    public function getTimeField(): ?DateTime
    {
        return $this->timeField;
    }

    public function setTimeField(?DateTime $timeField)
    {
        $this->timeField = $timeField;
    }

    public function getSubResources()
    {
        return $this->subResources;
    }

    public function addSubResource(SubResourceEntity $subResourceEntity)
    {
        $subResourceEntity->setParentEntity($this);
        $this->subResources->add($subResourceEntity);
    }

    public function removeSubResource(SubResourceEntity $subResourceEntity)
    {
        $subResourceEntity->setParentEntity(null);
        $this->subResources->removeElement($subResourceEntity);
    }

    public function getEmbeddedEntity(): EmbeddableEntity
    {
        return $this->embeddedEntity;
    }

    public function setEmbeddedEntity(EmbeddableEntity $embeddedEntity)
    {
        $this->embeddedEntity = $embeddedEntity;
    }

    public function getIntegerField(): ?int
    {
        return $this->integerField;
    }

    public function setIntegerField(?int $integerField): void
    {
        $this->integerField = $integerField;
    }
}
