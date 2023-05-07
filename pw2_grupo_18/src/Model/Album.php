<?php

declare(strict_types=1);

namespace Salle\PixSalle\Model;

use DateTime;

class Album
{

  private int $id;
  private string $title;
  private array $photos;

  public function __construct(
      int $id,
    string $title,
    array $photos
  ) {
      $this->id = $id;
    $this->title = $title;
    $this->photos = $photos;
  }

  public function id()
  {
    return $this->id;
  }

  public function title()
  {
    return $this->title;
  }

  public function photos()
  {
      return $this->photos;
  }

  public function setPhotos($photos)
  {
      $this->photos = $photos;
  }
}
