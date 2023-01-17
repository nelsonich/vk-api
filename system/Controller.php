<?php

namespace System;

abstract class Controller
{
    private $messages = [
        200 => 'OK',
        201 => 'Created',
        // ...

        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        // ...

        500 => 'Internal Server Error',
        // ...
    ];

    public function process()
    {
        header('HTTP/1.1 ' . $this->statusCode . ' ' . $this->messages[$this->statusCode]);
        header('Content-Type: ' . $this->mimeType);

        echo $this->data;
        exit;
    }

    protected final function json($data, $statusCode = 200)
    {
        $this->mimeType = 'application/json';
        $this->statusCode = $statusCode;
        $this->data = $data;

        return $this;
    }

    protected final function text($data, $statusCode = 200)
    {
        $this->mimeType = 'text/plain';
        $this->statusCode = $statusCode;
        $this->data = $data;

        return $this;
    }

    protected final function html($data, $statusCode = 200)
    {
        $this->mimeType = 'text/html';
        $this->statusCode = $statusCode;
        $this->data = $data;

        return $this;
    }

    protected final function view($viewName, $params = [], $statusCode = 200)
    {
        ob_start();
        extract($params);
        require_once __DIR__ . '/../views/' . $viewName . '.view.php';
        $html = ob_get_clean();

        return $this->html($html, $statusCode);
    }
}
