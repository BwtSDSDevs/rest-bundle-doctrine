<?php

return [
    new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
    new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
    new \Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
    new \Liip\FunctionalTestBundle\LiipFunctionalTestBundle(),
    new \Dontdrinkandroot\RestBundle\DdrRestBundle(),
    new \Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\TestBundle(),
];
