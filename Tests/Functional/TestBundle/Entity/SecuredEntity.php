<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\RestBundle\Metadata\Annotation as REST;
use Ramsey\Uuid\Uuid;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 *
 * @ORM\Entity(
 *     repositoryClass="Dontdrinkandroot\Service\DoctrineCrudService"
 * )
 * @REST\RootResource(
 *      pathPrefix="secured",
 *      methods={
 *          @REST\Method(name="LIST", right=@REST\Right(attributes={"ROLE_USER"})),
 *          @REST\Method(name="GET", right=@REST\Right(attributes={"ROLE_USER"}), defaultIncludes={"details"}),
 *          @REST\Method(name="PUT", right=@REST\Right(attributes={"ROLE_ADMIN"}), defaultIncludes={"details"})
 *     }
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
     * @REST\Includable("details")
     * @ORM\Column(type="datetime", nullable=true)
     * @REST\Puttable()
     *
     * @var \DateTime|null
     */
    private $dateTimeField;

    /**
     * @REST\Includable("details")
     * @ORM\Column(type="date", nullable=true)
     * @REST\Puttable()
     *
     * @var \DateTime|null
     */
    private $dateField;

    /**
     * @REST\Includable("details")
     * @ORM\Column(type="time", nullable=true)
     * @REST\Puttable()
     *
     * @var \DateTime|null
     */
    private $timeField;

    /**
     * @ORM\OneToMany(targetEntity="SubResourceEntity", mappedBy="parentEntity")
     * @REST\Includable()
     * @REST\SubResource(
     *      methods={
     *          @REST\Method(name="LIST", right=@REST\Right(attributes={"ROLE_USER"})),
     *          @REST\Method(name="POST", right=@REST\Right(attributes={"ROLE_ADMIN"}), defaultIncludes={"parentEntity"}),
     *          @REST\Method(name="PUT", right=@REST\Right(attributes={"ROLE_ADMIN"})),
     *          @REST\Method(name="DELETE", right=@REST\Right(attributes={"ROLE_ADMIN"}))
     *      }
     * )
     *
     * @var SubResourceEntity[]|Collection
     */
    private $subResources;

    /**
     * @REST\Includable("details")
     * @ORM\Embedded(class="EmbeddableEntity")
     * @REST\Puttable()
     *
     * @var EmbeddableEntity
     */
    private $embeddedEntity;

    /**
     * @REST\Excluded()
     *
     * @var mixed
     */
    private $unmappedField;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
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
}
