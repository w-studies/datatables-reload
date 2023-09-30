<?php

error_reporting(E_ALL);
use App\Config\Paths;
use Framework\App;

use function App\Config\registerRoutes;

spl_autoload_register(fn ($class) => require '../project/'.$class.'.php');

require Paths::SOURCE.'App/Config/Routes.php';

$app = new App();
registerRoutes($app);

return $app;
