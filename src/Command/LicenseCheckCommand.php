<?php

declare(strict_types=1);

namespace ErdmannFreunde\ContaoLicenseBundle\Command;

use ErdmannFreunde\ContaoLicenseBundle\License\LicenseManager;
use ErdmannFreunde\ContaoLicenseBundle\License\ProductRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'erdmannfreunde:license:check', description: 'Prüft alle registrierten Lizenzen.')]
final class LicenseCheckCommand extends Command
{
    public function __construct(
        private readonly ProductRegistry $registry,
        private readonly LicenseManager $manager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $rows = [];

        foreach ($this->registry->all() as $product) {
            $this->manager->clearCache($product->getProductKey());
            $status = $this->manager->getStatus($product->getProductKey());

            $rows[] = [
                $product->getProductName(),
                $status->state,
                $status->licensee ?? '–',
                $status->expiresAt?->format('Y-m-d') ?? '–',
                $status->isValid() ? 'OK' : 'FEHLER',
            ];
        }

        $io->table(['Produkt', 'Status', 'Lizenznehmer', 'Gültig bis', 'Ergebnis'], $rows);

        return Command::SUCCESS;
    }
}
