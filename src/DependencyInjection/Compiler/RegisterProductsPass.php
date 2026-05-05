<?php

declare(strict_types=1);

namespace ErdmannFreunde\ContaoLicenseBundle\DependencyInjection\Compiler;

use ErdmannFreunde\ContaoLicenseBundle\License\ProductRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterProductsPass implements CompilerPassInterface
{
    public const TAG = 'erdmannfreunde.license.product';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(ProductRegistry::class)) {
            return;
        }

        $definition = $container->findDefinition(ProductRegistry::class);
        $taggedServices = $container->findTaggedServiceIds(self::TAG);

        $references = [];
        foreach ($taggedServices as $id => $tags) {
            $references[] = new Reference($id);
        }

        $definition->setArgument(0, $references);
    }
}
