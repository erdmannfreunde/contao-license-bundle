<?php

declare(strict_types=1);

namespace ErdmannFreunde\ContaoLicenseBundle\License;

final class ProductRegistry
{
    /** @var array<string, ProductRegistration> */
    private array $products = [];

    /**
     * @param iterable<ProductRegistration> $products
     */
    public function __construct(iterable $products = [])
    {
        foreach ($products as $product) {
            $this->products[$product->getProductKey()] = $product;
        }
    }

    /** @return array<string, ProductRegistration> */
    public function all(): array
    {
        return $this->products;
    }

    public function get(string $productKey): ?ProductRegistration
    {
        return $this->products[$productKey] ?? null;
    }

    public function has(string $productKey): bool
    {
        return isset($this->products[$productKey]);
    }
}
