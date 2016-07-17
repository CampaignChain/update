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

namespace CampaignChain\DeploymentUpdateBundle\Command;

class SchemaGenerateCommand extends GenerateBase
{
    protected function configure()
    {
        $this
            ->setName('campaignchain:schema:generate')
            ->setDescription('Creates an empty schema migration file for the selected package.')
        ;
    }

    protected function getDoctrineMigrationsCommand()
    {
        return 'doctrine:migrations:generate';
    }
}