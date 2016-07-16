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

use CampaignChain\CoreBundle\Entity\Bundle;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

class DatabaseDiffCommand extends ContainerAwareCommand
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    protected function configure()
    {
        $this
            ->setName('campaignchain:database:generate-diff')
            ->setDescription('Creates a diff migration file for the selected package.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $locator = $this->getContainer()->get('campaignchain.core.module.locator');
        $bundles = $locator->getAvailableBundles();

        $selectedBundle = $this->selectBundle($bundles);

        $generateOutput = new BufferedOutput();
        $application = new Application($this->getContainer()->get('kernel'));
        $application->setAutoExit(false);

        $application->run(new ArrayInput([
            'command' => 'doctrine:migrations:diff',
            '--no-interaction' => true,
        ]), $generateOutput);

        preg_match('/Generated new migration class to "(.*)"/', $generateOutput->fetch(), $matches);

        if (count($matches) < 2) {
            //error
            return;
        }

        $pathForMigrationFile = $matches[1];
        preg_match('/Version.*.php/', $pathForMigrationFile, $fileNames);

        if (!count($fileNames)) {
            return;
        }

        $rootDir = $this->getContainer()->getParameter('kernel.root_dir').DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
        $targetFile = $rootDir.'vendor'.DIRECTORY_SEPARATOR.$selectedBundle->getName().DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'updates'.DIRECTORY_SEPARATOR.$fileNames[0];
        $fs = new Filesystem();
        $fs->copy($pathForMigrationFile, $targetFile);
        $fs->remove($pathForMigrationFile);

        $this->io->success('Generation finished. You can find your empty file here');
        $this->io->text($targetFile);
    }

    /**
     * @param Bundle[] $bundles
     *
     * @return Bundle|null
     */
    private function selectBundle(array $bundles)
    {
        $packageNames = array_map(function(Bundle $bundle) {
            return $bundle->getName();
        }, $bundles);

        $selectedName = $this->io->choice(
            'Please select the package, where you want to place the Migration file (defaults to campaignchain/core)',
            $packageNames,
            "campaignchain/core"
        );

        $this->io->text('You have selected: '.$selectedName);

        $selectedBundle = null;

        foreach ($bundles as $bundle) {
            if ($bundle->getName() != $selectedName) {
                continue;
            }

            $selectedBundle = $bundle;
            break;
        }

        return $selectedBundle;
    }
}