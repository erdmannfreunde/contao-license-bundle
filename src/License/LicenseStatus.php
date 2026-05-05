<?php

declare(strict_types=1);

namespace ErdmannFreunde\ContaoLicenseBundle\License;

final class LicenseStatus
{
    public const STATE_VALID = 'valid';
    public const STATE_TRIAL = 'trial';
    public const STATE_INVALID = 'invalid';
    public const STATE_MAJOR_MISMATCH = 'major_mismatch';
    public const STATE_REVOKED = 'revoked';
    public const STATE_NO_KEY = 'no_key';
    public const STATE_OFFLINE_GRACE = 'offline_grace';

    public function __construct(
        public readonly string $state,
        public readonly ?string $licensee = null,
        public readonly ?int $licensedMajor = null,
        public readonly ?\DateTimeImmutable $expiresAt = null,
        public readonly ?string $message = null,
        public readonly array $raw = [],
    ) {
    }

    public function isValid(): bool
    {
        return \in_array($this->state, [self::STATE_VALID, self::STATE_TRIAL, self::STATE_OFFLINE_GRACE], true);
    }

    public function isTrial(): bool
    {
        return self::STATE_TRIAL === $this->state;
    }

    public static function noKey(): self
    {
        return new self(self::STATE_NO_KEY, message: 'Kein Lizenzkey hinterlegt.');
    }
}
