<?php

declare(strict_types=1);

namespace ErdmannFreunde\ContaoLicenseBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final class RequiresLicense
{
    public function __construct(
        public readonly string $productKey,
        public readonly bool $allowTrial = true,
    ) {
    }
}
