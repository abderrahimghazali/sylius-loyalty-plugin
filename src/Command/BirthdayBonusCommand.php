<?php

declare(strict_types=1);

namespace Abderrahim\SyliusLoyaltyPlugin\Command;

use Abderrahim\SyliusLoyaltyPlugin\Enum\TransactionType;
use Abderrahim\SyliusLoyaltyPlugin\Service\LoyaltyBalanceManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
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
    /** @param RepositoryInterface<CustomerInterface> $customerRepository */
    public function __construct(
        private readonly RepositoryInterface $customerRepository,
        private readonly LoyaltyBalanceManagerInterface $balanceManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly int $birthdayBonus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->birthdayBonus <= 0) {
            $io->note('Birthday bonus is disabled (set to 0).');

            return Command::SUCCESS;
        }

        $today = new \DateTime('today');

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

        $count = 0;

        /** @var CustomerInterface $customer */
        foreach ($customers as $customer) {
            $account = $this->balanceManager->getOrCreateAccount($customer);

            // Check if birthday bonus was already awarded this year
            $transactions = $account->getTransactions()->filter(
                fn ($t) => $t->getType() === TransactionType::Bonus
                    && str_contains((string) $t->getDescription(), 'Birthday')
                    && $t->getCreatedAt()?->format('Y') === $today->format('Y'),
            );

            if (!$transactions->isEmpty()) {
                continue;
            }

            $this->balanceManager->addTransaction(
                $account,
                TransactionType::Bonus,
                $this->birthdayBonus,
                sprintf('Birthday bonus %s', $today->format('Y')),
            );

            ++$count;
        }

        $io->success(sprintf('Awarded birthday bonus to %d customer(s).', $count));

        return Command::SUCCESS;
    }
}
