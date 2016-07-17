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

namespace CampaignChain\DeploymentUpdateBundle\Service;

use CampaignChain\DeploymentUpdateBundle\Entity\DataUpdateVersion;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class DataUpdateService
 * @package CampaignChain\Service\Command
 */
class DataUpdateService
{
    /**
     * @var DataUpdateInterface[]
     */
    private $versions = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * DataUpdateService constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param DataUpdateInterface $codeUpdate
     */
    public function addUpdater(DataUpdateInterface $codeUpdate)
    {
        $this->versions[(int) $codeUpdate->getVersion()] = $codeUpdate;
        ksort($this->versions);
    }


    /**
     * @param SymfonyStyle|null $io
     */
    public function updateCode(SymfonyStyle $io = null)
    {
        $io->title('CampaignChain Data Update');

        if (empty($this->versions)) {
            $io->warning('No code updater Service found, maybe you didn\'t enabled a bundle?');

            return;
        }

        $io->comment('The following versions will be updated');

        $migratedVersions = array_map(
            function(DataUpdateVersion $version) {
                return $version->getVersion();
            },
            $this->entityManager
                ->getRepository('CampaignChainDeploymentUpdateBundle:DataUpdateVersion')
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
                $dbVersion = new DataUpdateVersion();
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