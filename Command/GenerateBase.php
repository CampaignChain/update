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

class GenerateBase extends ContainerAwareCommand
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    protected function getDoctrineMigrationsCommand()
    {
        throw new \Exception('You must overwrite '.get_class($this).'::'.__FUNCTION__);
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
            'command' => $this->getDoctrineMigrationsCommand(),
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

        $schemaFile  = $this->getContainer()->getParameter('kernel.root_dir').DIRECTORY_SEPARATOR.'..';
        $schemaFile .= DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.$selectedBundle->getName();
        $schemaFile .= str_replace('/', DIRECTORY_SEPARATOR, $this->getContainer()->getParameter('campaignchain_deployment_update.bundle.schema_dir'));
        $schemaFile .= DIRECTORY_SEPARATOR.$fileNames[0];
        $fs = new Filesystem();
        $fs->copy($pathForMigrationFile, $schemaFile);
        $fs->remove($pathForMigrationFile);

        $this->io->success('Generation finished. You can find the file here:');
        $this->io->text($schemaFile);
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
            'Please select the package, where you want to place the Migration file',
            $packageNames,
            $this->getContainer()->getParameter('campaignchain_deployment_update.diff_package')
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