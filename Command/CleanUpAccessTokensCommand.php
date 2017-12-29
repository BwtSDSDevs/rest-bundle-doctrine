<?php

namespace Dontdrinkandroot\RestBundle\Command;

use Dontdrinkandroot\RestBundle\Service\AccessTokenServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class CleanUpAccessTokensCommand extends Command
{
    protected static $defaultName = 'ddr:rest:access-token:cleanup';

    /**
     * @var AccessTokenServiceInterface
     */
    private $accessTokenService;

    /**
     * CleanUpAccessTokensCommand constructor.
     */
    public function __construct(AccessTokenServiceInterface $accessTokenService)
    {
        parent::__construct();
        $this->accessTokenService = $accessTokenService;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $numTokensRemoved = $this->accessTokenService->cleanUpExpiredTokens();
        $output->writeln($numTokensRemoved . ' tokens removed');
    }
}
