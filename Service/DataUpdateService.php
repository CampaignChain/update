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

namespace CampaignChain\UpdateBundle\Service;

use CampaignChain\UpdateBundle\Entity\DataUpdateVersion;
use Doctrine\Common\Persistence\ManagerRegistry;
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
     * @var Registry
     */
    private $em;

    /**
     * DataUpdateService constructor.
     *
     * @param Registry $Registry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->em = $managerRegistry->getManager();
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
            $io->warning('No code updater Service found, maybe you didn\'t enable a bundle?');

            return;
        }

        $io->comment('The following data versions will be updated');

        $migratedVersions = array_map(
            function(DataUpdateVersion $version) {
                return $version->getVersion();
            },
            $this->em
                ->getRepository('CampaignChainUpdateBundle:DataUpdateVersion')
                ->findAll()
        );

        $updated = false;

        foreach ($this->versions as $version => $class) {
            if (in_array($version, $migratedVersions)) {
                continue;
            }

            $io->section('Version '.$class->getVersion());
            $io->listing($class->getDescription());

            $io->text('Begin data update');

            $result = $class->execute($io);

            if ($result) {
                $dbVersion = new DataUpdateVersion();
                $dbVersion->setVersion($version);

                $this->em->persist($dbVersion);
                $this->em->flush();

                $io->text('Data update finished');
            }


            $updated = true;
        }

        if (!$updated) {
            $io->success('All data is up to date.');
        } else {
            $io->success('Every data version has been updated.');
        }
    }
}