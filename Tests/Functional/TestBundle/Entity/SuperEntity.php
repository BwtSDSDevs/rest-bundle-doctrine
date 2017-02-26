<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Dontdrinkandroot\RestBundle\Metadata\Annotation as REST;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 *
 * @MappedSuperclass()
 */
class SuperEntity
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
     * @ORM\Column(type="string", nullable= false)
     * @REST\Excluded()
     *
     * @var string
     */
    private $excludedFieldOne;

    /**
     * @ORM\Column(type="string", nullable= false)
     * @REST\Excluded()
     *
     * @var string
     */
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
