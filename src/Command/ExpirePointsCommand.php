<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Command;

use Abderrahim\SyliusLoyaltyPlugin\Enum\TransactionType;
use Abderrahim\SyliusLoyaltyPlugin\Repository\PointTransactionRepositoryInterface;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyBalanceManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'loyalty:expire-points',
    description: 'Expire loyalty points that have passed their expiry date',
)]
final class ExpirePointsCommand extends Command
{
    public function __construct(
        private readonly PointTransactionRepositoryInterface $transactionRepository,
        private readonly LoyaltyBalanceManagerInterface $balanceManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $now = new \DateTime();

        $expirableTransactions = $this->transactionRepository->findExpirableTransactions($now);

        if (count($expirableTransactions) === 0) {
            $io->success('No points to expire.');

            return Command::SUCCESS;
        }

        $count = 0;

        foreach ($expirableTransactions as $transaction) {
            $account = $transaction->getLoyaltyAccount();
            if ($account === null) {
                continue;
            }

            $transaction->setExpired(true);

            $this->balanceManager->addTransaction(
                $account,
                TransactionType::Expire,
                $transaction->getPoints(),
                sprintf('Points expired (earned on %s)', $transaction->getCreatedAt()?->format('Y-m-d')),
            );

            ++$count;
        }

        $io->success(sprintf('Expired %d point transaction(s).', $count));

        return Command::SUCCESS;
    }
}
