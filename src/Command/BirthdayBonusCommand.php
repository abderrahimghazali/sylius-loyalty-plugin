<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Command;

use Abderrahim\SyliusLoyaltyPlugin\Enum\TransactionType;
use Abderrahim\SyliusLoyaltyPlugin\Repository\LoyaltyConfigurationRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Repository\PointTransactionRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyBalanceManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'loyalty:birthday-bonus',
    description: 'Award birthday bonus points to customers whose birthday is today',
)]
final class BirthdayBonusCommand extends Command
{
    public function __construct(
        private readonly RepositoryInterface $customerRepository,
        private readonly LoyaltyBalanceManagerInterface $balanceManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly RepositoryInterface $channelRepository,
        private readonly LoyaltyConfigurationRepositoryInterface $configurationRepository,
        private readonly PointTransactionRepositoryInterface $transactionRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $today = new \DateTime('today');
        $year = (int) $today->format('Y');

        // Find customers whose birthday month and day match today
        $qb = $this->entityManager->createQueryBuilder();
        $customers = $qb->select('c')
            ->from($this->customerRepository->getClassName(), 'c')
            ->where('MONTH(c.birthday) = :month')
            ->andWhere('DAY(c.birthday) = :day')
            ->andWhere('c.birthday IS NOT NULL')
            ->setParameter('month', (int) $today->format('m'))
            ->setParameter('day', (int) $today->format('d'))
            ->getQuery()
            ->getResult();

        if (count($customers) === 0) {
            $io->success('No customers with a birthday today.');

            return Command::SUCCESS;
        }

        /** @var ChannelInterface[] $channels */
        $channels = $this->channelRepository->findAll();
        $count = 0;

        foreach ($channels as $channel) {
            $config = $this->configurationRepository->findOneByChannel($channel);

            if ($config === null || !$config->isBirthdayBonusEnabled() || $config->getBirthdayBonusPoints() <= 0) {
                continue;
            }

            $bonus = $config->getBirthdayBonusPoints();
            $channelCode = $channel->getCode() ?? 'default';
            $description = sprintf('Birthday bonus %d [%s]', $year, $channelCode);

            /** @var CustomerInterface $customer */
            foreach ($customers as $customer) {
                $account = $this->balanceManager->getOrCreateAccount($customer);

                // Check via DB query — scoped to channel + year
                $existing = $this->transactionRepository->findBonusByDescriptionAndYear($account, $description, $year);
                if ($existing !== null) {
                    continue;
                }

                $this->balanceManager->addTransaction(
                    $account,
                    TransactionType::Bonus,
                    $bonus,
                    $description,
                    null,
                    $channel,
                );

                ++$count;
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf('Awarded birthday bonus to %d customer/channel pair(s).', $count));

        return Command::SUCCESS;
    }
}
