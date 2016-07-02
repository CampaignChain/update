<?php
/*
 * This file is part of the CampaignChain package.
 *
 * (c) CampaignChain, Inc. <info@campaignchain.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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