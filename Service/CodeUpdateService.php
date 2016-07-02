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

use CampaignChain\DeploymentUpdateBundle\Entity\CodeUpdateVersion;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class CodeUpdateService
 * @package CampaignChain\Service\Command
 */
class CodeUpdateService
{
    /**
     * @var CodeUpdateInterface[]
     */
    private $versions = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * CodeUpdateService constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param CodeUpdateInterface $codeUpdate
     */
    public function addUpdater(CodeUpdateInterface $codeUpdate)
    {
        $this->versions[(int) $codeUpdate->getVersion()] = $codeUpdate;
        ksort($this->versions);
    }


    /**
     * @param SymfonyStyle|null $io
     */
    public function updateCode(SymfonyStyle $io = null)
    {
        $io->title('CampaignChain code update');

        if (empty($this->versions)) {
            $io->warning('No code updater Service found, maybe you didn\'t enabled a bundle?');

            return;
        }

        $io->comment('The following versions will be updated');

        $migratedVersions = array_map(
            function(CodeUpdateVersion $version) {
                return $version->getVersion();
            },
            $this->entityManager
                ->getRepository('CampaignChainDeploymentUpdateBundle:CodeUpdateVersion')
                ->findAll()
        );

        $updated = false;

        foreach ($this->versions as $version => $class) {
            if (in_array($version, $migratedVersions)) {
                continue;
            }

            $io->section('Version '.$class->getVersion());
            $io->listing($class->getDescription());

            $io->text('Begin Update');

            $result = $class->execute($io);

            if ($result) {
                $dbVersion = new CodeUpdateVersion();
                $dbVersion->setVersion($version);

                $this->entityManager->persist($dbVersion);
                $this->entityManager->flush();

                $io->text('Update finished');
            }


            $updated = true;
        }

        if (!$updated) {
            $io->success('Everything is up to date.');
        } else {
            $io->success('Every version is updated.');
        }
    }
}