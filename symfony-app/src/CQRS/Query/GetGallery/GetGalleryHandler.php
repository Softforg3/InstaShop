<?php

declare(strict_types=1);

namespace App\CQRS\Query\GetGallery;

use App\CQRS\QueryHandlerInterface;
use App\Entity\User;
use App\Likes\LikeRepositoryInterface;
use App\Repository\PhotoRepository;
use Doctrine\ORM\EntityManagerInterface;

final class GetGalleryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly PhotoRepository $photoRepository,
        private readonly LikeRepositoryInterface $likeRepository,
        private readonly EntityManagerInterface $em,
    ) {}

    /**
     * @return array{photos: array, currentUser: ?User, userLikes: array<int, bool>}
     */
    public function handle(GetGalleryQuery $query): array
    {
        $photos = $this->photoRepository->findAllWithUsers($query->filters);
        $currentUser = null;
        $userLikes = [];

        if ($query->userId) {
            $currentUser = $this->em->getRepository(User::class)->find($query->userId);

            if ($currentUser && $photos) {
                $likedIds = $this->likeRepository->getLikedPhotoIds($currentUser, $photos);
                $userLikes = array_fill_keys($likedIds, true);
            }
        }

        return [
            'photos' => $photos,
            'currentUser' => $currentUser,
            'userLikes' => $userLikes,
        ];
    }
}
