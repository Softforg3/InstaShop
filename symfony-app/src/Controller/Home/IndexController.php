<?php

declare(strict_types=1);

namespace App\Controller\Home;

use App\CQRS\QueryBus;
use App\CQRS\Query\GetGallery\GetGalleryQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class IndexController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {}

    #[Route('/', name: 'home')]
    public function __invoke(Request $request): Response
    {
        $userId = $request->getSession()->get('user_id');

        $result = $this->queryBus->dispatch(new GetGalleryQuery($userId));

        return $this->render('home/index.html.twig', $result);
    }
}
