<?php

declare(strict_types=1);

namespace App\CQRS\Query\GetProfile;

use App\Entity\User;
use App\CQRS\QueryHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;

final class GetProfileHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    public static function getHandledQuery(): string
    {
        return GetProfileQuery::class;
    }

    public function handle(GetProfileQuery $query): ?User
    {
        return $this->em->getRepository(User::class)->find($query->userId);
    }
}
