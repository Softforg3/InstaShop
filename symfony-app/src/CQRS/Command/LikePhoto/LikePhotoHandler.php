<?php

declare(strict_types=1);

namespace App\CQRS\Command\LikePhoto;

use App\Domain\Exception\PhotoAlreadyLikedException;
use App\Domain\Exception\PhotoNotFoundException;
use App\Domain\Exception\UserNotFoundException;
use App\Entity\Photo;
use App\Entity\User;
use App\Likes\LikeRepositoryInterface;
use App\CQRS\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;

final class LikePhotoHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LikeRepositoryInterface $likeRepository,
    ) {}

    public static function getHandledCommand(): string
    {
        return LikePhotoCommand::class;
    }

    public function handle(LikePhotoCommand $command): void
    {
        $user = $this->em->getRepository(User::class)->find($command->userId);
        if (!$user) {
            throw UserNotFoundException::withId($command->userId);
        }

        $photo = $this->em->getRepository(Photo::class)->find($command->photoId);
        if (!$photo) {
            throw PhotoNotFoundException::withId($command->photoId);
        }

        if ($this->likeRepository->hasUserLikedPhoto($user, $photo)) {
            throw PhotoAlreadyLikedException::create($command->photoId, $command->userId);
        }

        $this->em->wrapInTransaction(function () use ($user, $photo): void {
            $this->likeRepository->addLike($user, $photo);
            $photo->setLikeCounter($photo->getLikeCounter() + 1);
        });
    }
}
