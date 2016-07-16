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

namespace CampaignChain\DeploymentUpdateBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CodeUpdaterCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('campaignchain.deployment.update.code_updater')) {
            return;
        }

        $definition = $container->findDefinition(
            'campaignchain.deployment.update.code_updater'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'campaignchain.code_updater'
        );

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall(
                'addUpdater',
                [new Reference($id)]
            );
        }
    }
}