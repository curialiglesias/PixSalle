<?php

declare(strict_types=1);

namespace Salle\PixSalle\Model;

use DateTime;

class Photo
{

  private int $id;
  private string $url;

  public function __construct(
      int $id,
    string $url
  ) {
      $this->id = $id;
    $this->url = $url;
  }

  public function id()
  {
    return $this->id;
  }

  public function url()
  {
    return $this->url;
  }
}
