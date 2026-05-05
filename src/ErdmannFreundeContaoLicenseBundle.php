<?php

declare(strict_types=1);

namespace ErdmannFreunde\ContaoLicenseBundle;

use ErdmannFreunde\ContaoLicenseBundle\DependencyInjection\Compiler\RegisterProductsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ErdmannFreundeContaoLicenseBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterProductsPass());
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
