<?php

declare(strict_types=1);

namespace App\Controller\Profile;

use App\CQRS\QueryBus;
use App\CQRS\Query\GetProfile\GetProfileQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ShowController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {}

    #[Route('/profile', name: 'profile')]
    public function __invoke(Request $request): Response
    {
        $userId = $request->getSession()->get('user_id');

        if (!$userId) {
            return $this->redirectToRoute('home');
        }

        $user = $this->queryBus->dispatch(new GetProfileQuery($userId));

        if (!$user) {
            $request->getSession()->clear();
            return $this->redirectToRoute('home');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }
}
