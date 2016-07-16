<?php
/*
 * This file is part of the CampaignChain package.
 *
 * (c) CampaignChain, Inc. <info@campaignchain.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
