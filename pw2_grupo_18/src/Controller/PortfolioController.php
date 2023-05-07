<?php

declare(strict_types=1);

namespace Salle\PixSalle\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Salle\PixSalle\Repository\UserRepository;
use Salle\PixSalle\Controller\FlashController;

use Slim\Routing\RouteContext;
use Slim\Views\Twig;

use DateTime;

final class PortfolioController
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

    public function showPortfolio(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['user_id'])) {
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            return $response->withHeader('Location',$routeParser->urlFor('signIn'))->withStatus(302);
        } else {
            $this->loggedIn = true;
            $user_name = $_SESSION['username'];
        }

        $portfolio = $this->userRepository->getPortfolioByEmail($_SESSION['email']);

        $albums = [];
        if ($portfolio == -1) {
            $portfolioCreated = false;
        } else {
            $portfolioCreated = true;
            $albums = $this->userRepository->getAlbumsByPortfolio(intval($portfolio));
        }

        $portfolio_title = "";
        if(isset($_SESSION['portfolio'])){
            $portfolio_title = $_SESSION['portfolio'];
        }

        return $this->twig->render(
            $response,
            'portfolio.twig',
            [
                'loggedIn' => $this->loggedIn,
                'username' => $user_name,
                'portfolio_title' => $portfolio_title,
                'portfolioCreated' => $portfolioCreated,
                'albums' => $albums
            ]
        );
    }

    public function portfolioCreate(Request $request, Response $response): Response
    {
        $this->loggedIn = true;
        $data = $request->getParsedBody();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $this->userRepository->createPortfolio($data['title'], intval($_SESSION['user_id']));
        $_SESSION['portfolio'] = $data['title'];

        return $this->twig->render(
            $response,
            'portfolio.twig',
            [
                'formAction' => $routeParser->urlFor('portfolio'),
                'loggedIn' => $this->loggedIn,
                'username' => $_SESSION['username'],
                'portfolio_title' => $_SESSION['portfolio'],
                'portfolioCreated' => true
            ]
        );
    }

    public function albumCreate(Request $request, Response $response): Response
    {
        $this->loggedIn = true;
        $data = $request->getParsedBody();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $id_portfolio = $this->userRepository->getPortfolioByEmail($_SESSION['email']);

        $error = false;
        if ($_SESSION['membership'] == 'Active' && $_SESSION['wallet'] >= 2) {
            $this->userRepository->createAlbum($data['title'], intval($id_portfolio));
            $_SESSION['wallet'] = $_SESSION['wallet'] - 2;
            $this->userRepository->modifyWallet($_SESSION['email'], $_SESSION['wallet'], new DateTime());
        } else {
            $error = true;
            //$this->flashController->addMessage($request, $response, "You don't have enough money!");
            //return $response->withHeader('Location',$routeParser->urlFor('wallet'))->withStatus(302);
        }

        $albums = $this->userRepository->getAlbumsByPortfolio(intval($id_portfolio));

        return $this->twig->render(
            $response,
            'portfolio.twig',
            [
                'formAction' => $routeParser->urlFor('portfolio'),
                'loggedIn' => $this->loggedIn,
                'username' => $_SESSION['username'],
                'portfolio_title' => $_SESSION['portfolio'],
                'albums' => $albums,
                'portfolioCreated' => true,
                'formError' => $error
            ]
        );
    }
}