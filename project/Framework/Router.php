<?php

declare(strict_types=1);

namespace Framework;

class Router
{
  private array $routes = [];

  public function __construct()
  {
  }

  public function add(string $method, string $path, array $controller)
  {
    $path = $this->normalizeURL($path);

    /**
     *  CONVERT THE $route['path'] TO A REGULAR EXPRESSION
     */
    // escape forward slashes
    $regPath = preg_replace('/\//', '\\/', $path);

    // convert variables
    $regPath = preg_replace('/\{([a-z-]+)\}/', '(?P<\1>[0-9a-z-]+)', $regPath);

    // convert variables with regex
    $regPath = preg_replace('/\{([a-z-]+):([^\}]+)\}/', '(?P<\1>\2)', $regPath);

    // add start/end delimiters and insensitive case
    $regPath = '/^'.$regPath.'$/i';

    $this->routes[] = [
      'path'       => $regPath,
      'method'     => strtoupper($method),
      'controller' => $controller,
    ];
  }

  public function dispatch(string $path, string $method)
  {
    $path   = $this->normalizeURL($path);
    $method = strtoupper($method);
    $params = [];

    foreach ($this->routes as $route) {
      // check the request method
      if ($route['method'] !== $method) {
        continue;
      }
      if (preg_match($route['path'], $path, $matches)) {
        foreach($matches as $k => $v) {
          if(is_string($k)) {
            $params[$k] = $v;
          }
        }

        [$class, $action] = $route['controller'] + [1 => 'index'];

        $controllerInstance = new $class;

        return $controllerInstance->{$action}(...$params);
      }
    }

    echo '<pre>Route not found: ';
    print_r($path);
    echo '</pre>';
  }

  private function normalizeURL(string $url)
  {
    $normalizedURL = normalizer_normalize(trim($url, '/'), \Normalizer::FORM_D);
    $cleanedURL    = preg_replace('/[\p{M}\p{Zs}]/u', '', strtolower($normalizedURL));
    $path          = preg_replace('#[/]{2,}#', '/', "/{$cleanedURL}/");

    return $path;
  }
}
