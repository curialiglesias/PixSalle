<?php

declare(strict_types=1);

namespace Salle\PixSalle\Repository;

use PDO;
use Salle\PixSalle\Model\Photo;
use Salle\PixSalle\Model\User;
use Salle\PixSalle\Model\Album;
use Salle\PixSalle\Repository\UserRepository;

final class MySQLUserRepository implements UserRepository
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    private PDO $databaseConnection;

    public function __construct(PDO $database)
    {
        $this->databaseConnection = $database;
    }

    public function createUser(User $user): void
    {
        $query = <<<'QUERY'
        INSERT INTO users(email, username, password, phone, picture, wallet, membership, createdAt, updatedAt)
        VALUES(:email, :username, :password, :phone, :picture, :wallet, :membership, :createdAt, :updatedAt)
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $email = $user->email();
        $username = $user->username();
        $password = $user->password();
        $phone = $user->phone();
        $picture = $user->picture();
        $wallet = $user->wallet();
        $membership = $user->membership();
        $createdAt = $user->createdAt()->format(self::DATE_FORMAT);
        $updatedAt = $user->updatedAt()->format(self::DATE_FORMAT);

        $statement->bindParam('email', $email, PDO::PARAM_STR);
        $statement->bindParam('username', $username, PDO::PARAM_STR);
        $statement->bindParam('password', $password, PDO::PARAM_STR);
        $statement->bindParam('phone', $phone, PDO::PARAM_STR);
        $statement->bindParam('picture', $picture, PDO::PARAM_STR);
        $statement->bindParam('wallet', $wallet, PDO::PARAM_STR);
        $statement->bindParam('membership', $membership, PDO::PARAM_STR);
        $statement->bindParam('createdAt', $createdAt, PDO::PARAM_STR);
        $statement->bindParam('updatedAt', $updatedAt, PDO::PARAM_STR);

        $statement->execute();
    }

    public function createPortfolio(string $title, int $id_user): void
    {
        $query = <<<'QUERY'
        INSERT INTO portfolio(title, id_user)
        VALUES(:title, :id_user)
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('title', $title, PDO::PARAM_STR);
        $statement->bindParam('id_user', $id_user, PDO::PARAM_STR);

        $statement->execute();
    }
    public function createAlbum(string $title, int $id_portfolio): void
    {
        $query = <<<'QUERY'
        INSERT INTO album(title, id_portfolio)
        VALUES(:title, :id_portfolio)
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('title', $title, PDO::PARAM_STR);
        $statement->bindParam('id_portfolio', $id_portfolio, PDO::PARAM_STR);

        $statement->execute();
    }

    public function uploadPhoto(string $url, int $id_album): void
    {
        $query = <<<'QUERY'
        INSERT INTO photo(url, id_album)
        VALUES(:url, :id_album)
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('url', $url, PDO::PARAM_STR);
        $statement->bindParam('id_album', $id_album, PDO::PARAM_STR);

        $statement->execute();
    }

    public function getUserByEmail(string $email)
    {
        $query = <<<'QUERY'
        SELECT * FROM users WHERE email = :email
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('email', $email, PDO::PARAM_STR);

        $statement->execute();

        $count = $statement->rowCount();
        if ($count > 0) {
            $row = $statement->fetch(PDO::FETCH_OBJ);
            return $row;
        }
        return null;
    }

    public function getIdByEmail(string $email): string
    {
        $query = <<<'QUERY'
        SELECT id FROM users WHERE email = :email
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('email', $email, PDO::PARAM_STR);

        $statement->execute();

        $data = $statement->fetch();

        return $data['id'];
    }

    public function getPasswordByEmail(string $email): string
    {
        $query = <<<'QUERY'
        SELECT password FROM users WHERE email = :email
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('email', $email, PDO::PARAM_STR);

        $statement->execute();

        $data = $statement->fetch();

        return $data['password'];
    }

    public function getWalletByEmail(string $email): string
    {
        $query = <<<'QUERY'
        SELECT wallet FROM users WHERE email = :email
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('email', $email, PDO::PARAM_STR);

        $statement->execute();

        $data = $statement->fetch();

        return $data['wallet'];
    }

    public function getPortfolioByEmail(string $email)
    {
        $query = <<<'QUERY'
        SELECT portfolio.id FROM portfolio, users WHERE users.id = portfolio.id_user AND users.email = :email
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('email', $email, PDO::PARAM_STR);

        $statement->execute();

        //$data = $statement->fetch();

        $count = $statement->rowCount();
        if ($count > 0) {
            $row = $statement->fetch();
            return $row['id'];
        }
        return -1;
    }

    public function getAlbumsByPortfolio(int $id_portfolio)
    {
        $query = <<<'QUERY'
        SELECT * FROM album WHERE album.id_portfolio = :id_portfolio
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('id_portfolio', $id_portfolio, PDO::PARAM_STR);

        $statement->execute();

        $count = $statement->rowCount();
        $albums = [];
        if ($count > 0) {
            $data = $statement->fetchAll();
            foreach ($data as $row) {
                $album = new Album(
                    (int)$row['id'],
                    $row['title'],
                    $this->getPhotosByAlbum((int)$row['id'])
                );
                $albums[] = $album;
            }
            return $albums;
        }
        return null;
    }

    public function getPhotosByAlbum(int $id_album)
    {
        $query = <<<'QUERY'
        SELECT * FROM photo WHERE photo.id_album = :id_album
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('id_album', $id_album, PDO::PARAM_STR);

        $statement->execute();

        $count = $statement->rowCount();
        $photos = [];
        if ($count > 0) {
            $data = $statement->fetchAll();
            foreach ($data as $row) {
                $photo = new Photo(
                    (int)$row['id'],
                    $row['url']
                );
                $photos[] = $photo;
            }
            return $photos;
        }
        return array();
    }

    public function getAllPhotos()
    {
        $query = <<<'QUERY'
        SELECT photo.url, users.username FROM users, portfolio, album, photo
        WHERE photo.id_album = album.id AND album.id_portfolio = portfolio.id AND portfolio.id_user = users.id
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->execute();

        $data = $statement->fetchAll();

        return $data;
    }

    public function modifyUser(string $email, string $username, string $phone, string $picture, \DateTime $updatedAt)
    {
        $query = <<<'QUERY'
        UPDATE users SET username = :username, phone = :phone, picture = :picture, updatedAt = :updatedAt WHERE email = :email
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $updatedAt = $updatedAt->format(self::DATE_FORMAT);

        $statement->bindParam('email', $email, PDO::PARAM_STR);
        $statement->bindParam('username', $username, PDO::PARAM_STR);
        $statement->bindParam('phone', $phone, PDO::PARAM_STR);
        $statement->bindParam('picture', $picture, PDO::PARAM_STR);
        $statement->bindParam('updatedAt', $updatedAt, PDO::PARAM_STR);

        $statement->execute();
    }

    public function modifyPassword(string $email, string $password, \DateTime $updatedAt)
    {
        $query = <<<'QUERY'
        UPDATE users SET password = :password, updatedAt = :updatedAt WHERE email = :email
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $updatedAt = $updatedAt->format(self::DATE_FORMAT);

        $statement->bindParam('email', $email, PDO::PARAM_STR);
        $statement->bindParam('password', $password, PDO::PARAM_STR);
        $statement->bindParam('updatedAt', $updatedAt, PDO::PARAM_STR);

        $statement->execute();
    }

    public function modifyWallet(string $email, int $wallet, \DateTime $updatedAt)
    {
        $query = <<<'QUERY'
        UPDATE users SET wallet = :wallet, updatedAt = :updatedAt WHERE email = :email
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $updatedAt = $updatedAt->format(self::DATE_FORMAT);

        $statement->bindParam('email', $email, PDO::PARAM_STR);
        $statement->bindParam('wallet', $wallet, PDO::PARAM_STR);
        $statement->bindParam('updatedAt', $updatedAt, PDO::PARAM_STR);

        $statement->execute();
    }

    public function modifyMembership(string $email, string $membership, \DateTime $updatedAt)
    {
        $query = <<<'QUERY'
        UPDATE users SET membership = :membership, updatedAt = :updatedAt WHERE email = :email
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $updatedAt = $updatedAt->format(self::DATE_FORMAT);

        $statement->bindParam('email', $email, PDO::PARAM_STR);
        $statement->bindParam('membership', $membership, PDO::PARAM_STR);
        $statement->bindParam('updatedAt', $updatedAt, PDO::PARAM_STR);

        $statement->execute();
    }

    public function deleteAlbumPhotos(int $id_album)
    {
        $query = <<<'QUERY'
        DELETE FROM photo WHERE photo.id_album = :id_album;
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('id_album', $id_album, PDO::PARAM_STR);

        $statement->execute();
    }

    public function deleteAlbum(int $id_album)
    {
        $query = <<<'QUERY'
        DELETE FROM album WHERE id = :id_album;
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('id_album', $id_album, PDO::PARAM_STR);

        $statement->execute();
    }

    public function deletePhotoById(int $id_photo)
    {
        $query = <<<'QUERY'
        DELETE FROM photo WHERE id = :id_photo;
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('id_photo', $id_photo, PDO::PARAM_STR);

        $statement->execute();
    }

}
