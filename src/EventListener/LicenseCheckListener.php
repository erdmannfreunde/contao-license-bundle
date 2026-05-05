<?php

declare(strict_types=1);

namespace ErdmannFreunde\ContaoLicenseBundle\EventListener;

use ErdmannFreunde\ContaoLicenseBundle\Attribute\RequiresLicense;
use ErdmannFreunde\ContaoLicenseBundle\License\LicenseManager;
use ErdmannFreunde\ContaoLicenseBundle\License\LicenseStatus;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Wertet das #[RequiresLicense(...)] Attribute auf Controllern aus
 * und blockiert die Action, wenn keine gültige Lizenz vorhanden ist.
 */
final class LicenseCheckListener
{
    public function __construct(private readonly LicenseManager $manager)
    {
    }

    public function __invoke(ControllerEvent $event): void
    {
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return;
        }

        $controller = $event->getController();
        if (!\is_array($controller)) {
            return;
        }

        [$instance, $method] = $controller;
        $attribute = $this->findAttribute(new \ReflectionMethod($instance, $method))
            ?? $this->findAttribute(new \ReflectionClass($instance));

        if (null === $attribute) {
            return;
        }

        $status = $this->manager->getStatus($attribute->productKey);

        if ($status->isValid() && ($attribute->allowTrial || !$status->isTrial())) {
            return;
        }

        throw new HttpException(403, $this->buildMessage($status, $attribute->productKey));
    }

    private function findAttribute(\ReflectionMethod|\ReflectionClass $ref): ?RequiresLicense
    {
        $attrs = $ref->getAttributes(RequiresLicense::class);

        return $attrs ? $attrs[0]->newInstance() : null;
    }

    private function buildMessage(LicenseStatus $status, string $productKey): string
    {
        return match ($status->state) {
            LicenseStatus::STATE_NO_KEY => sprintf('Für "%s" ist kein Lizenzkey hinterlegt. Bitte unter System → Lizenzen einen Key eintragen.', $productKey),
            LicenseStatus::STATE_MAJOR_MISMATCH => sprintf('Dein Lizenzkey für "%s" gilt für eine andere Major-Version. Bitte ein Upgrade erwerben.', $productKey),
            LicenseStatus::STATE_REVOKED => 'Dieser Lizenzkey wurde widerrufen.',
            default => $status->message ?? 'Diese Funktion erfordert eine gültige Lizenz.',
        };
    }
}
