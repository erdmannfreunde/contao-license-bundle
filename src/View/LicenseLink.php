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
            '<a href="%s" class="%s" title="Lizenzen verwalten">%s%s</a>',
            htmlspecialchars($url),
            htmlspecialchars($class),
            $this->icon(),
            htmlspecialchars($label),
        );
    }

    private function icon(): string
    {
        $base = '/system/themes/flexible/icons/';
        $attr = 'width="16" height="16" alt="" style="vertical-align:middle;margin-right:4px" loading="lazy"';

        return sprintf('<img src="%spasskey--dark.svg" class="color-scheme--dark" %s>', $base, $attr)
            .sprintf('<img src="%spasskey.svg" class="color-scheme--light" %s>', $base, $attr);
    }
}
