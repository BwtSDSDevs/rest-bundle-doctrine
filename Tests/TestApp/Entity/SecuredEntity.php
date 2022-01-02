<?php

namespace Dontdrinkandroot\RestBundle\Tests\TestApp\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\RestBundle\Metadata\Annotation as REST;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @REST\RootResource(
 *      pathPrefix="secured",
 *      operations={
 *          @REST\Operation(name="LIST", right=@REST\Right(attributes={"ROLE_USER"})),
 *          @REST\Operation(name="CREATE", right=@REST\Right(attributes={"ROLE_ADMIN"}), defaultIncludes={"details"}),
 *          @REST\Operation(name="DELETE", right=@REST\Right(attributes={"ROLE_ADMIN"})),
 *          @REST\Operation(name="READ", right=@REST\Right(attributes={"ROLE_USER"}), defaultIncludes={"details"}),
 *          @REST\Operation(name="UPDATE", right=@REST\Right(attributes={"ROLE_ADMIN"}), defaultIncludes={"details"})
 *     }
 * )
 */
#[ORM\Entity]
class SecuredEntity
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", nullable: false)]
    private $id;

    /**
     * @var Uuid
     */
    #[ORM\Column(type: "uuid", nullable: false, unique: true)]
    private $uuid;

    /**
     * @REST\Includable("details")
     * @REST\Puttable()
     *
     * @var DateTime|null
     */
    #[ORM\Column(type: "datetime", nullable: true)]
    private $dateTimeField;

    /**
     * @REST\Includable("details")
     * @REST\Puttable()
     *
     * @var DateTime|null
     */
    #[ORM\Column(type: "date", nullable: true)]
    private $dateField;

    /**
     * @REST\Includable("details")
     * @ORM\Column(type="time", nullable=true)
     * @REST\Puttable()
     *
     * @var DateTime|null
     */
    #[ORM\Column(type: "time", nullable: true)]
    private $timeField;

    /**
     * @REST\Includable("details")
     * @Assert\Type("integer")
     * @var int|null
     * @REST\Postable()
     */
    #[ORM\Column(type: "integer", nullable: true)]
    private $integerField;

    /**
     * @REST\Includable()
     * @REST\SubResource(
     *      operations={
     *          @REST\Operation(name="LIST", right=@REST\Right(attributes={"ROLE_USER"})),
     *          @REST\Operation(name="CREATE", right=@REST\Right(attributes={"ROLE_ADMIN"}), defaultIncludes={"parentEntity"}),
     *          @REST\Operation(name="UPDATE", right=@REST\Right(attributes={"ROLE_ADMIN"})),
     *          @REST\Operation(name="DELETE", right=@REST\Right(attributes={"ROLE_ADMIN"}))
     *      }
     * )
     *
     * @var SubResourceEntity[]|Collection
     */
    #[ORM\OneToMany(targetEntity: SubResourceEntity::class, mappedBy: "parentEntity")]
    private $subResources;

    /**
     * @REST\Includable("details")
     * @REST\Puttable()
     *
     * @var EmbeddableEntity
     */
    #[ORM\Embedded(class: EmbeddableEntity::class)]
    private $embeddedEntity;

    /**
     * @REST\Excluded()
     *
     * @var mixed
     */
    private $unmappedField;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
        $this->subResources = new ArrayCollection();
        $this->embeddedEntity = new EmbeddableEntity();
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

    /**
     * @return EmbeddableEntity
     */
    public function getEmbeddedEntity(): EmbeddableEntity
    {
        return $this->embeddedEntity;
    }

    /**
     * @param EmbeddableEntity $embeddedEntity
     */
    public function setEmbeddedEntity(EmbeddableEntity $embeddedEntity)
    {
        $this->embeddedEntity = $embeddedEntity;
    }

    /**
     * @return int|null
     */
    public function getIntegerField()
    {
        return $this->integerField;
    }

    /**
     * @param int|null $integerField
     */
    public function setIntegerField($integerField)
    {
        $this->integerField = $integerField;
    }
}
