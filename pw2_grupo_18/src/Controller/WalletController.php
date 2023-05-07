<?php

declare(strict_types=1);

namespace Salle\PixSalle\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Salle\PixSalle\Repository\UserRepository;
use Salle\PixSalle\Model\User;

use Slim\Flash\Messages;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;


use DateTime;

final class WalletController
{
    private Twig $twig;
    private Messages $flash;
    private UserRepository $userRepository;
    private bool $loggedIn = false;

    public function __construct(
        Twig $twig,
        UserRepository $userRepository,
        Messages $flash
    ) {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
        $this->flash = $flash;
    }

    public function showWallet(Request $request, Response $response): Response
    {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        if (!isset($_SESSION['user_id'])) {
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            return $response->withHeader('Location',$routeParser->urlFor('signIn'))->withStatus(302);
        } else {
            $this->loggedIn = true;
        }

        $messages = $this->flash->getMessages();

        $notifications = $messages['notifications'] ?? [];

        return $this->twig->render(
            $response,
            'wallet.twig',
            [
                'formAction' => $routeParser->urlFor('walletForm'),
                'loggedIn' => $this->loggedIn,
                'username' => $_SESSION['username'],
                'wallet' => $_SESSION['wallet'],
                'notifications' => $notifications
            ]
        );
    }

    public function walletUpdate(Request $request, Response $response): Response
    {
        $this->loggedIn = true;
        $data = $request->getParsedBody();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        if ($data['amount'] > 0) {
            $error = 'Success';
            $old_amount = $this->userRepository->getWalletByEmail($_SESSION['email']);
            $new_amount = $old_amount + $data['amount'];
            $this->userRepository->modifyWallet($_SESSION['email'], $new_amount, new DateTime());
            $_SESSION['wallet'] = $new_amount;
        } else {
            $error = 'Error';
        }

        return $this->twig->render(
            $response,
            'wallet.twig',
            [
                'formAction' => $routeParser->urlFor('wallet'),
                'loggedIn' => $this->loggedIn,
                'username' => $_SESSION['username'],
                'wallet' => $_SESSION['wallet'],
                'formMessage' => $error
            ]
        );
    }

    public function apply(Request $request, Response $response)
    {
        $messages = $this->flash->getMessages();

        $notifications = $messages['notifications'] ?? [];

        return $this->twig->render(
            $response,
            'wallet.twig',
            [
                'notifications' => $notifications
            ]
        );
    }
}