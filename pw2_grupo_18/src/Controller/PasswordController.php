<?php

declare(strict_types=1);

namespace Salle\PixSalle\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Salle\PixSalle\Service\ValidatorService;
use Salle\PixSalle\Repository\UserRepository;
use Salle\PixSalle\Model\User;

use Slim\Routing\RouteContext;
use Slim\Views\Twig;

use DateTime;

final class PasswordController
{
    private Twig $twig;
    private ValidatorService $validator;
    private UserRepository $userRepository;
    private bool $loggedIn = false;

    public function __construct(
        Twig $twig,
        UserRepository $userRepository
    ) {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
        $this->validator = new ValidatorService();
    }

    public function showChangePassword(Request $request, Response $response): Response
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
            'password.twig',
            [
                'formAction' => $routeParser->urlFor('passwordForm'),
                'loggedIn' => $this->loggedIn,
                'username' => $_SESSION['username']
            ]
        );
    }

    public function passwordChange(Request $request, Response $response): Response
    {
        $this->loggedIn = true;
        $data = $request->getParsedBody();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        if (md5($data['old_password']) == $this->userRepository->getPasswordByEmail($_SESSION['email'])
            && $this->validator->validatePassword($data['new_password']) == ""
            && $data['new_password'] == $data['confirm_password']) {
            $error = 'Success';
            $this->userRepository->modifyPassword($_SESSION['email'], md5($data['new_password']), new DateTime());
        } else {
            $error = 'Error';
        }

        return $this->twig->render(
            $response,
            'password.twig',
            [
                'formAction' => $routeParser->urlFor('password'),
                'loggedIn' => $this->loggedIn,
                'username' => $_SESSION['username'],
                'formMessage' => $error
            ]
        );
    }
}