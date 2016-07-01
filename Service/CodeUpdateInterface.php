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

use Symfony\Component\Console\Style\SymfonyStyle;

interface CodeUpdateInterface {

    /**
     * Return the version for the code update
     * The string has to be in date(YmdHis) format
     * ie: 20160621125928
     *
     * @return integer
     */
    public function getVersion();

    /**
     * A description about what the code update
     * It will be used in the console
     *
     * @return string
     */
    public function getDescription();

    /**
     * The code updates will
     *
     * @param SymfonyStyle $io
     *
     * @return boolean
     */
    public function execute(SymfonyStyle $io = null);
}