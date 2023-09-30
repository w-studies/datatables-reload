<?php

declare(strict_types=1);

namespace App\Config;

use Framework\App;
use Modules\Items\ItemsController;

function registerRoutes(App $app)
{
  $app->get('/items', [ItemsController::class]);

  $app->get('/items/{id}', [ItemsController::class], 'index');

  $app->post('/items/save', [ItemsController::class, 'save']);
  $app->post('/items/save/{id}', [ItemsController::class, 'save']);
}
