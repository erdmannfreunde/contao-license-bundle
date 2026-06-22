<?php

declare(strict_types=1);

namespace ErdmannFreunde\ContaoLicenseBundle\Controller;

use Doctrine\DBAL\Connection;
use ErdmannFreunde\ContaoLicenseBundle\License\ProductRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * Backend-Weiche für den kontextuellen "Lizenz"-Button einer Erweiterung:
 * legt den tl_license-Datensatz des Produkts bei Bedarf an und leitet in
 * dessen Bearbeitungsansicht (do=license) weiter. So passiert das Anlegen
 * beim Klick – nicht bei jedem Listen-Rendern.
 */
#[Route(
    '%contao.backend.route_prefix%/license/{product}',
    name: 'erdmannfreunde_license_open',
    defaults: ['_scope' => 'backend'],
    methods: ['GET'],
)]
final class LicenseRedirectController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly RouterInterface $router,
        private readonly ProductRegistry $registry,
    ) {
    }

    public function __invoke(string $product): RedirectResponse
    {
        if (!$this->registry->has($product)) {
            throw new NotFoundHttpException(sprintf('Unbekanntes Produkt "%s".', $product));
        }

        $id = $this->connection->fetchOne(
            'SELECT id FROM tl_license WHERE product = :product LIMIT 1',
            ['product' => $product],
        );

        if (false === $id) {
            $this->connection->insert('tl_license', [
                'tstamp'  => time(),
                'product' => $product,
            ]);
            $id = (int) $this->connection->lastInsertId();
        }

        return new RedirectResponse(
            $this->router->generate('contao_backend', [
                'do'  => 'license',
                'act' => 'edit',
                'id'  => (int) $id,
            ]),
        );
    }
}
