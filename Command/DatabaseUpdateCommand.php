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

use CampaignChain\CoreBundle\Entity\Bundle;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class DatabaseUpdateCommand extends ContainerAwareCommand
{
    private $migrationPath;

    protected function configure()
    {
        $this
            ->setName('campaignchain:database:update')
            ->setDescription('Run database update for the packages.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->migrationPath = 'Resources'.DIRECTORY_SEPARATOR.'updates';

        $io = new SymfonyStyle($input, $output);
        $io->title('Gathering migration files from CampaignChain packages');
        $io->newLine();

        $locator = $this->getContainer()->get('campaignchain.core.module.locator');
        $bundleList = $locator->getAvailableBundles();

        if (empty($bundleList)) {
            $io->error('No CampaignChain Module found');
            return;
        }

        $rootDir = $this->getContainer()->getParameter('kernel.root_dir').DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
        $migrationDir = $rootDir.'app'.DIRECTORY_SEPARATOR.'campaignchain'.DIRECTORY_SEPARATOR.'updates';

        $fs = new Filesystem();

        $table = [];

        foreach ($bundleList as $bundle) {
            $packageMigrationsDir = $rootDir.'vendor'.DIRECTORY_SEPARATOR.$bundle->getName().DIRECTORY_SEPARATOR.$this->migrationPath;

            if (!$fs->exists($packageMigrationsDir)) {
                continue;
            }

            $migrationFiles = new Finder();
            $migrationFiles->files()
                ->in($packageMigrationsDir)
                ->name('Version*.php');

            $files = [];

            /** @var SplFileInfo $migrationFile */
            foreach ($migrationFiles as $migrationFile) {
                $fs->copy($migrationFile->getPathname(), $migrationDir.DIRECTORY_SEPARATOR.$migrationFile->getFilename(), true);
                $files[] = $migrationFile->getFilename();

            }
            $table[] = [$bundle->getName(), implode(', ', $files)];

        }
        $io->table(['Module', 'Versions'], $table);

        $this->getApplication()
            ->run(new ArrayInput([
                'command' => 'doctrine:migrations:migrate',
                '--no-interaction' => true,
            ]), $output);

    }
}