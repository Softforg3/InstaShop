<?php

declare(strict_types=1);

namespace App\Controller\Profile;

use App\CQRS\CommandBus;
use App\CQRS\Command\SavePhoenixToken\SavePhoenixTokenCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class SaveTokenController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {}

    #[Route('/profile/token', name: 'profile_save_token', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $userId = $request->getSession()->get('user_id');

        if (!$userId) {
            return $this->redirectToRoute('home');
        }

        $token = trim((string) $request->request->get('phoenix_token', ''));

        $this->commandBus->dispatch(new SavePhoenixTokenCommand($userId, $token));

        $this->addFlash('success', 'Phoenix API token saved.');

        return $this->redirectToRoute('profile');
    }
}
