<?php

declare(strict_types=1);

namespace App\Controller\Profile;

use App\CQRS\CommandBus;
use App\CQRS\Command\ImportPhotos\ImportPhotosCommand;
use App\Domain\Exception\DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ImportPhotosController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {}

    #[Route('/profile/import', name: 'profile_import_photos', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $userId = $request->getSession()->get('user_id');

        if (!$userId) {
            return $this->redirectToRoute('home');
        }

        try {
            $imported = $this->commandBus->dispatch(new ImportPhotosCommand($userId));
            $this->addFlash('success', sprintf('Imported %d photo(s) from Phoenix API.', $imported));
        } catch (DomainException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('profile');
    }
}
