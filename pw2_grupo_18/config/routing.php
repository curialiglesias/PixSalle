<?php

declare(strict_types=1);

use Salle\PixSalle\Controller\AlbumController;
use Salle\PixSalle\Controller\API\BlogAPIController;
use Salle\PixSalle\Controller\ExploreController;
use Salle\PixSalle\Controller\MembershipController;
use Salle\PixSalle\Controller\PasswordController;
use Salle\PixSalle\Controller\PortfolioController;
use Salle\PixSalle\Controller\ProfileController;
use Salle\PixSalle\Controller\SignUpController;
use Salle\PixSalle\Controller\UserSessionController;
use Salle\PixSalle\Controller\LandingPageController;
use Salle\PixSalle\Controller\WalletController;
use Slim\App;
use Salle\PixSalle\Controller\FlashController;

function addRoutes(App $app): void
{
    $app->get('/', LandingPageController::class . ':showLandingPage')->setName('landing');
    $app->get('/sign-in', UserSessionController::class . ':showSignInForm')->setName('signIn');
    $app->post('/sign-in', UserSessionController::class . ':signIn')->setName('signInForm');
    $app->get('/sign-up', SignUpController::class . ':showSignUpForm')->setName('signUp');
    $app->post('/sign-up', SignUpController::class . ':signUp');
    $app->get('/profile', ProfileController::class . ':showProfile')->setName('profile');
    $app->post('/profile', ProfileController::class . ':profileUpdate')->setName('profileForm');
    $app->get('/profile/changePassword', PasswordController::class . ':showChangePassword')->setName('password');
    $app->post('/profile/changePassword', PasswordController::class . ':passwordChange')->setName('passwordForm');
    $app->get('/user/wallet', WalletController::class . ':showWallet')->setName('wallet');
    $app->post('/user/wallet', WalletController::class . ':walletUpdate')->setName('walletForm');
    $app->get('/user/membership', MembershipController::class . ':showMembership')->setName('membership');
    $app->post('/user/membership', MembershipController::class . ':membershipUpdate')->setName('membershipForm');
    $app->get('/explore', ExploreController::class . ':showExplore')->setName('explore');
    $app->get('/portfolio', PortfolioController::class . ':showPortfolio')->setName('portfolio');
    $app->post('/portfolio', PortfolioController::class . ':portfolioCreate')->setName('portfolioForm');
    $app->post('/portfolio/album-create', PortfolioController::class . ':albumCreate')->setName('albumCreate');
    $app->get('/portfolio/album/{id}', AlbumController::class . ':showAlbum')->setName('album');
    $app->post('/portfolio/album/{id}', AlbumController::class . ':photoUpload')->setName('photoUpload');
    $app->delete('/portfolio/album/{id}', AlbumController::class . ':deleteAlbum')->setName('deleteAlbum');
    $app->post('/portfolio/album/{id}/qr', AlbumController::class . ':generateQR')->setName('generateQR');
}
