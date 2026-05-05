<?php

declare(strict_types=1);

namespace ErdmannFreunde\ContaoLicenseBundle\EventListener;

use ErdmannFreunde\ContaoLicenseBundle\License\ProductRegistry;

/**
 * Liefert die im DCA verfügbaren Produkt-Optionen aus dem ProductRegistry.
 */
final class ProductOptionsListener
{
    public function __construct(private readonly ProductRegistry $registry)
    {
    }

    /** @return array<string, string> */
    public function getProducts(): array
    {
        $options = [];
        foreach ($this->registry->all() as $product) {
            $options[$product->getProductKey()] = $product->getProductName();
        }

        return $options;
    }

    /**
     * label_callback für die tabellarische Übersicht.
     * Reihenfolge der $args entspricht der 'fields'-Liste im DCA:
     *   0 = product, 1 = license_key, 2 = status, 3 = valid_until, 4 = last_checked
     *
     * @param array<string, mixed> $row
     * @param array<int, string>   $args
     * @return array<int, string>
     */
    public function getLabel(array $row, string $label, $dc, array $args): array
    {
        $productKey = (string) ($row['product'] ?? '');
        $product = $this->registry->get($productKey);

        if (null !== $product) {
            $args[0] = htmlspecialchars($product->getProductName());
        }

        $key = (string) ($row['license_key'] ?? '');
        if ('' !== $key) {
            $args[1] = '<span style="color:#999;font-family:monospace">'.htmlspecialchars($key).'</span>';
        }

        $stateLabels = $GLOBALS['TL_LANG']['tl_license']['states'] ?? [];
        $stateRaw = (string) ($row['status'] ?? '');
        $args[2] = '<em>'.htmlspecialchars($stateLabels[$stateRaw] ?? $stateRaw).'</em>';

        $validUntil = (string) ($row['valid_until'] ?? '');
        $args[3] = '' !== $validUntil ? htmlspecialchars($validUntil) : '–';

        $lastChecked = (int) ($row['last_checked'] ?? 0);
        $args[4] = $lastChecked > 0
            ? date($GLOBALS['TL_CONFIG']['datimFormat'] ?? 'Y-m-d H:i', $lastChecked)
            : '–';

        return $args;
    }
}
