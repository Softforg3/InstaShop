<?php

declare(strict_types=1);

namespace App\CQRS\Command\Login;

use App\Domain\Exception\InvalidTokenException;
use App\Domain\Exception\UserNotFoundException;
use App\Entity\AuthToken;
use App\Entity\User;
use App\CQRS\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;

final class LoginHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    public static function getHandledCommand(): string
    {
        return LoginCommand::class;
    }

    public function handle(LoginCommand $command): User
    {
        $authToken = $this->em->getRepository(AuthToken::class)->findOneBy([
            'token' => $command->token,
        ]);

        if (!$authToken) {
            throw InvalidTokenException::notFound();
        }

        $user = $this->em->getRepository(User::class)->findOneBy([
            'username' => $command->username,
        ]);

        if (!$user) {
            throw UserNotFoundException::withUsername($command->username);
        }

        if ($authToken->getUser()->getId() !== $user->getId()) {
            throw InvalidTokenException::mismatch();
        }

        return $user;
    }
}
