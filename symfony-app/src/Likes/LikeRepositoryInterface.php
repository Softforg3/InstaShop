<?php

declare(strict_types=1);

namespace App\Likes;

use App\Entity\Photo;
use App\Entity\User;

interface LikeRepositoryInterface
{
    public function findLike(User $user, Photo $photo): ?Like;

    public function hasUserLikedPhoto(User $user, Photo $photo): bool;

    public function addLike(User $user, Photo $photo): Like;

    public function removeLike(Like $like): void;

    /**
     * @param User $user
     * @param Photo[] $photos
     * @return int[] Photo IDs liked by user
     */
    public function getLikedPhotoIds(User $user, array $photos): array;
}
