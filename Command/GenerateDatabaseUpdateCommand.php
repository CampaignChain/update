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
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class GenerateDatabaseUpdateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('campaignchain:database:generate-update')
            ->setDescription('Creates an empty migration file for the selected package.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bundles = $this->getPackages();
        $packageNames = array_map(function(Bundle $bundle) {
            return $bundle->getName();
        }, $bundles);

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            sprintf('Please select the package, where you want to place the Migration file (defaults to %s)', $packageNames[0]),
            $packageNames,
            0
        );
        $question->setErrorMessage('Package name %s is invalid.');

        $selectedName = $helper->ask($input, $output, $question);
        $output->writeln('You have selected: '.$selectedName);

        $selectedBundle = null;
        foreach ($bundles as $bundle) {
            if ($bundle->getName() == $selectedName) {
                $selectedBundle = $bundle;
            }
        }

        $generateOutput = new BufferedOutput();

        $application = new Application($this->getContainer()->get('kernel'));
        $application->setAutoExit(false);

        $application->run(new ArrayInput([
                'command' => 'doctrine:migrations:generate',
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
        $targetFile = $rootDir.'vendor'.DIRECTORY_SEPARATOR.$selectedBundle->getName().DIRECTORY_SEPARATOR.DoctrineMigrateCommand::MIGRATION_PATH.DIRECTORY_SEPARATOR.$fileNames[0];
        $fs = new Filesystem();
        $fs->copy($pathForMigrationFile, $targetFile);
        $fs->remove($pathForMigrationFile);
    }

    /**
     * @return \CampaignChain\CoreBundle\Entity\Bundle[]
     */
    private function getPackages()
    {
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

        return $bundleList;
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