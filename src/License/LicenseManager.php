<?php

declare(strict_types=1);

namespace ErdmannFreunde\ContaoLicenseBundle\License;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Zentraler Service. Pro-Bundles fragen hier:  $manager->isValid('swiper-pro').
 */
final class LicenseManager
{
    private const CACHE_TTL = 86400;          // 24h
    private const OFFLINE_GRACE = 7 * 86400;  // 7 Tage Karenz

    public function __construct(
        private readonly Connection $connection,
        private readonly LicenseClient $client,
        private readonly ProductRegistry $registry,
        private readonly CacheInterface $cache,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function isValid(string $productKey): bool
    {
        return $this->getStatus($productKey)->isValid();
    }

    public function getStatus(string $productKey): LicenseStatus
    {
        $product = $this->registry->get($productKey);
        if (null === $product) {
            return new LicenseStatus(LicenseStatus::STATE_INVALID, message: 'Unbekanntes Produkt.');
        }

        $row = $this->loadKeyFromDb($productKey);
        if (null === $row || '' === ($row['license_key'] ?? '')) {
            return LicenseStatus::noKey();
        }

        $cacheKey = sprintf('license.%s.%s', $productKey, sha1((string) $row['license_key']));

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($row, $product): LicenseStatus {
            $item->expiresAfter(self::CACHE_TTL);

            $domain = $this->requestStack->getCurrentRequest()?->getHost() ?? 'cli';
            $response = $this->client->check(
                (string) $row['license_key'],
                $product->getProductKey(),
                $product->getCurrentMajor(),
                $domain,
            );

            if (null === $response) {
                $item->expiresAfter(self::OFFLINE_GRACE);

                $status = new LicenseStatus(
                    LicenseStatus::STATE_OFFLINE_GRACE,
                    message: 'Lizenzserver derzeit nicht erreichbar – Karenzzeit aktiv.',
                );
                $this->persistStatus($product->getProductKey(), $status);

                return $status;
            }

            $status = $this->mapResponse($response);
            $this->persistStatus($product->getProductKey(), $status);

            return $status;
        });
    }

    private function persistStatus(string $productKey, LicenseStatus $status): void
    {
        $this->connection->executeStatement(
            'UPDATE tl_license SET status = :status, valid_until = :validUntil, last_checked = :now, tstamp = :now WHERE product = :product',
            [
                'status'     => $status->state,
                'validUntil' => $status->expiresAt?->format('Y-m-d') ?? '',
                'now'        => time(),
                'product'    => $productKey,
            ],
        );
    }

    public function clearCache(string $productKey): void
    {
        $row = $this->loadKeyFromDb($productKey);
        if (null === $row) {
            return;
        }

        $this->cache->delete(sprintf('license.%s.%s', $productKey, sha1((string) $row['license_key'])));
    }

    private function loadKeyFromDb(string $productKey): ?array
    {
        $stmt = $this->connection->executeQuery(
            'SELECT license_key, status, valid_until FROM tl_license WHERE product = :p LIMIT 1',
            ['p' => $productKey],
        );

        $row = $stmt->fetchAssociative();

        return false === $row ? null : $row;
    }

    private function mapResponse(array $response): LicenseStatus
    {
        $state = (string) ($response['state'] ?? LicenseStatus::STATE_INVALID);
        $expires = isset($response['expires_at'])
            ? new \DateTimeImmutable((string) $response['expires_at'])
            : null;

        return new LicenseStatus(
            state: $state,
            licensee: $response['licensee'] ?? null,
            licensedMajor: isset($response['licensed_major']) ? (int) $response['licensed_major'] : null,
            expiresAt: $expires,
            message: $response['message'] ?? null,
            raw: $response,
        );
    }
}
