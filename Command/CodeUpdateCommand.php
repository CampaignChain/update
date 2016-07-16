<?php
/*
 * Copyright 2016 CampaignChain, Inc. <info@campaignchain.com>
 *
 * NOTICE:  All information contained herein is, and remains the property of
 * CampaignChain, Inc. and its suppliers, if any. The intellectual and technical
 * concepts contained herein are proprietary to CampaignChain, Inc. and its
 * suppliers and may be covered by U.S. and Foreign Patents, patents in process,
 * and are protected by trade secret or copyright law. Dissemination of this
 * information or reproduction of this material is strictly forbidden unless prior
 * written permission is obtained from CampaignChain, Inc..
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