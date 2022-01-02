<?php

namespace Dontdrinkandroot\RestBundle\Tests\TestApp\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\RestBundle\Metadata\Annotation as REST;

#[ORM\MappedSuperclass]
class SuperEntity
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", nullable: false)]
    private $id;

    /**
     * @REST\Excluded()
     *
     * @var string
     */
    #[ORM\Column(type: Types::STRING, nullable: false)]
    private $excludedFieldOne;

    /**
     * @REST\Excluded()
     *
     * @var string
     */
    #[ORM\Column(type: Types::STRING, nullable: false)]
    private $excludedFieldTwo;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getExcludedFieldOne(): ?string
    {
        return $this->excludedFieldOne;
    }

    /**
     * @param string $excludedFieldOne
     */
    public function setExcludedFieldOne(string $excludedFieldOne)
    {
        $this->excludedFieldOne = $excludedFieldOne;
    }

    /**
     * @return string
     */
    public function getExcludedFieldTwo(): ?string
    {
        return $this->excludedFieldTwo;
    }

    /**
     * @param string $excludedFieldTwo
     */
    public function setExcludedFieldTwo(string $excludedFieldTwo)
    {
        $this->excludedFieldTwo = $excludedFieldTwo;
    }
}
