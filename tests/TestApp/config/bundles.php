<?php

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Niebvelungen\RestBundleDoctrine\DdrRestBundle;
use Liip\TestFixturesBundle\LiipTestFixturesBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;

return [
    FrameworkBundle::class        => ['all' => true],
    DoctrineBundle::class         => ['all' => true],
    SecurityBundle::class         => ['all' => true],
    DoctrineFixturesBundle::class => ['all' => true],
    LiipTestFixturesBundle::class => ['all' => true],
    DdrRestBundle::class          => ['all' => true],
];
