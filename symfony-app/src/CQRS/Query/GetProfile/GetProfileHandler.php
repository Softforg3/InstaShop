<?php

declare(strict_types=1);

namespace App\CQRS\Query\GetProfile;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class GetProfileHandler
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    public function handle(GetProfileQuery $query): ?User
    {
        return $this->em->getRepository(User::class)->find($query->userId);
    }
}
