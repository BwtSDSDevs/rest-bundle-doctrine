<?php

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Dontdrinkandroot\RestBundle\DdrRestBundle;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\TestBundle;
use Liip\FunctionalTestBundle\LiipFunctionalTestBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;

return [
    FrameworkBundle::class          => ['all' => true],
    DoctrineBundle::class           => ['all' => true],
    SecurityBundle::class           => ['all' => true],
    DoctrineFixturesBundle::class   => [
        'dev'     => true,
        'test'    => true,
        'secured' => true,
        'minimal' => true
    ],
    LiipFunctionalTestBundle::class => [
        'dev'     => true,
        'test'    => true,
        'secured' => true,
        'minimal' => true
    ],
    DdrRestBundle::class            => ['all' => true],
    TestBundle::class               => ['all' => true],
];
