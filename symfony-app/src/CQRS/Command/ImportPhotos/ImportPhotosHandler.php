<?php

declare(strict_types=1);

namespace App\CQRS\Command\ImportPhotos;

use App\Domain\Exception\InvalidTokenException;
use App\Domain\Exception\UserNotFoundException;
use App\Domain\Port\PhoenixClientInterface;
use App\Entity\Photo;
use App\Entity\User;
use App\Repository\PhotoRepository;
use App\CQRS\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;

final class ImportPhotosHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PhoenixClientInterface $phoenixClient,
        private readonly PhotoRepository $photoRepository,
    ) {}

    public static function getHandledCommand(): string
    {
        return ImportPhotosCommand::class;
    }

    public function handle(ImportPhotosCommand $command): int
    {
        $user = $this->em->getRepository(User::class)->find($command->userId);

        if (!$user) {
            throw UserNotFoundException::withId($command->userId);
        }

        $token = $user->getPhoenixApiToken();

        if (!$token) {
            throw InvalidTokenException::notFound();
        }

        $phoenixPhotos = $this->phoenixClient->fetchPhotos($token);
        $existingUrls = $this->photoRepository->findUrlsByUser($user);

        $imported = 0;

        foreach ($phoenixPhotos as $dto) {
            if (in_array($dto->photoUrl, $existingUrls, true)) {
                continue;
            }

            $photo = new Photo();
            $photo->setImageUrl($dto->photoUrl);
            $photo->setUser($user);

            $this->em->persist($photo);
            $imported++;
        }

        if ($imported > 0) {
            $this->em->flush();
        }

        return $imported;
    }
}
