<?php

namespace Dontdrinkandroot\RestBundle\Tests\TestApp\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\RestBundle\Metadata\Annotation as REST;

/**
 * @ORM\Entity()
 * @REST\RootResource(
 *     methods = {
 *         @REST\Method("CREATE"),
 *         @REST\Method("UPDATE"),
 *     }
 * )
 */
class PuttablePostableAnnotationEntity
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
     * @ORM\Column(type="string", nullable=true)
     * @REST\Puttable()
     *
     * @var string|null
     */
    private $puttableByAll;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @REST\Postable()
     *
     * @var string|null
     */
    private $postableByAll;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @REST\Puttable(@REST\Right("ROLE_USER"))
     *
     * @var string|null
     */
    private $puttableByUser;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @REST\Postable(@REST\Right("ROLE_USER"))
     *
     * @var string|null
     */
    private $postableByUser;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @REST\Puttable(@REST\Right("ROLE_ADMIN"))
     *
     * @var string|null
     */
    private $puttableByAdmin;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @REST\Postable(@REST\Right("ROLE_ADMIN"))
     *
     * @var string|null
     */
    private $postableByAdmin;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getPuttableByAll()
    {
        return $this->puttableByAll;
    }

    /**
     * @param null|string $puttableByAll
     */
    public function setPuttableByAll($puttableByAll)
    {
        $this->puttableByAll = $puttableByAll;
    }

    /**
     * @return null|string
     */
    public function getPostableByAll()
    {
        return $this->postableByAll;
    }

    /**
     * @param null|string $postableByAll
     */
    public function setPostableByAll($postableByAll)
    {
        $this->postableByAll = $postableByAll;
    }

    /**
     * @return null|string
     */
    public function getPuttableByUser()
    {
        return $this->puttableByUser;
    }

    /**
     * @param null|string $puttableByUser
     */
    public function setPuttableByUser($puttableByUser)
    {
        $this->puttableByUser = $puttableByUser;
    }

    /**
     * @return null|string
     */
    public function getPostableByUser()
    {
        return $this->postableByUser;
    }

    /**
     * @param null|string $postableByUser
     */
    public function setPostableByUser($postableByUser)
    {
        $this->postableByUser = $postableByUser;
    }

    /**
     * @return null|string
     */
    public function getPuttableByAdmin()
    {
        return $this->puttableByAdmin;
    }

    /**
     * @param null|string $puttableByAdmin
     */
    public function setPuttableByAdmin($puttableByAdmin)
    {
        $this->puttableByAdmin = $puttableByAdmin;
    }

    /**
     * @return null|string
     */
    public function getPostableByAdmin()
    {
        return $this->postableByAdmin;
    }

    /**
     * @param null|string $postableByAdmin
     */
    public function setPostableByAdmin($postableByAdmin)
    {
        $this->postableByAdmin = $postableByAdmin;
    }
}
