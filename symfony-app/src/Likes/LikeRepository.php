<?php

declare(strict_types=1);

namespace App\Likes;

use App\Entity\Photo;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class LikeRepository extends ServiceEntityRepository implements LikeRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Like::class);
    }

    #[\Override]
    public function findLike(User $user, Photo $photo): ?Like
    {
        return $this->createQueryBuilder('l')
            ->where('l.user = :user')
            ->andWhere('l.photo = :photo')
            ->setParameter('user', $user)
            ->setParameter('photo', $photo)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    #[\Override]
    public function hasUserLikedPhoto(User $user, Photo $photo): bool
    {
        return (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.user = :user')
            ->andWhere('l.photo = :photo')
            ->setParameter('user', $user)
            ->setParameter('photo', $photo)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    #[\Override]
    public function addLike(User $user, Photo $photo): Like
    {
        $like = new Like();
        $like->setUser($user);
        $like->setPhoto($photo);

        $this->getEntityManager()->persist($like);

        return $like;
    }

    #[\Override]
    public function removeLike(Like $like): void
    {
        $this->getEntityManager()->remove($like);
    }

    #[\Override]
    public function getLikedPhotoIds(User $user, array $photos): array
    {
        if (empty($photos)) {
            return [];
        }

        $result = $this->createQueryBuilder('l')
            ->select('IDENTITY(l.photo) as photo_id')
            ->where('l.user = :user')
            ->andWhere('l.photo IN (:photos)')
            ->setParameter('user', $user)
            ->setParameter('photos', $photos)
            ->getQuery()
            ->getScalarResult();

        return array_map('intval', array_column($result, 'photo_id'));
    }
}
