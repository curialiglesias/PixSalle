<?php

declare(strict_types=1);

namespace Salle\PixSalle\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Salle\PixSalle\Repository\UserRepository;
use Salle\PixSalle\Model\User;

use Slim\Routing\RouteContext;
use Slim\Views\Twig;

use DateTime;

final class ExploreController
{
    private Twig $twig;
    private UserRepository $userRepository;
    private bool $loggedIn = false;

    public function __construct(
        Twig $twig,
        UserRepository $userRepository
    ) {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }

    public function showExplore(Request $request, Response $response): Response
    {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        if (!isset($_SESSION['user_id'])) {
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            return $response->withHeader('Location',$routeParser->urlFor('signIn'))->withStatus(302);
        } else {
            $this->loggedIn = true;
        }

        $photos = $this->userRepository->getAllPhotos();

        return $this->twig->render(
            $response,
            'explore.twig',
            [
                'loggedIn' => $this->loggedIn,
                'username' => $_SESSION['username'],
                'photos' => $photos
            ]
        );
    }
}