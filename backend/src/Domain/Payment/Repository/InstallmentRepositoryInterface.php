<?php

declare(strict_types=1);

namespace App\Domain\Payment\Repository;

use App\Domain\Payment\Entity\Installment;
use DateTimeInterface;
use Symfony\Component\Uid\Uuid;

interface InstallmentRepositoryInterface
{
    public function save(Installment $installment): void;

    public function findById(Uuid $id): ?Installment;

    /**
     * @return Installment[]
     */
    public function findOverdue(): array;

    /**
     * @return Installment[]
     */
    public function findDueToday(): array;

    /**
     * @return Installment[]
     */
    public function findDueBetween(DateTimeInterface $start, DateTimeInterface $end): array;

    /**
     * @return Installment[]
     */
    public function findPaidOnDate(DateTimeInterface $date): array;
}
