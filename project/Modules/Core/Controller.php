<?php

declare(strict_types=1);

namespace Modules\Core;

class Controller
{
  protected $conn;

  public function __construct()
  {
    $this->conn = include 'connect.php';
  }
}
