<?php
/*
 * This file is part of the CampaignChain package.
 *
 * (c) CampaignChain, Inc. <info@campaignchain.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace CampaignChain\DeploymentUpdateBundle\Service;

use Symfony\Component\Console\Style\SymfonyStyle;

class DummyUpdater implements CodeUpdateInterface
{
    public function getVersion()
    {
        return 20160212232323;
    }

    public function getDescription()
    {
        return [
            'This will be one task',
            'And this will be an other task',
            'And just for fun, a third one',
        ];
    }

    public function execute(SymfonyStyle $io = null)
    {
        $i = 5;
        $io->progressStart($i);

        for ($a = 1; $a < $i; $a++) {
            sleep(1);
            $io->progressAdvance();
        }

        $io->progressFinish();

        return true;
    }

}