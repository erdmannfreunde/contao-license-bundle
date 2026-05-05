<?php

declare(strict_types=1);

namespace ErdmannFreunde\ContaoLicenseBundle\License;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Spricht den Lizenzserver via HTTPS an.
 */
final class LicenseClient
{
    private const ENDPOINT = 'https://ping.erdmann.app/api/license';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger = new NullLogger(),
        private readonly float $timeout = 5.0,
    ) {
    }

    /**
     * @return array<string, mixed>|null  null bei Fehler / Server unerreichbar
     */
    public function check(string $key, string $product, int $installedMajor, string $domain): ?array
    {
        try {
            $response = $this->httpClient->request('POST', self::ENDPOINT.'/check', [
                'json' => [
                    'key' => $key,
                    'product' => $product,
                    'installed_major' => $installedMajor,
                    'domain' => $domain,
                ],
                'timeout' => $this->timeout,
            ]);

            if (200 !== $response->getStatusCode()) {
                $this->logger->warning('Lizenzserver antwortete mit Status {status}', [
                    'status' => $response->getStatusCode(),
                ]);

                return null;
            }

            return $response->toArray(false);
        } catch (ExceptionInterface $e) {
            $this->logger->warning('Lizenzserver nicht erreichbar: {msg}', ['msg' => $e->getMessage()]);

            return null;
        }
    }
}
