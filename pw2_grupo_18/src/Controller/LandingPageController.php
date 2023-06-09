<?php

declare(strict_types=1);

namespace Salle\PixSalle\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteContext;
use Slim\Views\Twig;

use DateTime;

final class LandingPageController
{
    private Twig $twig;
    private bool $loggedIn = false;

    public function __construct(
        Twig $twig
    ) {
        $this->twig = $twig;
    }

    public function showLandingPage(Request $request, Response $response): Response
    {
        $user_name = "user";
        if(isset($_SESSION['user_id'])){
            $this->loggedIn = true;
            $user_name = $_SESSION['username'];
        }

        return $this->twig->render(
            $response,
            'landing.twig',
            [
                'loggedIn' => $this->loggedIn,
                'username' => $user_name
            ]
        );
    }
}