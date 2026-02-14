<?php

declare(strict_types=1);

namespace App\Controller\Photo;

use App\CQRS\CommandBus;
use App\CQRS\Command\LikePhoto\LikePhotoCommand;
use App\CQRS\Command\UnlikePhoto\UnlikePhotoCommand;
use App\Entity\Photo;
use App\Entity\User;
use App\Likes\LikeRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ToggleLikeController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly LikeRepositoryInterface $likeRepository,
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('/photo/{id}/like', name: 'photo_like')]
    public function __invoke(int $id, Request $request): Response
    {
        $userId = $request->getSession()->get('user_id');

        if (!$userId) {
            $this->addFlash('error', 'You must be logged in to like photos.');
            return $this->redirectToRoute('home');
        }

        $user = $this->em->getRepository(User::class)->find($userId);
        $photo = $this->em->getRepository(Photo::class)->find($id);

        if (!$photo) {
            throw $this->createNotFoundException('Photo not found');
        }

        if ($this->likeRepository->hasUserLikedPhoto($user, $photo)) {
            $this->commandBus->dispatch(new UnlikePhotoCommand($userId, $id));
            $this->addFlash('info', 'Photo unliked!');
        } else {
            $this->commandBus->dispatch(new LikePhotoCommand($userId, $id));
            $this->addFlash('success', 'Photo liked!');
        }

        return $this->redirectToRoute('home');
    }
}
