<?php

declare(strict_types=1);

namespace App\CQRS\Command\UnlikePhoto;

use App\Domain\Exception\PhotoNotLikedException;
use App\Domain\Exception\PhotoNotFoundException;
use App\Domain\Exception\UserNotFoundException;
use App\Entity\Photo;
use App\Entity\User;
use App\Likes\LikeRepositoryInterface;
use App\CQRS\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;

final class UnlikePhotoHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LikeRepositoryInterface $likeRepository,
    ) {}

    public function handle(UnlikePhotoCommand $command): void
    {
        $user = $this->em->getRepository(User::class)->find($command->userId);
        if (!$user) {
            throw UserNotFoundException::withId($command->userId);
        }

        $photo = $this->em->getRepository(Photo::class)->find($command->photoId);
        if (!$photo) {
            throw PhotoNotFoundException::withId($command->photoId);
        }

        $like = $this->likeRepository->findLike($user, $photo);
        if (!$like) {
            throw PhotoNotLikedException::create($command->photoId, $command->userId);
        }

        $this->em->wrapInTransaction(function () use ($like, $photo): void {
            $this->likeRepository->removeLike($like);
            $photo->setLikeCounter($photo->getLikeCounter() - 1);
        });
    }
}
