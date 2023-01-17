<?php

namespace System;

class Router
{
    private array $handlers = [];
    private const METHOD_GET = 'GET';
    private const METHOD_POST = 'POST';
    private const METHOD_DELETE = 'delete';

    public function get(string $path, $handler): void
    {
        $this->addHandler(self::METHOD_GET, $path, $handler);
    }

    public function post(string $path, $handler): void
    {
        $this->addHandler(self::METHOD_POST, $path, $handler);
    }

    public function delete(string $path, $handler): void
    {
        $this->addHandler(strtoupper(self::METHOD_DELETE), $path, $handler);
    }

    public function addHandler(string $method, string $path, $handler)
    {
        $this->handlers[$method . $path] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
        ];
    }

    public function run()
    {
        $requestURI = parse_url($_SERVER['REQUEST_URI']);
        $requestPath = $requestURI['path'];
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        $callback = null;

        $matches = [];

        foreach ($this->handlers as $handler) {
            $paramsMatches = [];
            $params = preg_match_all('/\{[a-zA-Z\-\_\d]+\}/', $handler['path'], $paramsMatches);
            $handler['path'] = preg_replace('/\{[a-zA-Z\-\_\d]+\}/', '([a-zA-Z\-\_\d]+)', $handler['path']);

            if ($params) {
                foreach ($paramsMatches as $index => $match) {
                    $paramsMatches[$index] = str_replace('{', '', str_replace('}', '', $match[0]));
                }
            }

            $pattern = '|^' . $handler['path'] . '$|';
            $matches = [];

            $result = preg_match_all($pattern, $requestPath, $matches, PREG_PATTERN_ORDER);
            if (!$result) {
                continue;
            }

            if ($handler['method'] === $requestMethod) {
                array_shift($matches);
                foreach ($matches as $index => $match) {
                    $matches[$index] = $match[0];
                }

                $matchResult = [];

                if (count($matches) > 0) {
                    foreach ($paramsMatches as $index => $paramsMatch) {
                        $matchResult[$paramsMatch] = $matches[$index];
                    }
                }


                $matches = $matchResult;
                $callback = $handler['handler'];
                break;
            }
        }

        if (!$callback) {
            echo '404';
            die;
        }

        $request = new Request(
            $matches,
            $_FILES,
            $_GET,
            $_POST,
        );

        try {
            list($controller, $action) = explode('::', $callback);
            $controller = new $controller();

            $response = call_user_func_array([$controller, $action], [$request]);
            $response->process();
        } catch (\Throwable $e) {
            echo json_encode([
                'code' => 500,
                'status' => false,
                'data' => [
                    'message' => $e->getMessage()
                ],
            ]);
            exit;
        }
    }
}
