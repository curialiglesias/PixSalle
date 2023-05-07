<?php

declare(strict_types=1);

namespace Salle\PixSalle\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Salle\PixSalle\Repository\UserRepository;

use Slim\Routing\RouteContext;
use Slim\Views\Twig;

use DateTime;

final class AlbumController
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

    public function showAlbum(Request $request, Response $response, $args): Response
    {
        if (!isset($_SESSION['user_id'])) {
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            return $response->withHeader('Location',$routeParser->urlFor('signIn'))->withStatus(302);
        } else {
            $this->loggedIn = true;
            $user_name = $_SESSION['username'];
        }

        $portfolio = $this->userRepository->getPortfolioByEmail($_SESSION['email']);
        $albums = $this->userRepository->getAlbumsByPortfolio(intval($portfolio));

        $id_album = $args['id'];
        $selected_album = "";
        foreach ($albums as $album) {
            if ($album->id() == $id_album) {
                $selected_album = $album;
            }
        }

        $photos = $this->userRepository->getPhotosByAlbum(intval($id_album));

        $selected_album->setPhotos($photos);

        return $this->twig->render(
            $response,
            'album.twig',
            [
                'loggedIn' => $this->loggedIn,
                'username' => $user_name,
                'album' => $selected_album
            ]
        );
    }

    public function photoUpload(Request $request, Response $response, $args): Response
    {
        $this->loggedIn = true;
        $data = $request->getParsedBody();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $id_album = $args['id'];
        $this->userRepository->uploadPhoto($data['photo-url'], intval($id_album));
        $photos = $this->userRepository->getPhotosByAlbum(intval($id_album));

        $portfolio = $this->userRepository->getPortfolioByEmail($_SESSION['email']);
        $albums = $this->userRepository->getAlbumsByPortfolio(intval($portfolio));

        $selected_album = "";
        foreach ($albums as $album) {
            if ($album->id() == $id_album) {
                $selected_album = $album;
            }
        }

        $selected_album->setPhotos($photos);
        return $this->twig->render(
            $response,
            'album.twig',
            [
                'loggedIn' => $this->loggedIn,
                'username' => $_SESSION['username'],
                'album' => $selected_album
            ]
        );
    }

    public function deleteAlbum(Request $request, Response $response, $args): Response
    {
        $this->loggedIn = true;
        $data = $request->getParsedBody();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $id_album = $args['id'];
        echo $args['id'];
        $this->userRepository->deleteAlbumPhotos($id_album);
        $this->userRepository->deleteAlbum($id_album);

        $portfolio = $this->userRepository->getPortfolioByEmail($_SESSION['email']);
        $albums = $this->userRepository->getAlbumsByPortfolio(intval($portfolio));

        return $this->twig->render(
            $response,
            'portfolio.twig',
            [
                'loggedIn' => $this->loggedIn,
                'username' => $_SESSION['username'],
                'portfolio_title' => $portfolio->title,
                'portfolioCreated' => true,
                'albums' => $albums
            ]
        );
    }

    public function generateQR(Request $request, Response $response, $args): Response
    {
        $this->loggedIn = true;
        $data = $request->getParsedBody();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $id_album = $args['id'];
        $photos = $this->userRepository->getPhotosByAlbum(intval($id_album));

        $portfolio = $this->userRepository->getPortfolioByEmail($_SESSION['email']);
        $albums = $this->userRepository->getAlbumsByPortfolio(intval($portfolio));

        $selected_album = "";
        foreach ($albums as $album) {
            if ($album->id() == $id_album) {
                $selected_album = $album;
            }
        }

        $this->sendQrRequest("http://localhost:8030/portfolio/album/{$id_album}");

        $selected_album->setPhotos($photos);
        return $this->twig->render(
            $response,
            'album.twig',
            [
                'loggedIn' => $this->loggedIn,
                'username' => $_SESSION['username'],
                'album' => $selected_album
            ]
        );
    }

    private function sendQrRequest($code)
    {
        $data = array(
            'symbology' => 'QRCode',
            'code' => $code
        );

        $options = array(
            'http' => array(
                'method'  => 'POST',
                'content' => json_encode( $data ),
                'header' =>  "Content-Type: application/json\r\n" .
                    "Accept: image/png\r\n"
            )
        );

        $context  = stream_context_create( $options );
        $url = 'http://localhost:8020/BarcodeGenerator';
        $response = file_get_contents( $url, false, $context );
        file_put_contents("{$code}.png", $response);
    }
}