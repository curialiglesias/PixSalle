<?php

declare(strict_types=1);

namespace Salle\PixSalle\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;

use Ramsey\Uuid\Uuid;

use Salle\PixSalle\Repository\UserRepository;
use Salle\PixSalle\Model\User;

use Slim\Routing\RouteContext;
use Slim\Views\Twig;

use DateTime;

final class ProfileController
{
    private Twig $twig;
    private UserRepository $userRepository;
    private bool $loggedIn = false;

    private const UPLOADS_DIR = __DIR__ . '/../../public/uploads';
    private const ALLOWED_EXTENSIONS = ['jpg', 'png'];

    public function __construct(
        Twig $twig,
        UserRepository $userRepository
    ) {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }

    public function showProfile(Request $request, Response $response): Response
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
            'profile.twig',
            [
                'formAction' => $routeParser->urlFor('profileForm'),
                'loggedIn' => $this->loggedIn,
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email'],
                'phone' => $_SESSION['phone'],
                'picture' => $_SESSION['picture']
            ]
        );
    }

    public function profileUpdate(Request $request, Response $response): Response
    {
        $this->loggedIn = true;
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $data = $request->getParsedBody();

        /** @var UploadedFileInterface $uploadedFile */
        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['file'];
        $data['picture'] = $uploadedFile->getClientFilename();

        $data = $this->fillEmpty($data);

        $formMessage = [];

        if (!$this->validateUsername($data['username'])) {
            $formMessage['username'] = "Invalid username introduced.";
        }
        if (!$this->validatePhone($data['phone'])){
            $formMessage['phone'] = "Invalid phone number.";
        }

        // Validate picture
        if ($data['picture'] == "") {
            $picture = $_SESSION['picture'];
            $updatePicture = false;
        } else {
            $fileInfo = pathinfo($data['picture']);
            $format = $fileInfo['extension'];
            $size = $uploadedFile->getSize();
            list($width, $height) = getimagesize($_FILES['file']['tmp_name']);
            if ($this->isValidFormat($format) && $size <= 1000000 && $width <= 500 && $height <= 500) {
                $updatePicture = true;
                $uuid = Uuid::uuid4();
                $picture = $uuid->toString() . '.' . $format;
            } else {
                $formMessage['picture'] = "Invalid picture.";
            }
        }

        if (count($formMessage) == 0) {
            $this->userRepository->modifyUser($_SESSION['email'], $data['username'], $data['phone'], $picture, new DateTime());
            $_SESSION['username'] = $data['username'];
            $_SESSION['phone'] = $data['phone'];
            $_SESSION['picture'] = $picture;
            $formMessage['success'] = "Successfully updated information.";
            if ($updatePicture) {
                $uploadedFile->moveTo(self::UPLOADS_DIR . DIRECTORY_SEPARATOR . $picture);
            }
        }

        return $this->twig->render(
            $response,
            'profile.twig',
            [
                'formAction' => $routeParser->urlFor('profile'),
                'loggedIn' => $this->loggedIn,
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email'],
                'phone' => $_SESSION['phone'],
                'picture' => $_SESSION['picture'],
                'formMessage' => $formMessage
            ]
        );
    }

    private function validateUsername(string $username): bool
    {
        if (preg_match("/[a-zA-Z0-9]+/", $username)) {
            return true;
        } else {
            return false;
        }
    }

    private function validatePhone(string $phone): bool
    {
        if ($phone == "6XXXXXXXX" || (preg_match("/(6)[0-9]{8}$/", $phone))) {
            return true;
        } else {
            return false;
        }
    }

    private function isValidFormat(string $extension): bool
    {
        return in_array($extension, self::ALLOWED_EXTENSIONS, true);
    }

    // Omple els camps que l'usuari no ha volgut modificar
    private function fillEmpty($data)
    {
        if ($data['username'] == null) {
            $data['username'] = $_SESSION['username'];
        }
        if ($data['phone'] == null) {
            $data['phone'] = $_SESSION['phone'];
        }
        return $data;
    }
}