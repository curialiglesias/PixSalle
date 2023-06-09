<?php

declare(strict_types=1);

namespace Salle\PixSalle\Controller;

use Salle\PixSalle\Service\ValidatorService;
use Salle\PixSalle\Repository\UserRepository;
use Salle\PixSalle\Model\User;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Routing\RouteContext;
use Slim\Views\Twig;

use DateTime;

final class SignUpController
{
    private Twig $twig;
    private ValidatorService $validator;
    private UserRepository $userRepository;

    public function __construct(
        Twig $twig,
        UserRepository $userRepository
    ) {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
        $this->validator = new ValidatorService();
    }

    public function showSignUpForm(Request $request, Response $response): Response
    {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        return $this->twig->render(
            $response,
            'sign-up.twig',
            [
                'formAction' => $routeParser->urlFor('signUp'),
                'test' => 'test'
            ]
        );
    }

    public function signUp(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $errors = [];

        $errors['email'] = $this->validator->validateEmail($data['email']);
        $errors['password'] = $this->validator->validatePassword($data['password']);

        if ($errors['email'] == '') {
            unset($errors['email']);
        }
        if ($errors['password'] == '') {
            unset($errors['password']);
        }
        
        $savedUser = $this->userRepository->getUserByEmail($data['email']);
        if ($savedUser != null) {
            $errors['email'] = "User already exists!";

            return $this->twig->render(
                $response,
                'sign-in.twig',
                [
                    'formErrors' => $errors,
                    'formData' => $data,
                    'formAction' => $routeParser->urlFor('signIn')
                ]
            );
        }
        if (count($errors) == 0) {
            $phone = "6XXXXXXXX";
            $picture = "";
            $wallet = 30;
            $membership = "Cool";
            $user = new User($data['email'], '', md5($data['password']), $phone, $picture, $wallet, $membership, new DateTime(), new DateTime());
            $this->userRepository->createUser($user);
            $id = $this->userRepository->getIdByEmail($data['email']);
            $this->userRepository->modifyUser($data['email'], "user{$id}", $phone, $picture, new DateTime());
            return $response->withHeader('Location', '/sign-in')->withStatus(302);
        }
        return $this->twig->render(
            $response,
            'sign-up.twig',
            [
                'formErrors' => $errors,
                'formData' => $data,
                'formAction' => $routeParser->urlFor('signUp')
            ]
        );
    }
}
