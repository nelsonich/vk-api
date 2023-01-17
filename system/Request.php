<?php

namespace System;

final class Request
{
    private $routeParams = [];
    private $fileList = [];
    private $queryString = [];
    private $postInput = [];

    public function __construct($routeParams, $fileList, $queryString, $postInput)
    {
        $this->routeParams = $routeParams;
        $this->fileList = $fileList;
        $this->queryString = $queryString;
        $this->postInput = $postInput;
    }

    public function query($key, $default = null)
    {
        return isset($this->queryString[$key]) ? $this->queryString[$key] : $default;
    }

    public function input($key, $default = null)
    {
        return isset($this->postInput[$key]) ? $this->postInput[$key] : $default;
    }

    public function param($key, $default = null)
    {
        return isset($this->routeParams[$key]) ? $this->routeParams[$key] : $default;
    }

    public function file($key)
    {
        return isset($this->fileList[$key]) ? $this->fileList[$key] : null;
    }
}
