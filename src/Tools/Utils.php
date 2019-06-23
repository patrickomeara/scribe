<?php

namespace Mpociot\ApiDoc\Tools;

use Illuminate\Support\Str;
use Illuminate\Routing\Route;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Utils
{
    public static function getFullUrl(Route $route, array $bindings = []): string
    {
        $uri = $route->uri();

        return self::replaceUrlParameterBindings($uri, $bindings);
    }

    /**
     * @param array $action
     *
     * @return array|null
     */
    public static function getRouteActionUses(array $action)
    {
        if ($action['uses'] !== null) {
            if (is_array($action['uses'])) {
                return $action['uses'];
            } elseif (is_string($action['uses'])) {
                return explode('@', $action['uses']);
            }
        }
        if (array_key_exists(0, $action) && array_key_exists(1, $action)) {
            return [
                0 => $action[0],
                1 => $action[1],
            ];
        }
    }

    /**
     * Transform parameters in URLs into real values (/users/{user} -> /users/2).
     * Uses bindings specified by caller, otherwise just uses '1'.
     *
     * @param string $uri
     * @param array $bindings
     *
     * @return mixed
     */
    public static function replaceUrlParameterBindings(string $uri, array $bindings)
    {
        foreach ($bindings as $path => $binding) {
            // So we can support partial bindings like
            // 'bindings' => [
            //  'foo/{type}' => 4,
            //  'bar/{type}' => 2
            //],
            if (Str::is("*$path*", $uri)) {
                preg_match('/({.+?})/', $path, $parameter);
                $uri = str_replace("{$parameter['1']}", $binding, $uri);
            }
        }
        // Replace any unbound parameters with '1'
        $uri = preg_replace('/{(.+?)}/', 1, $uri);

        return $uri;
    }

    public static function deleteDirectoryAndContents($dir)
    {
        if (is_dir($dir)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $fileinfo) {
                $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                $todo($fileinfo->getRealPath());
            }
            rmdir($dir);
        }
        /*
        $adapter = new Local(__DIR__.'../../');
        $filesystem = new Filesystem($adapter);*/
    }
}