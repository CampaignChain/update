<?php
/*
 * Copyright 2016 CampaignChain, Inc. <info@campaignchain.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace CampaignChain\UpdateBundle\Command;

use CampaignChain\CoreBundle\Entity\Bundle;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class SchemaUpdateCommand extends ContainerAwareCommand
{
    private $migrationPath;

    protected function configure()
    {
        $this
            ->setName('campaignchain:schema:update')
            ->setDescription('Run database schema update for the packages.')
            ->addOption(
                'gather-only',
                null,
                InputOption::VALUE_NONE,
                'With this option, the command will only gather the files.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->migrationPath =
            str_replace('/', DIRECTORY_SEPARATOR,
                $this->getContainer()->getParameter('campaignchain_deployment_update.bundle.schema_dir')
            );

        $io = new SymfonyStyle($input, $output);
        $io->title('Gathering migration files from CampaignChain packages');
        $io->newLine();

        $locator = $this->getContainer()->get('campaignchain.core.module.locator');
        $bundleList = $locator->getAvailableBundles();

        if (empty($bundleList)) {
            $io->error('No CampaignChain Module found');
            return;
        }

        $migrationsDir  = $this->getContainer()->getParameter('doctrine_migrations.dir_name');

        $fs = new Filesystem();

        $table = [];

        foreach ($bundleList as $bundle) {
            $packageSchemaDir = $this->getContainer()->getParameter('kernel.root_dir').
                DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'vendor'.
                DIRECTORY_SEPARATOR.$bundle->getName().$this->migrationPath;

            if (!$fs->exists($packageSchemaDir)) {
                continue;
            }

            $migrationFiles = new Finder();
            $migrationFiles->files()
                ->in($packageSchemaDir)
                ->name('Version*.php');

            $files = [];

            /** @var SplFileInfo $migrationFile */
            foreach ($migrationFiles as $migrationFile) {
                $fs->copy($migrationFile->getPathname(), $migrationsDir.DIRECTORY_SEPARATOR.$migrationFile->getFilename(), true);
                $files[] = $migrationFile->getFilename();

            }
            $table[] = [$bundle->getName(), implode(', ', $files)];

        }
        $io->table(['Module', 'Versions'], $table);

        if (!$input->getOption('gather-only')) {
            $this->getApplication()
                ->run(new ArrayInput([
                    'command' => 'doctrine:migrations:migrate',
                    '--no-interaction' => true,
                ]), $output);
        }
    }
}