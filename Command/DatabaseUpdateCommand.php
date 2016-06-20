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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class DatabaseUpdateCommand extends ContainerAwareCommand
{
    CONST MIGRATION_PATH = 'Resources'.DIRECTORY_SEPARATOR.'update';

    protected function configure()
    {
        $this
            ->setName('campaignchain:database:update')
            ->setDescription('Run database update for the packages.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Gathering migration files from CampaignChain packages</info>');
        $output->writeln('');

        $rootDir = $this->getContainer()->getParameter('kernel.root_dir').DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
        $finder = new Finder();
        // Find all the CampaignChain module configuration files.
        $finder->files()
            ->in($rootDir)
            ->exclude('app')
            ->exclude('bin')
            ->exclude('component')
            ->exclude('web')
            ->name('campaignchain.yml');

        /** @var Bundle[] $bundleList */
        $bundleList = [];


        $coreComposerFile = $rootDir.'vendor'.DIRECTORY_SEPARATOR.'campaignchain'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'composer.json';
        $bundleList[] = $this->getNewBundle($coreComposerFile);

        foreach ($finder as $moduleConfig) {
            $bundleComposer = $rootDir.str_replace(
                    'campaignchain.yml',
                    'composer.json',
                    $moduleConfig->getRelativePathname()
                );
            $bundleList[] = $this->getNewBundle($bundleComposer);
        }

        if (empty($bundleList)) {
            return;
        }

        $migrationDir = $rootDir.'app'.DIRECTORY_SEPARATOR.'campaignchain'.DIRECTORY_SEPARATOR.'updates';
        $fs = new Filesystem();

        foreach ($bundleList as $bundle) {
            $packageMigrationsDir = $rootDir.'vendor'.DIRECTORY_SEPARATOR.$bundle->getName().DIRECTORY_SEPARATOR.self::MIGRATION_PATH;
            $migrationFiles = new Finder();
            $migrationFiles->files()
                ->in($packageMigrationsDir)
                ->name('Version*.php');

            /** @var SplFileInfo $migrationFile */
            foreach ($migrationFiles as $migrationFile) {
                $fs->copy($migrationFile->getPathname(), $migrationDir.DIRECTORY_SEPARATOR.$migrationFile->getFilename(), true);
            }

        }

        $this->getApplication()
            ->run(new ArrayInput([
                'command' => 'doctrine:migrations:migrate',
                '--no-interaction' => true,
            ]), $output);

    }

    /**
     * @param $bundleComposer
     * @return Bundle
     */
    protected function getNewBundle($bundleComposer)
    {
        if(!file_exists($bundleComposer)) {
            return;
        }

        $bundleComposerData = file_get_contents($bundleComposer);

        $normalizer = new GetSetMethodNormalizer();
        $normalizer->setIgnoredAttributes(array(
            'require',
            'keywords',
        ));
        $encoder = new JsonEncoder();
        $serializer = new Serializer(array($normalizer), array($encoder));

        return $serializer->deserialize($bundleComposerData,'CampaignChain\CoreBundle\Entity\Bundle','json');
    }
}