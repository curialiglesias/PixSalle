<?php

declare(strict_types=1);

namespace Salle\PixSalle\Model;

use DateTime;

class User
{

  private int $id;
  private string $email;
  private string $username;
  private string $password;
  private string $phone;
  private string $picture;
  private int    $wallet;
  private string $membership;
  private Datetime $createdAt;
  private Datetime $updatedAt;

  public function __construct(
    string $email,
    string $username,
    string $password,
    string $phone,
    string $picture,
    int    $wallet,
    string $membership,
    Datetime $createdAt,
    Datetime $updatedAt
  ) {
    $this->email = $email;
    $this->username = $username;
    $this->password = $password;
    $this->phone = $phone;
    $this->picture = $picture;
    $this->wallet = $wallet;
    $this->membership = $membership;
    $this->createdAt = $createdAt;
    $this->updatedAt = $updatedAt;
  }

  public function id()
  {
    return $this->id;
  }

  public function email()
  {
    return $this->email;
  }

  public function username()
  {
      return $this->username;
  }

  public function password()
  {
    return $this->password;
  }

  public function phone()
  {
      return $this->phone;
  }

  public function picture()
  {
      return $this->picture;
  }

  public function wallet()
  {
      return $this->wallet;
  }

  public function membership()
  {
      return $this->membership;
  }

  public function createdAt()
  {
    return $this->createdAt;
  }

  public function updatedAt()
  {
    return $this->updatedAt;
  }

}
