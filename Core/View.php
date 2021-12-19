<?php

namespace Core;

/**
 * Core view
 * 
 * The base view of all views
 */
class View {
    /**
     * Twig object used to render view
     * 
     * @var
     */
    private static $twig = null;

    /**
     * Render a view template using Twig
     * 
     * @param string    $template   The template file
     * @param array     $args       Associative array of data to display in the view (optional)
     * 
     * @return void
     */
    public static function render($template, $args = []) {
        echo static::getTemplate($template, $args);
    }

    /**
     * Get the contents of a view template using Twig
     * 
     * @param string    $template   The template file
     * @param array     $args       Associative array of data to display in the view (optional)
     * 
     * @return string
     */
    public static function getTemplate($template, $args = []) {
        if (self::$twig === null) {
            $loader = new \Twig\Loader\FilesystemLoader(ROOT . "/App/Views");
            self::$twig = new \Twig\Environment($loader);
        }
        return self::$twig->render($template, $args);
    }
}