<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\CQRS\Command\Login\LoginCommand;
use App\CQRS\Command\Login\LoginHandler;
use App\Domain\Exception\DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class LoginController extends AbstractController
{
    public function __construct(
        private LoginHandler $loginHandler,
    ) {}

    #[Route('/auth/{username}/{token}', name: 'auth_login')]
    public function __invoke(string $username, string $token, Request $request): Response
    {
        try {
            $user = $this->loginHandler->handle(new LoginCommand($username, $token));
        } catch (DomainException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('home');
        }

        $session = $request->getSession();
        $session->set('user_id', $user->getId());
        $session->set('username', $user->getUsername());

        $this->addFlash('success', 'Welcome back, ' . $user->getUsername() . '!');

        return $this->redirectToRoute('home');
    }
}
