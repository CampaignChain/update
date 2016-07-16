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