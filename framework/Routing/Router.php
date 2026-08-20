<?php

declare(strict_types=1);

namespace Misi\Routing;

use Misi\Exceptions\NotFoundException;
use Misi\Http\Request;
use Misi\Http\Response;

/**
 * Router HTTP minimalista.
 *
 * Soporta GET/POST/PUT/PATCH/DELETE, parámetros de ruta {id}
 * y middleware básico (alias -> callable).
 *
 * No implementa: route caching, subdominios, named routes con generación
 * de URL avanzada, ni resolución de controladores vía container complejo.
 * Si el proyecto lo necesita en el futuro, se evalúa agregarlo (ver ROADMAP).
 */
final class Router
{
    /** @var array<int, Route> */
    private array $routes = [];

    /** @var array<string, callable(Request, callable): (Response|null)> */
    private array $middlewareAliases = [];

    public function get(string $uri, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $uri, $handler, $middleware);
    }

    public function post(string $uri, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $uri, $handler, $middleware);
    }

    public function put(string $uri, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $uri, $handler, $middleware);
    }

    public function patch(string $uri, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('PATCH', $uri, $handler, $middleware);
    }

    public function delete(string $uri, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $uri, $handler, $middleware);
    }

    private function addRoute(string $method, string $uri, mixed $handler, array $middleware): void
    {
        $this->routes[] = new Route($method, $uri, $handler, $middleware);
    }

    /** Registra un middleware con un alias, ej: $router->aliasMiddleware('auth', ...) */
    public function aliasMiddleware(string $alias, callable $middleware): void
    {
        $this->middlewareAliases[$alias] = $middleware;
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $uri = $request->uri();

        foreach ($this->routes as $route) {
            if ($route->method !== $method) {
                continue;
            }

            $params = $route->match($uri);

            if ($params === null) {
                continue;
            }

            return $this->runRoute($route, $request, $params);
        }

        throw new NotFoundException("Ruta no encontrada: {$method} {$uri}");
    }

    /** @param array<string, string> $params */
    private function runRoute(Route $route, Request $request, array $params): Response
    {
        $pipeline = array_reverse($route->middleware);

        $core = function (Request $request) use ($route, $params): Response {
            return $this->callHandler($route->handler, $request, $params);
        };

        $next = $core;

        foreach ($pipeline as $alias) {
            $middleware = $this->middlewareAliases[$alias]
                ?? throw new \RuntimeException("Middleware no registrado: {$alias}");

            $next = fn (Request $request): Response => $middleware($request, $next) ?? $core($request);
        }

        return $next($request);
    }

    /** @param array<string, string> $params */
    private function callHandler(mixed $handler, Request $request, array $params): Response
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            return $controller->{$method}($request, ...array_values($params));
        }

        return $handler($request, ...array_values($params));
    }
}
