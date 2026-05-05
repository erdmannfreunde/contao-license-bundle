<?php

declare(strict_types=1);

namespace ErdmannFreunde\ContaoLicenseBundle\EventListener;

use Contao\DataContainer;
use Contao\Message;
use ErdmannFreunde\ContaoLicenseBundle\License\LicenseManager;
use ErdmannFreunde\ContaoLicenseBundle\License\LicenseStatus;

/**
 * Stößt nach dem Speichern eines Lizenz-Datensatzes einen frischen
 * Server-Check an, damit Status / Gültigkeit sofort im Backend sichtbar sind.
 */
final class LicenseDcaListener
{
    public function __construct(private readonly LicenseManager $manager)
    {
    }

    public function onSubmit(DataContainer $dc): void
    {
        $product = (string) ($dc->activeRecord->product ?? '');
        if ('' === $product) {
            return;
        }

        $this->manager->clearCache($product);
        $status = $this->manager->getStatus($product);

        $label = $GLOBALS['TL_LANG']['tl_license']['states'][$status->state] ?? $status->state;
        $template = $GLOBALS['TL_LANG']['tl_license']['statusMessage'] ?? 'Lizenzstatus: %s';
        $text = sprintf($template, $label).($status->message ? ' – '.$status->message : '');

        match (true) {
            $status->isValid() && !$status->isTrial() => Message::addConfirmation($text),
            $status->isTrial()                        => Message::addInfo($text),
            LicenseStatus::STATE_OFFLINE_GRACE === $status->state => Message::addInfo($text),
            default                                   => Message::addError($text),
        };
    }
}
