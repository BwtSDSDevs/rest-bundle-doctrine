<?php

namespace Dontdrinkandroot\RestBundle\Tests\TestApp\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\RestBundle\Metadata\Annotation as REST;

/**
 * @REST\RootResource(
 *     operations = {
 *         @REST\Operation("CREATE"),
 *         @REST\Operation("UPDATE"),
 *     }
 * )
 */
#[ORM\Entity]
class PuttablePostableAnnotationEntity
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", nullable: false)]
    private $id;

    /**
     * @REST\Puttable()
     *
     * @var string|null
     */
    #[ORM\Column(type: "string", nullable: true)]
    private $puttableByAll;

    /**
     * @REST\Postable()
     *
     * @var string|null
     */
    #[ORM\Column(type: "string", nullable: true)]
    private $postableByAll;

    /**
     * @REST\Puttable(@REST\Right("ROLE_USER"))
     *
     * @var string|null
     */
    #[ORM\Column(type: "string", nullable: true)]
    private $puttableByUser;

    /**
     * @REST\Postable(@REST\Right("ROLE_USER"))
     *
     * @var string|null
     */
    #[ORM\Column(type: "string", nullable: true)]
    private $postableByUser;

    /**
     * @REST\Puttable(@REST\Right("ROLE_ADMIN"))
     *
     * @var string|null
     */
    #[ORM\Column(type: "string", nullable: true)]
    private $puttableByAdmin;

    /**
     * @REST\Postable(@REST\Right("ROLE_ADMIN"))
     *
     * @var string|null
     */
    #[ORM\Column(type: "string", nullable: true)]
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
