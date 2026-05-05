<?php

declare(strict_types=1);

namespace ErdmannFreunde\ContaoLicenseBundle\License;

/**
 * Wird von jedem kommerziellen Bundle als Service registriert,
 * markiert mit Tag "erdmannfreunde.license.product".
 */
final class ProductRegistration
{
    public function __construct(
        private readonly string $productKey,
        private readonly string $productName,
        private readonly int $currentMajor,
        private readonly ?string $vendorUrl = null,
    ) {
    }

    public function getProductKey(): string
    {
        return $this->productKey;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getCurrentMajor(): int
    {
        return $this->currentMajor;
    }

    public function getVendorUrl(): ?string
    {
        return $this->vendorUrl;
    }
}
