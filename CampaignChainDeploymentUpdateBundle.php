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

namespace CampaignChain\DeploymentUpdateBundle;

use CampaignChain\DeploymentUpdateBundle\DependencyInjection\CompilerPass\CodeUpdaterCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use CampaignChain\DeploymentUpdateBundle\DependencyInjection\CampaignChainDeploymentUpdateExtension;

class CampaignChainDeploymentUpdateBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new CodeUpdaterCompilerPass());
    }

    public function getContainerExtension()
    {
        return new CampaignChainDeploymentUpdateExtension();
    }
}
