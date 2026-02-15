<?php

declare(strict_types=1);

namespace App\CQRS\Command\SavePhoenixToken;

use App\Domain\Exception\UserNotFoundException;
use App\Entity\User;
use App\CQRS\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;

final class SavePhoenixTokenHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    public static function getHandledCommand(): string
    {
        return SavePhoenixTokenCommand::class;
    }

    public function handle(SavePhoenixTokenCommand $command): void
    {
        $user = $this->em->getRepository(User::class)->find($command->userId);

        if (!$user) {
            throw UserNotFoundException::withId($command->userId);
        }

        $user->setPhoenixApiToken($command->token ?: null);
        $this->em->flush();
    }
}
