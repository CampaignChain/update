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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class DoctrineMigrateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('campaignchain:doctrine:migration')
            ->setDescription('Run doctrine migration for the packages.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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

        if (empty($bundleList)) {
            return;
        }

        $bundlesHasMigrations = [];

        foreach ($bundleList as $bundle) {
            $extra = $bundle->getExtra();
            if (isset($extra['campaignchain']['migration']) && $extra['campaignchain']['migration']) {
                $bundlesHasMigrations[] = $bundle;
            }
        }

        if (empty($bundlesHasMigrations)) {
            return;
        }
        $fs = new Filesystem();
dump($bundlesHasMigrations);

        foreach ($bundlesHasMigrations as $bundleHasMigrations) {

        }

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