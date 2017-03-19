<?php

namespace Dontdrinkandroot\RestBundle\Command;

use Dontdrinkandroot\RestBundle\Service\AccessTokenServiceInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class CleanUpAccessTokensCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('ddr:rest:access-token:cleanup');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var AccessTokenServiceInterface $accessTokenService */
        $accessTokenService = $this->getContainer()->get('ddr.rest.service.access_token');
        $numTokensRemoved = $accessTokenService->cleanUpExpiredTokens();
        $output->writeln($numTokensRemoved . ' tokens removed');
    }
}
