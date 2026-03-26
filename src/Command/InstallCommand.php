<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Command;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
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
        private readonly RepositoryInterface $configurationRepository,
        private readonly FactoryInterface $configurationFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $existing = $this->configurationRepository->findOneBy([]);

        if ($existing !== null) {
            $io->success('Loyalty configuration already exists. Nothing to do.');

            return Command::SUCCESS;
        }

        $config = $this->configurationFactory->createNew();
        $this->entityManager->persist($config);
        $this->entityManager->flush();

        $io->success('Default loyalty configuration created successfully.');

        return Command::SUCCESS;
    }
}
