<?php

declare(strict_types=1);

namespace App\Controller\Home;

use App\CQRS\QueryBus;
use App\CQRS\Query\GetGallery\GetGalleryQuery;
use App\Gallery\Factory\GalleryFilterFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class IndexController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly GalleryFilterFactory $filterFactory,
    ) {}

    #[Route('/', name: 'home')]
    public function __invoke(Request $request): Response
    {
        $userId = $request->getSession()->get('user_id');
        $filters = $this->filterFactory->fromRequest($request);

        $result = $this->queryBus->dispatch(new GetGalleryQuery($userId, $filters));
        $result['filters'] = $filters;

        return $this->render('home/index.html.twig', $result);
    }
}
