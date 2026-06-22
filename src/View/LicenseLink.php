<?php

declare(strict_types=1);

namespace ErdmannFreunde\ContaoLicenseBundle\View;

use Symfony\Component\Routing\RouterInterface;

/**
 * Rendert den "Lizenz"-Link, über den eine Erweiterung kontextuell auf die
 * Lizenz-Übersicht (do=license) verlinkt. Das Modul ist aus der Navigation
 * ausgeblendet, bleibt aber über diesen Link erreichbar.
 */
final class LicenseLink
{
    public function __construct(private readonly RouterInterface $router)
    {
    }

    public function render(string $class = '', string $label = 'Lizenz'): string
    {
        $url = $this->router->generate('contao_backend', ['do' => 'license']);

        return sprintf(
            '<a href="%s" class="%s" title="Lizenzen verwalten">%s</a>',
            htmlspecialchars($url),
            htmlspecialchars($class),
            htmlspecialchars($label),
        );
    }
}
