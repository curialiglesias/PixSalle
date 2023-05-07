<?php
declare(strict_types=1);

namespace Salle\PixSalle\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Flash\Messages;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

final class FlashController
{

    public function __construct(
        private Twig $twig,
        private Messages $flash)
    {
        $this->twig = $twig;
        $this->flash = $flash;
    }

    public function addMessage(Request $request, Response $response, $message): Response
    {
        $this->flash->addMessage(
            'notifications',
            $message
        );

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        return $response->withHeader('Location', $routeParser->urlFor("wallet"))->withStatus(302);
    }
}