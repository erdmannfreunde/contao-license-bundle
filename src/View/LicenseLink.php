<?php

declare(strict_types=1);

namespace ErdmannFreunde\ContaoLicenseBundle\View;

use Symfony\Component\Routing\RouterInterface;

/**
 * Rendert den "Lizenz"-Link, über den eine Erweiterung kontextuell auf die
 * Lizenzverwaltung ihres Produkts verlinkt. Ziel ist die Weiche
 * LicenseRedirectController, die den Datensatz bei Bedarf anlegt.
 */
final class LicenseLink
{
    public function __construct(private readonly RouterInterface $router)
    {
    }

    public function url(string $productKey): string
    {
        return $this->router->generate('erdmannfreunde_license_open', ['product' => $productKey]);
    }

    public function render(string $productKey, string $class = '', string $label = 'Lizenz'): string
    {
        return sprintf(
            '<a href="%s" class="%s" title="Lizenzschlüssel verwalten">%s</a>',
            htmlspecialchars($this->url($productKey)),
            htmlspecialchars($class),
            htmlspecialchars($label),
        );
    }
}
