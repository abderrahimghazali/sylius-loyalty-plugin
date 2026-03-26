<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Command;

use Abderrahim\SyliusLoyaltyPlugin\Entity\Configuration\LoyaltyConfiguration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'loyalty:install',
    description: 'Seed the default loyalty configuration row',
)]
final class InstallCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $existing = $this->entityManager
            ->getRepository(LoyaltyConfiguration::class)
            ->findOneBy([]);

        if ($existing !== null) {
            $io->success('Loyalty configuration already exists. Nothing to do.');

            return Command::SUCCESS;
        }

        $config = new LoyaltyConfiguration();
        $this->entityManager->persist($config);
        $this->entityManager->flush();

        $io->success('Default loyalty configuration created successfully.');

        return Command::SUCCESS;
    }
}
