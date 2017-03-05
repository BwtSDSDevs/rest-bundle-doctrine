<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\RestBundle\Entity\AccessToken as BaseAccessToken;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 *
 * @ORM\Entity()
 */
class AccessToken extends BaseAccessToken
{
    /**
     * @ORM\ManyToOne(targetEntity="user")
     * @ORM\JoinColumn(nullable=false)
     *
     * @var User
     */
    private $user;

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
}
