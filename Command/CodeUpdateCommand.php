<?php
/*
 * This file is part of the CampaignChain package.
 *
 * (c) CampaignChain, Inc. <info@campaignchain.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace CampaignChain\DeploymentUpdateBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CodeUpdateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('campaignchain:code:update')
            ->setDescription('Run code update for the packages.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $service = $this->getContainer()->get('campaignchain.deployment.update.code_updater');
        $service->updateCode($io);
    }
}