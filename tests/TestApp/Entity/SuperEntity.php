<?php

namespace Niebvelungen\RestBundleDoctrine\Tests\TestApp\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Niebvelungen\RestBundleDoctrine\Metadata\Attribute as REST;

#[ORM\MappedSuperclass]
class SuperEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", nullable: false)]
    private int $id;

    #[REST\Excluded]
    #[ORM\Column(type: Types::STRING, nullable: false)]
    private string $excludedFieldOne;

    #[REST\Excluded]
    #[ORM\Column(type: Types::STRING, nullable: false)]
    private string $excludedFieldTwo;

    public function getId(): int
    {
        return $this->id;
    }

    public function getExcludedFieldOne(): string
    {
        return $this->excludedFieldOne;
    }

    public function setExcludedFieldOne(string $excludedFieldOne): void
    {
        $this->excludedFieldOne = $excludedFieldOne;
    }

    public function getExcludedFieldTwo(): string
    {
        return $this->excludedFieldTwo;
    }

    public function setExcludedFieldTwo(string $excludedFieldTwo): void
    {
        $this->excludedFieldTwo = $excludedFieldTwo;
    }
}
