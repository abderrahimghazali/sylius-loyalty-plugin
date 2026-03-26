<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Command;

use Abderrahim\SyliusLoyaltyPlugin\Entity\LoyaltyConfigurationInterface;
use Abderrahim\SyliusLoyaltyPlugin\Repository\LoyaltyConfigurationRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'loyalty:install',
    description: 'Seed the default loyalty configuration for each channel',
)]
final class InstallCommand extends Command
{
    public function __construct(
        private readonly LoyaltyConfigurationRepositoryInterface $configurationRepository,
        private readonly FactoryInterface $configurationFactory,
        private readonly RepositoryInterface $channelRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var ChannelInterface[] $channels */
        $channels = $this->channelRepository->findAll();

        if (count($channels) === 0) {
            $io->warning('No channels found. Create at least one channel first.');

            return Command::SUCCESS;
        }

        $created = 0;

        foreach ($channels as $channel) {
            $existing = $this->configurationRepository->findOneByChannel($channel);

            if ($existing !== null) {
                $io->note(sprintf('Channel "%s" already has a configuration. Skipping.', $channel->getCode()));

                continue;
            }

            /** @var LoyaltyConfigurationInterface $config */
            $config = $this->configurationFactory->createNew();
            $config->setChannel($channel);
            $this->entityManager->persist($config);

            ++$created;
        }

        $this->entityManager->flush();

        $io->success(sprintf('Created loyalty configuration for %d channel(s).', $created));

        return Command::SUCCESS;
    }
}
