<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Photo;
use App\Entity\User;
use App\Gallery\Dto\FilterCriteria;
use App\Gallery\Dto\FilterCriteriaCollection;
use App\Gallery\Dto\FilterOperator;
use App\Gallery\Dto\GalleryFilterDto;
use App\Gallery\Filter\PhotoFilterRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class PhotoRepository extends ServiceEntityRepository
{
    private const FIELD_MAP = [
        'location' => 'p.location',
        'camera' => 'p.camera',
        'description' => 'p.description',
        'username' => 'u.username',
        'takenAt' => 'p.takenAt',
    ];

    public function __construct(
        ManagerRegistry $registry,
        private readonly PhotoFilterRegistry $filterRegistry,
    ) {
        parent::__construct($registry, Photo::class);
    }

    public function findAllWithUsers(?GalleryFilterDto $filters = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->addSelect('u')
            ->orderBy('p.id', 'ASC');

        if ($filters !== null && !$filters->isEmpty()) {
            $criteria = $this->filterRegistry->resolve($filters);
            $this->applyCriteria($qb, $criteria);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return string[]
     */
    public function findUrlsByUser(User $user): array
    {
        $result = $this->createQueryBuilder('p')
            ->select('p.imageUrl')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'imageUrl');
    }

    private function applyCriteria(QueryBuilder $qb, FilterCriteriaCollection $criteria): void
    {
        $i = 0;
        foreach ($criteria as $criterion) {
            $dqlField = self::FIELD_MAP[$criterion->field] ?? null;

            if ($dqlField === null) {
                continue;
            }

            $param = 'filter_' . $i++;

            match ($criterion->operator) {
                FilterOperator::Like => $qb
                    ->andWhere(sprintf('LOWER(%s) LIKE LOWER(:%s)', $dqlField, $param))
                    ->setParameter($param, '%' . $criterion->value . '%'),
                FilterOperator::Equals => $qb
                    ->andWhere(sprintf('%s = :%s', $dqlField, $param))
                    ->setParameter($param, $criterion->value),
                FilterOperator::GreaterOrEqual => $qb
                    ->andWhere(sprintf('%s >= :%s', $dqlField, $param))
                    ->setParameter($param, $criterion->value),
                FilterOperator::LessOrEqual => $qb
                    ->andWhere(sprintf('%s <= :%s', $dqlField, $param))
                    ->setParameter($param, $criterion->value),
            };
        }
    }
}
