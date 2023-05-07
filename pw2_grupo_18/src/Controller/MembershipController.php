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

final class MembershipController
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

    public function showMembership(Request $request, Response $response): Response
    {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        if (!isset($_SESSION['user_id'])) {
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            return $response->withHeader('Location',$routeParser->urlFor('signIn'))->withStatus(302);
        } else {
            $this->loggedIn = true;
        }

        return $this->twig->render(
            $response,
            'membership.twig',
            [
                'formAction' => $routeParser->urlFor('membershipForm'),
                'loggedIn' => $this->loggedIn,
                'username' => $_SESSION['username'],
                'membership' => $_SESSION['membership']
            ]
        );
    }

    public function membershipUpdate(Request $request, Response $response): Response
    {
        $this->loggedIn = true;
        $data = $request->getParsedBody();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        if ($data['new_membership'] != "") {
            $error = 'Success';
            $this->userRepository->modifyMembership($_SESSION['email'], $data['new_membership'], new DateTime());
            $_SESSION['membership'] = $data['new_membership'];
        } else {
            $error = 'Error';
        }

        return $this->twig->render(
            $response,
            'membership.twig',
            [
                'formAction' => $routeParser->urlFor('membership'),
                'loggedIn' => $this->loggedIn,
                'username' => $_SESSION['username'],
                'membership' => $_SESSION['membership'],
                'formMessage' => $error
            ]
        );
    }
}